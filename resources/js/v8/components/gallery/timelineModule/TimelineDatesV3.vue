<template>
	<div
		ref="railRef"
		class="sticky top-(--ui-header-height) z-20 h-[calc(100svh-var(--ui-header-height))] shrink-0 w-16 select-none touch-none ltr:border-s rtl:border-e border-default"
		@pointerdown="onPointerDown"
		@pointermove="onPointerMove"
		@pointerup="onPointerUp"
		@pointerleave="onPointerLeave"
		dir="ltr"
	>
		<!-- Resting-state ticks: year labels + month dots, positioned by real pixel fraction (T-067). -->
		<div
			v-for="t in ticks"
			:key="t.bucketId"
			class="absolute ltr:right-3 rtl:left-3 -translate-y-1/2 pointer-events-none whitespace-nowrap"
			:style="{ top: `${t.topPx}px` }"
		>
			<span v-if="t.kind === 'year'" class="text-xs font-bold" :class="t.year === highlightBucketId?.year ? 'text-primary' : 'text-muted'">
				{{ t.year }}
			</span>
			<span v-else class="block w-1 h-1 rounded-full bg-toned" />
		</div>

		<!-- Playhead: continuously tracks real scroll position. -->
		<div class="absolute inset-x-0 h-0 pointer-events-none" :style="{ top: `${playheadTopPx}px` }">
			<div
				class="absolute left-2 right-2 -top-px h-0.5 rounded-full bg-primary shadow-[0_0_10px_var(--ui-primary)]"
			/>
		</div>

		<!-- Lens: fisheye magnification of nearby dates while hovering — anchored to
		     the rail's own right edge (not `right-full`/outside it) so it fully
		     covers the rail's resting-state ticks/labels underneath (the background
		     below is opaque over that entire region) instead of leaving them visible
		     alongside a disconnected floating magnified copy. -->
		<div
			v-if="hovering && lens.items.length > 0"
			class="absolute ltr:right-0 rtl:left-0 w-50 pointer-events-none z-20"
			:style="{ top: `${lens.top}px`, height: `${props.lensHeight}px` }"
		>
			<div
				class="absolute inset-0 overflow-hidden ltr:border-s rtl:border-e border-default bg-default/85 backdrop-blur-sm ltr:mask-[linear-gradient(90deg,transparent,#000_26%)] rtl:mask-[linear-gradient(270deg,transparent,#000_26%)]"
			/>
			<div
				:dir
				class="absolute inset-0 overflow-hidden rtl:text-left ltr:text-right"
				>
				<span
					v-for="item in lens.items"
					:key="item.bucketId"
					class="absolute -translate-y-1/2 whitespace-nowrap font-mono z-30"
					:class="{
						'text-highlighted' : item.strong,
						'text-muted' : !item.strong,
						'right-3' : isLTR(),
						'left-3' : isRTL(),
					}"
					:style="{ top: `${item.y}px`, fontSize: `${item.fs}px`, opacity: item.opacity, fontWeight: item.weight }"
				>
					{{ item.label }}
				</span>
			</div>
			<!-- Focal line inside the glass — the ruler edge across the magnified dates. -->
			<div
				class="absoluteh-0 pointer-events-none" :style="{ top: `${lens.focal}px` }"
				:class="{
					'left-[26%] right-0' : isLTR(),
					'left-0 right-[26%]' : isRTL(),
				}"
				>
				<div class="absolute inset-0 ltr:right-2 rtl:left-2 -top-px h-0.5 rounded-full bg-primary shadow-[0_0_10px_var(--ui-primary)]" />
			</div>
		</div>

		<!-- Readout pill: exact date + count under the cursor. -->
		<div
			v-if="hovering && hoverBucket !== null"
			class="absolute -translate-y-1/2 rounded-lg border border-default bg-default px-3 py-1.5 text-xs font-semibold text-highlighted whitespace-nowrap shadow-lg pointer-events-none z-20"
			:class="{
				'right-53' : isLTR(),
				'left-53' : isRTL(),
			}"
			:style="{ top: `${cursorY}px` }"
		>
			<span :dir>{{ hoverBucket.label }}</span>
			<em class="ms-2 font-mono not-italic text-xs font-normal text-muted">{{
				trans_choice("gallery.timeline.photos_count", hoverBucket.count, { count: hoverBucket.count.toString() })
			}}</em>
		</div>
	</div>
</template>
<script setup lang="ts">
/**
 * Feature 066 (I9, FR-066-14) — `TimelineDates.vue`'s v3 counterpart. A
 * deliberate fork (`[[project_v8_migration_scope]]`: fork shared modules
 * rather than editing them in place), not an in-place edit of
 * `TimelineDates.vue`: this component is mounted ONLY on the flag-on v3
 * Timeline path (`Timeline.vue`'s own dispatcher) — the v2 fallback keeps
 * using the original, completely untouched `TimelineDates.vue`.
 *
 * T-067 (Timeline Scrubber): reworked from an always-expanded, `position:
 * fixed` (overlapping the photo grid) list of every date into a narrow,
 * non-overlapping rail — a real flex sibling of the grid (`Timeline.vue`
 * wraps both in a `<div class="flex">`, same pattern `AlbumNavPanel.vue`
 * uses) — showing only year labels + month ticks at rest. Hovering shows a
 * fisheye "lens" magnifying nearby dates plus a readout pill; dragging
 * scrubs the grid continuously by pixel offset (not bucket-snapped).
 *
 * Ported from a Claude Design mockup (`Timeline Scrubber.html`) with its
 * hardcoded dark palette/fonts replaced by this app's own semantic theme
 * tokens (`text-primary`/`text-muted`/`bg-default`/etc.) so it stays correct
 * in light and dark mode, per explicit user direction — only the structure
 * and interaction math (fisheye falloff, collision-cull, playhead) is
 * carried over.
 */
import { useElementSize } from "@vueuse/core";
import { trans_choice } from "laravel-vue-i18n";
import { ref, computed } from "vue";
import { useLtRorRtL } from "@/utils/Helpers";

const { dir, isLTR, isRTL } = useLtRorRtL();

const props = defineProps<{
	buckets: App.Http.Resources.V3.PhotoBucketResource;
	/** `PhotoGridVirtual.vue`'s own scroll-tracked `activeHeaderEntry` bucket id — fallback for the highlight while `bucketLayout`/`scrollOffset` haven't resolved a bucket yet (e.g. very first paint). */
	activeBucketId?: string | null;
	/** Every bucket's real (or placeholder-estimated, if not yet loaded) pixel `{top,height}`, index-order-matched to `buckets.bucket_ids` — from `PhotoGridVirtual.vue`'s `timelineLayoutChanged`. */
	bucketLayout: { bucketId: string; top: number; height: number }[];
	/** The grid's total content height in the same pixel space as `bucketLayout`/`scrollOffset`. */
	totalHeight: number;
	/** Continuous content-relative scroll position (same space as `bucketLayout`), for the playhead. */
	scrollOffset: number;
	/** Height of the fisheye lens in px — admin-configurable `timeline_lens_height`. */
	lensHeight: number;
	/** Steepness of the fisheye falloff curve — admin-configurable `timeline_lens_falloff` (already divided by 10 by the caller). Higher is a sharper transition. */
	lensFalloff: number;
	/** How much dates are enlarged at the lens's focal line — admin-configurable `timeline_lens_magnification` (already divided by 10 by the caller). */
	lensMagnification: number;
}>();

const emits = defineEmits<{
	/** Unchanged contract from before T-067 — `Timeline.vue` still handles this via `goToBucket()` (a real route push, so it stays deep-linkable), fired on drag-release or a plain tap. */
	load: [bucketId: string];
	/** New for T-067 — a content-space pixel offset to jump to *without* a route push, for live drag-scrub feedback. Callers should expect several of these per second while dragging. */
	scrub: [pixelOffset: number];
}>();

const railRef = ref<HTMLElement | null>(null);
const { height: railHeight } = useElementSize(railRef);

type BucketEntry = { bucketId: string; label: string; count: number };

const bucketEntries = computed<BucketEntry[]>(() =>
	props.buckets.bucket_ids.map((bucketId, i) => ({ bucketId, label: props.buckets.labels[i], count: props.buckets.counts[i] })),
);

const bucketLayoutById = computed(() => new Map(props.bucketLayout.map((b) => [b.bucketId, b])));
const bucketEntryById = computed(() => new Map(bucketEntries.value.map((b) => [b.bucketId, b])));

function topPxFor(contentTop: number): number {
	if (props.totalHeight <= 0 || railHeight.value <= 0) {
		return 0;
	}
	return (contentTop / props.totalHeight) * railHeight.value;
}

/** Binary search: the bucket whose `[top, top+height)` span contains `contentPx` (clamped to the first/last bucket outside the range) — `bucketLayout` is already ordered top-to-bottom (newest-first), same order the server returns buckets in. */
function bucketAt(contentPx: number): { bucketId: string; top: number; height: number } | null {
	const list = props.bucketLayout;
	if (list.length === 0) {
		return null;
	}
	let lo = 0;
	let hi = list.length - 1;
	while (lo < hi) {
		const mid = (lo + hi) >> 1;
		if (list[mid].top + list[mid].height < contentPx) {
			lo = mid + 1;
		} else {
			hi = mid;
		}
	}
	return list[lo];
}

// --- Resting-state ticks (year labels + month dots) ---

type Tick = { bucketId: string; kind: "year" | "month"; year: string; topPx: number };

const ticks = computed<Tick[]>(() => {
	const seenYears = new Set<string>();
	const seenMonths = new Set<string>();
	const out: Tick[] = [];
	for (const bucketId of props.buckets.bucket_ids) {
		if (bucketId === "unknown") {
			continue;
		}
		const layoutEntry = bucketLayoutById.value.get(bucketId);
		if (layoutEntry === undefined) {
			continue;
		}
		const segments = bucketId.split("-");
		const year = segments[0];
		const month = segments.length > 1 ? segments[1] : null;
		const raw = topPxFor(layoutEntry.top);
		const topPx = Math.min(railHeight.value - 10, Math.max(10, raw));

		if (!seenYears.has(year)) {
			seenYears.add(year);
			out.push({ bucketId, kind: "year", year, topPx });
		} else if (month !== null) {
			const key = `${year}-${month}`;
			if (!seenMonths.has(key)) {
				seenMonths.add(key);
				out.push({ bucketId, kind: "month", year, topPx });
			}
		}
	}

	// Collision avoidance: a bucket's raw pixel position can land a month tick
	// right on top of a year label (e.g. a year with very few photos, or many
	// buckets compressed into a short span of `totalHeight`), making the label
	// unreadable. Earlier attempt pushed the colliding tick further down — but
	// with dozens of month ticks per year that push cascaded (each nudge adds
	// to the next), eventually dragging every subsequent year's whole block
	// past `railHeight` and off-screen entirely. Dropping the offending month
	// tick instead — rather than repositioning anything — keeps every rendered
	// tick's position exactly proportional to the real timeline (matters for a
	// scrubber, unlike the lens's own magnified view, which already drops
	// crowded rim items the same way for the same reason).
	const YEAR_EXCLUSION_PX = 16;
	const yearTops = out.filter((t) => t.kind === "year").map((t) => t.topPx);
	return out.filter((t) => t.kind === "year" || yearTops.every((y) => Math.abs(t.topPx - y) >= YEAR_EXCLUSION_PX));
});

// --- Playhead + active-year highlight ---

const playheadTopPx = computed(() => Math.max(0, Math.min(railHeight.value, topPxFor(props.scrollOffset))));

const scrollBucket = computed(() => {
	const found = bucketAt(props.scrollOffset);
	if (found !== null) {
		return found;
	}
	const fallbackId = props.activeBucketId;
	return fallbackId !== null && fallbackId !== undefined ? (bucketLayoutById.value.get(fallbackId) ?? null) : null;
});

/** Whichever bucket should currently be highlighted — the hovered one while hovering (mirrors the mockup's `onMove()` taking over from `syncHead()`), otherwise the one under the playhead. */
const highlightBucketId = computed(() => {
	const bucket = hovering.value ? hoverBucketLayout.value : scrollBucket.value;
	if (bucket === null) {
		return null;
	}
	return { bucketId: bucket.bucketId, year: bucket.bucketId.split("-")[0] };
});

// --- Hover: cursor tracking, lens (fisheye magnify), pill ---

const hovering = ref(false);
const cursorY = ref(0);

const hoverBucketLayout = computed(() => {
	if (!hovering.value || railHeight.value <= 0) {
		return null;
	}
	return bucketAt((cursorY.value / railHeight.value) * props.totalHeight);
});
const hoverBucket = computed(() => {
	const layoutEntry = hoverBucketLayout.value;
	if (layoutEntry === null) {
		return null;
	}
	return bucketEntryById.value.get(layoutEntry.bucketId) ?? null;
});

type LensItem = { bucketId: string; label: string; y: number; opacity: number; weight: number; fs: number; strong: boolean };

const lens = computed<{ items: LensItem[]; top: number; focal: number }>(() => {
	const railH = railHeight.value;
	if (!hovering.value || railH <= 0 || props.totalHeight <= 0) {
		return { items: [], top: 0, focal: 0 };
	}
	const lensH = props.lensHeight;
	const cy = cursorY.value;
	const lensTop = Math.max(0, Math.min(railH - lensH, cy - lensH / 2));
	const focal = cy - lensTop;
	const halfSrc = lensH / 2 / props.lensMagnification;

	const candidates: { bucketId: string; label: string; y: number; w: number; fs: number; d: number }[] = [];
	for (const b of props.bucketLayout) {
		const naturalY = topPxFor(b.top);
		const src = naturalY - cy;
		if (Math.abs(src) > halfSrc * 2.4) {
			continue;
		}
		const t = src / (halfSrc * 2.4);
		const span = t < 0 ? focal : lensH - focal;
		const out = (Math.tanh(t * props.lensFalloff) / Math.tanh(props.lensFalloff)) * span;
		const w = 1 - Math.min(1, Math.abs(t) * 1.25);
		const y = out + focal;
		if (y < 8 || y > lensH - 8) {
			continue;
		}
		const entry = bucketEntryById.value.get(b.bucketId);
		if (entry === undefined) {
			continue;
		}
		candidates.push({ bucketId: b.bucketId, label: entry.label, y, w, fs: 9.5 + w * 5, d: Math.abs(y - focal) });
	}
	// Collision cull: keep the focal label, then walk outwards dropping crowded rim items.
	candidates.sort((a, b) => a.d - b.d);
	const kept: typeof candidates = [];
	for (const c of candidates) {
		const need = c.fs * 1.35;
		if (kept.every((k) => Math.abs(k.y - c.y) >= Math.max(need, k.fs * 1.35))) {
			kept.push(c);
		}
	}
	return {
		items: kept.map((c) => ({
			bucketId: c.bucketId,
			label: c.label,
			y: c.y,
			opacity: 0.34 + c.w * 0.66,
			weight: c.w > 0.55 ? 700 : 400,
			fs: c.fs,
			strong: c.w > 0.75,
		})),
		top: lensTop,
		focal,
	};
});

// --- Pointer interaction: hover, drag-scrub, click/release-to-jump ---

const dragging = ref(false);
let scrubRafPending = false;
let pendingScrubOffset = 0;

function updateCursor(e: PointerEvent): void {
	const rect = railRef.value?.getBoundingClientRect();
	if (rect === undefined) {
		return;
	}
	cursorY.value = Math.max(0, Math.min(rect.height, e.clientY - rect.top));
}

function flushScrub(): void {
	scrubRafPending = false;
	emits("scrub", pendingScrubOffset);
}

function requestScrub(contentPx: number): void {
	pendingScrubOffset = contentPx;
	if (!scrubRafPending) {
		scrubRafPending = true;
		requestAnimationFrame(flushScrub);
	}
}

function onPointerMove(e: PointerEvent): void {
	updateCursor(e);
	hovering.value = true;
	if (dragging.value && railHeight.value > 0) {
		requestScrub((cursorY.value / railHeight.value) * props.totalHeight);
	}
}

function onPointerDown(e: PointerEvent): void {
	updateCursor(e);
	hovering.value = true;
	dragging.value = true;
	(e.currentTarget as HTMLElement).setPointerCapture(e.pointerId);
	if (railHeight.value > 0) {
		requestScrub((cursorY.value / railHeight.value) * props.totalHeight);
	}
}

function onPointerUp(e: PointerEvent): void {
	if (!dragging.value) {
		return;
	}
	dragging.value = false;
	(e.currentTarget as HTMLElement).releasePointerCapture(e.pointerId);
	const target = hoverBucketLayout.value ?? scrollBucket.value;
	if (target !== null) {
		emits("load", target.bucketId);
	}
}

function onPointerLeave(): void {
	if (dragging.value) {
		// Pointer capture keeps delivering move/up events to this element even
		// once the cursor leaves its bounds while dragging — only clear the
		// hover-only visuals (lens/pill) once the drag itself has ended.
		return;
	}
	hovering.value = false;
}
</script>
