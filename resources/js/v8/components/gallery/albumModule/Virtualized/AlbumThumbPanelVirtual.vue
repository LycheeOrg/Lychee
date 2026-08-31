<template>
	<AlbumListViewVirtual
		v-if="album_view_mode === 'list'"
		:selected-albums="props.selectedAlbums"
		@clicked="(e, id) => emits('clicked', e, id)"
		@selected="(e, id) => emits('selected', e, id)"
		@contexted="(e, id) => emits('contexted', e, id)"
	/>
	<AlbumThumbGridVirtual
		v-else
		:selected-albums="props.selectedAlbums"
		@clicked="(e, id) => emits('clicked', e, id)"
		@selected="(e, id) => emits('selected', e, id)"
		@contexted="(e, id) => emits('contexted', e, id)"
	/>
</template>
<script setup lang="ts">
/**
 * Flag-on replacement for AlbumThumbPanel.vue (FR-062-01) — dispatches
 * grid vs. list the same way AlbumThumbPanel.vue does (`album_view_mode`),
 * but each branch is now a self-contained virtualizer reading straight from
 * AlbumState.ts/AlbumsState.ts (bucketsV3/boundariesV3/albums) rather than
 * taking them as props: there's no client-side timeline re-splitting here
 * (FR-062-02's bucket-driven headers replace AlbumThumbPanel.vue's own
 * splitter.ts usage entirely), so the prop surface collapses to just
 * selectedAlbums + the three propagated events.
 */
import { storeToRefs } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumThumbGridVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumThumbGridVirtual.vue";
import AlbumListViewVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumListViewVirtual.vue";

const lycheeStore = useLycheeStateStore();
const { album_view_mode } = storeToRefs(lycheeStore);

const props = defineProps<{
	selectedAlbums: string[];
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();
</script>
