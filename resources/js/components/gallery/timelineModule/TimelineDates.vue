<template>
	<div
		:class="{
			'absolute flex flex-col text-muted-color text-right top-0 h-full overflow-y-scroll no-scrollbar': true,
			'right-6': !isTouch,
			'right-2': isTouch,
			'pt-14': !is_full_screen,
			'bg-gradient-to-l from-(--p-surface-0) dark:from-(--p-surface-900) text-shadow-sm  group pb-24': true,
		}"
	>
		<div v-for="yearChunk in dates" :key="yearChunk.header" class="flex flex-col">
			<span class="font-semibold text-lg text-muted-color-emphasis">{{ yearChunk.header }}</span>
			<span
				v-for="monthChunk in yearChunk.data"
				:class="{
					'cursor-pointer transition-all duration-150 scale-75 ease-in-out origin-right': true,
					'hover:text-primary-emphasis hover:font-bold group-hover:inline-block hover:scale-100 hidden': !isTouch,
				}"
				:key="monthChunk.timeDate"
				@click="emits('load', monthChunk.timeDate)"
				>{{ monthChunk.format }}
			</span>
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
