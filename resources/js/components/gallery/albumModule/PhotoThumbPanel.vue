<template>
	<Panel id="lychee_view_content" :header="$t(props.header)" class="w-full border-0">
		<template #icons>
			<PhotoThumbPanelControl v-if="withControl" />
		</template>
		<!-- <div class="flex justify-end" v-if="is_filters_visible"> -->
		<Collapse :when="is_filters_visible">
			<AlbumTagFilter v-if="albumStore.modelAlbum" :album-id="albumStore.modelAlbum.id" @apply="handleTagFilterApply" @clear="handleTagFilterClear" />
		</Collapse>
		<PhotoThumbPanelList
			v-if="isTimeline === false"
			:photos="props.photos"
			:selected-photos="props.selectedPhotos"
			:group-idx="0"
			:is-timeline="false"
			@clicked="(id, e) => emits('clicked', id, e)"
			@selected="(id, e) => emits('selected', id, e)"
			@contexted="(id, e) => emits('contexted', id, e)"
			@toggle-buy-me="(id) => emits('toggleBuyMe', id)"
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
							:group-idx="slotProps.index"
							:is-timeline="true"
							@contexted="(id, e) => emits('contexted', id, e)"
							@selected="(id, e) => emits('selected', id, e)"
							@clicked="(id, e) => emits('clicked', id, e)"
							@toggle-buy-me="(id) => emits('toggleBuyMe', id)"
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
							:group-idx="idx"
							:is-timeline="true"
							@contexted="(id, e) => emits('contexted', id, e)"
							@selected="(id, e) => emits('selected', id, e)"
							@clicked="(id, e) => emits('clicked', id, e)"
							@toggle-buy-me="(id) => emits('toggleBuyMe', id)"
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
import AlbumTagFilter from "./AlbumTagFilter.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { Collapse } from "vue-collapsed";

const { isLTR } = useLtRorRtL();

const lycheeStore = useLycheeStateStore();
const albumStore = useAlbumStore();
const modalStore = useTogglablesStateStore();

const { is_timeline_left_border_visible, is_debug_enabled } = storeToRefs(lycheeStore);
const { is_filters_visible } = storeToRefs(modalStore);

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
	clicked: [id: string, event: MouseEvent];
	selected: [id: string, event: MouseEvent];
	contexted: [id: string, event: MouseEvent];
	toggleBuyMe: [id: string];
}>();

function onIntersectionObserver([entry]: IntersectionObserverEntry[]) {
	if (entry.isIntersecting) {
		const section = entry?.target as HTMLElement;
		props.intersectionAction?.(section.dataset?.date ?? null);
	}
}
const { verifyOrder } = useSplitter();

const isTimeline = computed(() => props.isTimeline && props.photosTimeline !== undefined && props.photosTimeline.length > 1);

/**
 * Handle tag filter apply event from AlbumTagFilter component.
 * Sets the tag filter in AlbumStore and reloads photos.
 */
function handleTagFilterApply(payload: { tagIds: number[]; tagLogic: string }) {
	albumStore.setTagFilter(payload.tagIds, payload.tagLogic);
}

/**
 * Handle tag filter clear event from AlbumTagFilter component.
 * Clears the tag filter in AlbumStore and reloads all photos.
 */
function handleTagFilterClear() {
	albumStore.clearTagFilter();
}

onMounted(() => {
	if (isTimeline.value) {
		verifyOrder(is_debug_enabled.value, props.photos, props.photosTimeline as SplitData<App.Http.Resources.Models.PhotoResource>[]);
	}
});
</script>
