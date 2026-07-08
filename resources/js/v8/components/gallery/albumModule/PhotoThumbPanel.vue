<template>
	<UContainer id="lychee_view_content" class="w-full border-0">
		<div class="flex items-center justify-between gap-2 py-2">
			<h2 class="font-semibold text-highlighted">{{ $t(props.header) }}</h2>
			<div class="flex items-center gap-1" v-if="withControl">
				<PhotoThumbPanelControl />
			</div>
		</div>
		<Collapse :when="is_filters_visible">
			<AlbumTagFilter
				v-if="albumStore.modelAlbum"
				:album-id="albumStore.modelAlbum.id"
				@apply="handleTagFilterApply"
				@clear="handleTagFilterClear"
				:key="`tags_list_album${albumStore.modelAlbum.id}`"
			/>
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
			<template v-for="(photoTimeline, idx) in props.photosTimeline" :key="'photoTimeline' + idx">
				<div
					data-type="timelineBlock"
					:data-date="photoTimeline.data[0].timeline?.time_date"
					class="flex flex-wrap flex-row shrink w-full justify-start gap-1 sm:gap-2 md:gap-4 pb-8"
					:class="{ 'ltr:border-l rtl:border-r border-neutral-300 dark:border-neutral-600 ltr:pl-4 rtl:pr-4': isLeftBorderVisible }"
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
		</template>
	</UContainer>
</template>
<script setup lang="ts">
import { computed, onMounted } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
import PhotoThumbPanelList from "./PhotoThumbPanelList.vue";
import PhotoThumbPanelControl from "./PhotoThumbPanelControl.vue";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { vIntersectionObserver } from "@vueuse/components";
import AlbumTagFilter from "./AlbumTagFilter.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { Collapse } from "vue-collapsed";

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
