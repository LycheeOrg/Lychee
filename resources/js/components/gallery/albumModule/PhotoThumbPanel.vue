<template>
	<Panel id="lychee_view_content" :header="$t(props.header)" class="w-full border-0">
		<template #icons>
			<PhotoThumbPanelControl v-if="withControl" />
		</template>
		<PhotoThumbPanelList
			v-if="isTimeline === false"
			:photos="props.photos"
			:selected-photos="props.selectedPhotos"
			:iter="0"
			:group-idx="0"
			:is-timeline="false"
			@clicked="propagateClicked"
			@selected="propagateSelected"
			@contexted="propagateMenuOpen"
			@toggle-buy-me="propagateToggleBuyMe"
		/>
		<template v-else>
			<Timeline
				v-if="isLeftBorderVisible"
				:value="props.photosTimeline"
				:pt:eventopposite:class="'hidden'"
				class="mt-4"
				:align="isLTR() ? 'left' : 'right'"
			>
				<template #content="slotProps">
					<div
						data-type="timelineBlock"
						:data-date="(slotProps.item.data[0] as App.Http.Resources.Models.PhotoResource).timeline?.time_date"
						class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8"
						v-intersection-observer="onIntersectionObserver"
					>
						<div class="w-full ltr:text-left rtl:text-right font-semibold text-muted-color-emphasis text-lg">
							{{ slotProps.item.header }}
						</div>
						<PhotoThumbPanelList
							:photos="slotProps.item.data"
							:selected-photos="props.selectedPhotos"
							:iter="slotProps.item.iter"
							:group-idx="slotProps.index"
							:is-timeline="true"
							@contexted="propagateMenuOpen"
							@selected="propagateSelected"
							@clicked="propagateClicked"
							@toggle-buy-me="propagateToggleBuyMe"
						/>
					</div>
				</template>
			</Timeline>
			<div v-else>
				<template v-for="(photoTimeline, idx) in props.photosTimeline" :key="'photoTimeline' + idx">
					<div
						data-type="timelineBlock"
						:data-date="photoTimeline.data[0].timeline?.time_date"
						class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8"
						v-intersection-observer="onIntersectionObserver"
					>
						<div class="w-full ltr:text-left rtl:text-right font-semibold text-muted-color-emphasis text-lg">
							{{ photoTimeline.header }}
						</div>
						<PhotoThumbPanelList
							:photos="photoTimeline.data"
							:selected-photos="props.selectedPhotos"
							:iter="photoTimeline.iter"
							:group-idx="idx"
							:is-timeline="true"
							@contexted="propagateMenuOpen"
							@selected="propagateSelected"
							@clicked="propagateClicked"
							@toggle-buy-me="propagateToggleBuyMe"
						/>
					</div>
				</template>
			</div>
		</template>
	</Panel>
</template>
<script setup lang="ts">
import { computed } from "vue";
import Panel from "primevue/panel";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
import Timeline from "primevue/timeline";
import PhotoThumbPanelList from "./PhotoThumbPanelList.vue";
import PhotoThumbPanelControl from "./PhotoThumbPanelControl.vue";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { onMounted } from "vue";
import { useLtRorRtL } from "@/utils/Helpers";
import { vIntersectionObserver } from "@vueuse/components";

const { isLTR } = useLtRorRtL();

const lycheeStore = useLycheeStateStore();

const { is_timeline_left_border_visible, is_debug_enabled } = storeToRefs(lycheeStore);

const props = defineProps<{
	header: string;
	photos: App.Http.Resources.Models.PhotoResource[];
	photosTimeline?: SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined;
	selectedPhotos: string[];
	isTimeline?: boolean;
	withControl: boolean;
	intersectionAction?: (arg: string | null) => void;
}>();

// We do not show the left border on touch devices (mostly phones) due to limited real estate.
const isLeftBorderVisible = computed(() => is_timeline_left_border_visible.value && !isTouchDevice());

// bubble up.
const emits = defineEmits<{
	clicked: [idx: number, event: MouseEvent];
	selected: [idx: number, event: MouseEvent];
	contexted: [idx: number, event: MouseEvent];
	toggleBuyMe: [idx: string];
}>();

const propagateSelected = (idx: number, e: MouseEvent) => {
	emits("selected", idx, e);
};

const propagateClicked = (idx: number, e: MouseEvent) => {
	emits("clicked", idx, e);
};

const propagateMenuOpen = (idx: number, e: MouseEvent) => {
	emits("contexted", idx, e);
};

const propagateToggleBuyMe = (idx: string) => {
	emits("toggleBuyMe", idx);
};

function onIntersectionObserver([entry]: IntersectionObserverEntry[]) {
	if (entry.isIntersecting) {
		const section = entry?.target as HTMLElement;
		props.intersectionAction?.(section.dataset?.date ?? null);
	}
}
const { verifyOrder } = useSplitter();

const isTimeline = computed(() => props.isTimeline && props.photosTimeline !== undefined && props.photosTimeline.length > 1);

onMounted(() => {
	if (isTimeline.value) {
		verifyOrder(is_debug_enabled.value, props.photos, props.photosTimeline as SplitData<App.Http.Resources.Models.PhotoResource>[]);
	}
});
</script>
