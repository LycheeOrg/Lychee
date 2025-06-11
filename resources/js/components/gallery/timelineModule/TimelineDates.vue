<template>
	<div
		:class="{
			'absolute flex flex-col text-muted-color text-right top-0 h-full overflow-y-scroll no-scrollbar': true,
			'right-6': !isTouch,
			'right-2': isTouch,
			'bg-gradient-to-l from-(--p-surface-0) pt-14 dark:from-(--p-surface-900) text-shadow-sm group pb-24': true,
		}"
		@mouseleave="scrollToView"
	>
		<div v-for="yearChunk in dates" :key="yearChunk.header" class="">
			<span
				:class="{
					'sticky inline-block top-0 font-semibold z-10 shadow-surface-950 drop-shadow-md text-3xl scale-75 text-muted-color-emphasis': true,
					'group-hover:scale-100 transition-all duration-150 origin-right': true,
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
						v-for="monthChunk in yearChunk.data"
						:key="monthChunk.time_date"
						:data-date-pointer="monthChunk.time_date"
						:class="{
							'cursor-pointer transition-all duration-150 scale-75 ease-in-out origin-right': true,
							'hover:text-primary-emphasis hover:font-bold hover:scale-100': !isTouch,
							'scale-110  text-primary-emphasis font-bold': currentDate === monthChunk.time_date,
						}"
						@click="emits('load', monthChunk.time_date)"
						>{{ monthChunk.format }}
					</span>
				</div>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import { useSplitter } from "@/composables/album/splitter";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useDebounceFn } from "@vueuse/core";
import { ref } from "vue";
import { onMounted } from "vue";
import { watch } from "vue";
import { computed } from "vue";
import { useRoute } from "vue-router";

const route = useRoute();
const props = defineProps<{
	dates: App.Http.Resources.Models.Utils.TimelineData[];
}>();

const { spliter } = useSplitter();

const dates = computed(() => {
	return spliter(
		props.dates,
		(d) => d.time_date.split("-")[0],
		(d) => d.time_date.split("-")[0],
	);
});

const currentDate = computed(() => (route.params.date as string | undefined) ?? "");
const currentYear = computed(() => parseInt(currentDate.value.split("-")[0], 10));

const isTouch = ref(isTouchDevice());

const emits = defineEmits<{
	load: [date: string];
}>();

// Scroll magic side
// Select the current date and center it in the view
const scrollToView = useDebounceFn(() => {
	if (!currentDate.value) {
		return;
	}

	const el = document.querySelector(`[data-date-pointer="${currentDate.value}"]`);
	if (el) {
		el.scrollIntoView({ behavior: "smooth", block: "center" });
	}
}, 100);

// If the date change, we update!
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
