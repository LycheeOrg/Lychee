<template>
	<UContainer id="lychee_view_content" class="w-full border-0">
		<div class="flex items-center justify-between gap-2 py-2">
			<h2 class="font-semibold text-highlighted">{{ $t(props.header) }}</h2>
			<div class="flex items-center gap-1">
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
		<PhotoGridVirtual
			:selected-photos="props.selectedPhotos"
			@clicked="(id, e) => emits('clicked', id, e)"
			@selected="(id, e) => emits('selected', id, e)"
			@contexted="(id, e) => emits('contexted', id, e)"
			@toggle-buy-me="(id) => emits('toggleBuyMe', id)"
		/>
	</UContainer>
</template>
<script setup lang="ts">
/**
 * Flag-on (`isPhotoSoaActive`) replacement for `PhotoThumbPanel.vue` —
 * reuses its exact header/control/tag-filter shell (a tag filter is still
 * offered here even though applying one drops to the v2 path, NG4/Q-065-05:
 * `isPhotoSoaActive` recomputes to `false`, `AlbumPanel.vue`'s own `v-if`
 * reactively swaps back to `PhotoThumbPanel.vue`) but mounts
 * `PhotoGridVirtual.vue` instead of `PhotoThumbPanelList.vue`/the
 * client-side `splitter.ts` timeline grouping — bucket headers come from
 * tier 1's `labels[]` instead (FR-065-09).
 */
import { useAlbumStore } from "@/stores/AlbumState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { Collapse } from "vue-collapsed";
import PhotoThumbPanelControl from "@/v8/components/gallery/albumModule/PhotoThumbPanelControl.vue";
import AlbumTagFilter from "@/v8/components/gallery/albumModule/AlbumTagFilter.vue";
import PhotoGridVirtual from "@/v8/components/gallery/albumModule/Virtualized/PhotoGridVirtual.vue";

const albumStore = useAlbumStore();
const modalStore = useTogglablesStateStore();
const { is_filters_visible } = storeToRefs(modalStore);

const props = defineProps<{
	header: string;
	selectedPhotos: string[];
}>();

const emits = defineEmits<{
	clicked: [id: string, event: MouseEvent];
	selected: [id: string, event: MouseEvent];
	contexted: [id: string, event: MouseEvent];
	toggleBuyMe: [id: string];
}>();

function handleTagFilterApply(payload: { tagIds: number[]; tagLogic: string }) {
	albumStore.setTagFilter(payload.tagIds, payload.tagLogic);
}

function handleTagFilterClear() {
	albumStore.clearTagFilter();
}
</script>
