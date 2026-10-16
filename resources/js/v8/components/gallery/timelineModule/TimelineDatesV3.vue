<template>
	<div
		ref="railRef"
		class="sticky top-(--ui-header-height) z-20 h-[calc(100svh-var(--ui-header-height))] shrink-0 w-16 select-none touch-none ltr:border-s rtl:border-e border-default"
		data-stop-drag-select="true"
		@pointerdown="onPointerDown"
		@pointermove="onPointerMove"
		@pointerup="onPointerUp"
		@pointerleave="onPointerLeave"
		dir="ltr"
	>
		<!-- Resting-state ticks: labels in the unit the data spans + dots, positioned by real pixel fraction (T-067, Feature 094).
		     Labels are clipped to the rail's inner width so a long admin format never covers the grid (FR-095-05). -->
		<div
			v-for="t in ticks"
			:key="t.key"
			class="absolute ltr:right-3 rtl:left-3 -translate-y-1/2 pointer-events-none whitespace-nowrap"
			:style="{ top: `${t.topPx}px` }"
		>
			<span
				v-if="t.kind === 'label'"
				class="block max-w-13 overflow-hidden text-xs font-bold"
				:class="t.unitKey === highlightUnitKey ? 'text-primary' : 'text-muted'"
			>
				{{ t.label }}
			</span>
			<span v-else class="block w-1 h-1 rounded-full bg-toned" />
		</div>

		<!-- Playhead: continuously tracks real scroll position. -->
		<div class="absolute inset-x-0 h-0 pointer-events-none" :style="{ top: `${playheadTopPx}px` }">
			<div class="absolute left-2 right-2 -top-px h-0.5 rounded-full bg-primary shadow-[0_0_10px_var(--ui-primary)]" />
		</div>

		<!-- Lens: fisheye magnification of nearby dates while hovering — anchored to
		     the rail's own right edge (not `right-full`/outside it) so it fully
		     covers the rail's resting-state ticks/labels underneath (the background
		     below is opaque over that entire region) instead of leaving them visible
		     alongside a disconnected floating magnified copy. -->
		<Transition
			enter-active-class="transition duration-200 ease-out motion-reduce:transition-none"
			enter-from-class="opacity-0 scale-x-50"
			leave-active-class="transition duration-150 ease-in motion-reduce:transition-none"
			leave-to-class="opacity-0 scale-x-50"
		>
			<div
				v-if="isLensVisible"
				class="absolute pointer-events-none z-20"
				:class="[isLTR() ? 'right-0 origin-right' : 'left-0 origin-left', isWideLensVisible ? 'w-60' : 'w-50']"
				:style="{ top: `${lens.top}px`, height: `${props.lensHeight}px` }"
			>
				<div
					class="absolute inset-0 overflow-hidden border-default bg-default/85 backdrop-blur-sm"
					:class="{
						'border-s mask-[linear-gradient(90deg,transparent,#000_26%)]': isLTR(),
						'border-e mask-[linear-gradient(270deg,transparent,#000_26%)]': isRTL(),
					}"
				/>
				<div :dir class="absolute inset-0 overflow-hidden rtl:text-left ltr:text-right">
					<span
						v-for="item in lens.items"
						:key="item.bucketId"
						class="absolute -translate-y-1/2 whitespace-nowrap font-mono z-30"
						:class="{
							'text-highlighted': item.strong,
							'text-muted': !item.strong,
							'right-3': isLTR(),
							'left-3': isRTL(),
						}"
						:style="{ top: `${item.y}px`, fontSize: `${item.fs}px`, opacity: item.opacity, fontWeight: item.weight }"
					>
						{{ item.label }}
					</span>
				</div>
				<!-- Focal line inside the glass — the ruler edge across the magnified dates. -->
				<div
					class="absolute h-0 pointer-events-none"
					:style="{ top: `${lens.focal}px` }"
					:class="{
						'left-[26%] right-0': isLTR(),
						'left-0 right-[26%]': isRTL(),
					}"
				>
					<div class="absolute inset-0 ltr:right-2 rtl:left-2 -top-px h-0.5 rounded-full bg-primary shadow-[0_0_10px_var(--ui-primary)]" />
				</div>
			</div>
		</Transition>

		<!-- No date close enough to magnify: keep a glowing focal line on the rail itself so the
		     hovered position stays visible, and draw it in from the rail's inner edge. -->
		<Transition
			enter-active-class="transition duration-200 ease-out motion-reduce:transition-none"
			enter-from-class="opacity-0 scale-x-0"
			leave-active-class="transition duration-150 ease-in motion-reduce:transition-none"
			leave-to-class="opacity-0 scale-x-0"
		>
			<div
				v-if="hovering && !isLensVisible"
				class="absolute inset-x-2 -translate-y-px h-px rounded-full bg-primary shadow-[0_0_10px_var(--ui-primary)] pointer-events-none z-20"
				:class="isLTR() ? 'origin-right' : 'origin-left'"
				:style="{ top: `${cursorY}px` }"
			/>
		</Transition>

		<!-- Readout pill: exact date + count under the cursor. Sits beside the lens while it is open,
		     otherwise right next to the rail. -->
		<Transition
			enter-active-class="transition-opacity duration-150 ease-out motion-reduce:transition-none"
			enter-from-class="opacity-0"
			leave-active-class="transition-opacity duration-150 ease-in motion-reduce:transition-none"
			leave-to-class="opacity-0"
		>
			<div
				v-if="hovering && hoverBucket !== null"
				class="absolute -translate-y-1/2 rounded-lg border border-default bg-default px-3 py-1.5 text-xs font-semibold text-highlighted whitespace-nowrap shadow-lg pointer-events-none z-20 transition-[left,right] duration-200 ease-out motion-reduce:transition-none"
				:class="{
					'right-53': isLTR() && isLensVisible && !isWideLensVisible,
					'right-63': isLTR() && isLensVisible && isWideLensVisible,
					'right-18': isLTR() && !isLensVisible,
					'left-53': isRTL() && isLensVisible && !isWideLensVisible,
					'left-63': isRTL() && isLensVisible && isWideLensVisible,
					'left-18': isRTL() && !isLensVisible,
				}"
				:style="{ top: `${cursorY}px` }"
			>
				<span :dir>{{ hoverBucket.label }}</span>
				<em class="ms-2 font-mono not-italic text-xs font-normal text-muted">{{
					trans_choice(props.countLabelKey ?? "gallery.timeline.photos_count", hoverBucket.count, { count: hoverBucket.count.toString() })
				}}</em>
			</div>
		</Transition>
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
 * uses) — showing only labels and dots at rest (Feature 094: in the unit
 * the data spans, `railTicks.ts`). Hovering shows a
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
import { buildRailTicks, entryKeyOf, isWideLens, precisionOf, unitKeyOf } from "@/v8/utils/railTicks";
import { useLycheeStateStore } from "@/stores/LycheeState";

const { dir, isLTR, isRTL } = useLtRorRtL();
const lycheeStore = useLycheeStateStore();

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
	/** Feature 071: pluralized lang key for the readout pill's count — album views counting sub-albums pass their own. */
	countLabelKey?: string;
	/** Feature 071 (FR-071-12): content-space tops of the album grid's bucket section headers. When given, the rail draws one dot per bucket separator instead of one per month. */
	separatorTops?: number[];
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

// --- Resting-state ticks (Feature 094: labels in the unit the data spans; dots one unit finer, or one per album bucket separator) ---

type Tick = { key: string; kind: "label" | "dot"; unitKey: string; label: string; topPx: number };

function clampTickPx(contentTop: number): number {
	return Math.min(railHeight.value - 10, Math.max(10, topPxFor(contentTop)));
}

// Colliding ticks are dropped rather than nudged (`buildRailTicks()`): pushing
// them cascaded down the rail and off-screen, while dropping keeps every
// rendered tick exactly proportional to the real timeline.
const railTicks = computed(() =>
	buildRailTicks({
		entries: props.buckets.bucket_ids.flatMap((bucketId) => {
			const layoutEntry = bucketLayoutById.value.get(bucketId);
			return layoutEntry === undefined ? [] : [{ bucketId, top: layoutEntry.top }];
		}),
		separatorTops: props.separatorTops,
		toRailPx: clampTickPx,
		formats: {
			year: lycheeStore.date_scrubber_format_year,
			month: lycheeStore.date_scrubber_format_month,
			day: lycheeStore.date_scrubber_format_day,
			hour: lycheeStore.date_scrubber_format_hour,
		},
	}),
);

const ticks = computed<Tick[]>(() => [
	...railTicks.value.labels.map((l) => ({ key: `label-${l.unitKey}`, kind: "label" as const, unitKey: l.unitKey, label: l.label, topPx: l.topPx })),
	...railTicks.value.dots.map((d, i) => ({ key: `dot-${i}`, kind: "dot" as const, unitKey: "", label: "", topPx: d.topPx })),
]);

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

/** The label unit to highlight — the one of the hovered bucket while hovering (mirrors the mockup's `onMove()` taking over from `syncHead()`), otherwise of the one under the playhead (FR-094-05). */
const highlightUnitKey = computed(() => {
	const bucket = hovering.value ? hoverBucketLayout.value : scrollBucket.value;
	const unit = railTicks.value.unit;
	const key = bucket === null ? null : entryKeyOf(bucket.bucketId);
	if (key === null || unit === null || precisionOf(key) < unit) {
		return null;
	}
	return unitKeyOf(key, unit);
});

/** FR-094-09: dated hour labels need a wider lens (and the pill beside it moves out). */
const isWideLensVisible = computed(() => isWideLens(props.buckets.bucket_ids));

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

		// ALSO TO BE CONSIDERED
		// Square-root falloff rather than linear: its slope is steepest right at the focal point, so
		// even a date only a hair away in time reads as visibly less prominent, instead of the near-1:1
		// weight a linear falloff gives to anything close to the cursor. 1.118 keeps the same cutoff
		// (w reaches 0 at |t| = 0.8) that the linear version used.
		// const w = 1 - Math.min(1, Math.sqrt(Math.abs(t)) * 1.118);

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
	// Collision cull: anchor on the focal label, then sweep outward in each direction in fixed
	// spatial (y) order, checking spacing only against the last item kept in that direction.
	// An earlier version sorted by distance-to-focal and greedily excluded against every kept
	// item; for two dates close together in time (and so close in y), that let the "nearer to
	// cursor" item flip between them on a sub-pixel cursor move, making the visible label swap
	// unpredictably instead of settling. Sweeping in a fixed order removes that ambiguity.
	candidates.sort((a, b) => a.y - b.y);
	const kept: typeof candidates = [];
	if (candidates.length > 0) {
		let focalIndex = 0;
		for (let i = 1; i < candidates.length; i++) {
			if (candidates[i].d < candidates[focalIndex].d) {
				focalIndex = i;
			}
		}
		const focalItem = candidates[focalIndex];
		kept.push(focalItem);

		// The immediate temporal neighbors are always shown, collision or not, so hovering a date
		// always reveals what comes right before and after it, not just whichever labels happened
		// to have room. If one would land close enough to overlap the focal label, nudge it outward
		// just enough to stay readable — the lens is already a distorted fisheye view, so a small
		// positional nudge here doesn't misrepresent anything the way it would on the real rail ticks.
		let lastY = focalItem.y;
		let lastFs = focalItem.fs;
		if (focalIndex + 1 < candidates.length) {
			const next = { ...candidates[focalIndex + 1] };
			const needed = Math.max(next.fs, lastFs) * 1.35;
			if (next.y - lastY < needed) {
				next.y = lastY + needed;
			}
			kept.push(next);
			lastY = next.y;
			lastFs = next.fs;
		}
		for (let i = focalIndex + 2; i < candidates.length; i++) {
			const c = candidates[i];
			if (Math.abs(c.y - lastY) >= Math.max(c.fs, lastFs) * 1.35) {
				kept.push(c);
				lastY = c.y;
				lastFs = c.fs;
			}
		}

		lastY = focalItem.y;
		lastFs = focalItem.fs;
		if (focalIndex - 1 >= 0) {
			const prev = { ...candidates[focalIndex - 1] };
			const needed = Math.max(prev.fs, lastFs) * 1.35;
			if (lastY - prev.y < needed) {
				prev.y = lastY - needed;
			}
			kept.push(prev);
			lastY = prev.y;
			lastFs = prev.fs;
		}
		for (let i = focalIndex - 2; i >= 0; i--) {
			const c = candidates[i];
			if (Math.abs(c.y - lastY) >= Math.max(c.fs, lastFs) * 1.35) {
				kept.push(c);
				lastY = c.y;
				lastFs = c.fs;
			}
		}
	}
	return {
		items: kept.map((c) => ({
			bucketId: c.bucketId,
			label: c.label,
			y: c.y,
			// Cubed rather than linear: its slope is steepest right at w = 1 (the focal date), so even a
			// date only a hair away in time drops noticeably in opacity instead of reading almost as
			// opaque as the one actually under the cursor.
			opacity: 0.34 + Math.pow(c.w, 4) * 0.66,
			weight: c.w > 0.55 ? 700 : 400,
			fs: c.fs,
			strong: c.w > 0.75,
		})),
		top: lensTop,
		focal,
	};
});

const isLensVisible = computed(() => hovering.value && lens.value.items.length > 0);

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
