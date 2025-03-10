<template>
	<div
		:class="{
			'absolute flex flex-col text-muted-color text-right top-0 h-full overflow-y-scroll no-scrollbar': true,
			'right-6': !isTouch,
			'right-2': isTouch,
			'bg-gradient-to-l from-(--p-surface-0) pt-14 dark:from-(--p-surface-900) text-shadow-sm group pb-24': true,
		}"
	>
		<div v-for="yearChunk in dates" :key="yearChunk.header" class="">
			<span
				class="sticky top-0 font-semibold drop-shadow-md text-3xl scale-75 text-muted-color-emphasis group-hover:scale-100 transition-all duration-150 origin-right"
				>{{ yearChunk.header }}</span
			>
			<div class="date-wrapper group-hover:grid-rows-[1]">
				<div class="overflow-hidden">
					<template v-for="monthChunk in yearChunk.data" :key="monthChunk.timeDate">
						<span
							:class="{
								'cursor-pointer transition-all duration-150 scale-75 ease-in-out origin-right': true,
								'hover:text-primary-emphasis hover:font-bold hover:scale-100': !isTouch,
							}"
							@click="emits('load', monthChunk.timeDate)"
							>{{ monthChunk.format }}
						</span>
						<br />
					</template>
				</div>
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
import { useSplitter } from "@/composables/album/splitter";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { storeToRefs } from "pinia";
import { ref } from "vue";
import { computed } from "vue";

const props = defineProps<{
	dates: App.Http.Resources.Models.Utils.TimelineData[];
}>();

const { spliter } = useSplitter();

const togglableStore = useTogglablesStateStore();
const { is_full_screen } = storeToRefs(togglableStore);

const dates = computed(() => {
	return spliter(
		props.dates,
		(d) => d.timeDate.split("-")[0],
		(d) => d.timeDate.split("-")[0],
	);
});

const isTouch = ref(isTouchDevice());

const emits = defineEmits<{
	load: [date: string];
}>();
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

/* .group-hover\:grid-rows-\[1\] {
    &:is(:where(.group):hover *) {
		grid-template-rows: 1fr;
    }
} */

.inner {
	overflow: hidden;
}
</style>
