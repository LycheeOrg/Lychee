<template>
	<div
		:class="{
			'fixed flex flex-col text-muted ltr:text-right rtl:text-left top-0 h-full overflow-y-scroll no-scrollbar': true,
			'ltr:right-6 rtl:left-6': !isTouch,
			'ltr:right-2 rtl:left-2': isTouch,
			'ltr:bg-linear-to-l rtl:bg-linear-to-r from-default pt-14 text-shadow-sm group pb-24': true,
		}"
		@mouseleave="scrollToView"
	>
		<div v-for="yearChunk in years" :key="yearChunk.header" class="">
			<span
				:class="{
					'sticky inline-block top-0 font-semibold z-10 drop-shadow-md text-3xl scale-75 text-highlighted': true,
					'group-hover:scale-100 transition-all duration-150 ltr:origin-right rtl:origin-left': true,
					'scale-100': currentYear === parseInt(yearChunk.header, 10),
				}"
			>
				{{ yearChunk.header }}
			</span>
			<!-- We only apply the hover property for items bellow the current scrolling year,
			 	this avoids the upper part of the element to scroll up and down and some jerky behaviours.  -->
			<div :class="{ 'date-wrapper group-hover:grid-rows-[1]': currentYear > parseInt(yearChunk.header, 10) }">
				<div class="overflow-hidden flex flex-col">
					<span
						v-for="entry in yearChunk.data"
						:key="entry.bucketId"
						:data-bucket-pointer="entry.bucketId"
						:class="{
							'cursor-pointer transition-all duration-150 scale-75 ease-in-out ltr:origin-right rtl:origin-left': true,
							'hover:text-primary hover:opacity-80 hover:font-bold hover:scale-100': !isTouch,
							'scale-110  text-primary font-bold': currentBucketId === entry.bucketId,
						}"
						@click="emits('load', entry.bucketId)"
						>{{ entry.label }}
					</span>
				</div>
			</div>
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
 * using the original, completely untouched `TimelineDates.vue`
 * (`props.dates: TimelineData[]`, `GET /api/Timeline::dates`-sourced),
 * exactly as NFR-066-06/FR-066-15 require.
 *
 * Consumes the `buckets` tier response shape directly
 * (`PhotoBucketResource`: `{bucket_ids[], counts[], labels[], bucketable}`)
 * instead of `TimelineData[]` — `labels[i]` is already a ready-to-render
 * display string (computed server-side, see `PhotoBucketResource`'s own doc
 * comment), so no client-side date formatting is needed here, mirroring the
 * v2 component's own `format` field usage exactly. Regroups by year off
 * `bucket_ids[i].split("-")[0]` instead of `time_date.split("-")[0]`; emits
 * `load(bucketId)` instead of `load(date)`; DOM pointer attribute renamed
 * `data-date-pointer` → `data-bucket-pointer` (bucket ids and v2 dates share
 * the same string shape for YEAR/MONTH granularity, but not necessarily for
 * DAY/HOUR — the attribute name should reflect what it actually holds).
 */
import { useSplitter } from "@/composables/album/splitter";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useDebounceFn } from "@vueuse/core";
import { ref, onMounted, watch, computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const props = defineProps<{
	buckets: App.Http.Resources.V3.PhotoBucketResource;
}>();

const { spliter } = useSplitter();

type BucketEntry = { bucketId: string; label: string };

const bucketEntries = computed<BucketEntry[]>(() =>
	props.buckets.bucket_ids.map((bucketId, i) => ({ bucketId, label: props.buckets.labels[i] })),
);

const years = computed(() => {
	return spliter(
		bucketEntries.value,
		(d) => d.bucketId.split("-")[0],
		(d) => d.bucketId.split("-")[0],
	);
});

const currentBucketId = computed(() => (route.params.date as string | undefined) ?? "");
const currentYear = computed(() => parseInt(currentBucketId.value.split("-")[0], 10));

const isTouch = ref(isTouchDevice());

const emits = defineEmits<{
	load: [bucketId: string];
}>();

// Scroll magic side
// Select the current bucket and center it in the view
const scrollToView = useDebounceFn(() => {
	if (!currentBucketId.value) {
		return;
	}

	const el = document.querySelector(`[data-bucket-pointer="${currentBucketId.value}"]`);
	if (el) {
		el.scrollIntoView({ behavior: "smooth", block: "center" });
	}
}, 100);

// If the bucket changes, we update!
watch(() => route.params.date, scrollToView, { immediate: true });

// Also do that at when loading the component
onMounted(() =>
	// But wait! if we do it too early the dom is not rendered and this does not work.
	// Let's wait 500ms for the rendering, then select the element.
	setTimeout(scrollToView, 500),
);
</script>
<style>
.date-wrapper {
	display: grid;
	grid-template-rows: 0fr;
	transition: grid-template-rows 0.25s ease-out;

	&:is(:where(.group):hover *) {
		grid-template-rows: 1fr;
	}
}
</style>
