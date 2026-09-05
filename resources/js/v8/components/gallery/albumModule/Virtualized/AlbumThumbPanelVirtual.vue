<template>
	<AlbumListViewVirtual
		v-if="album_view_mode === 'list'"
		:selected-albums="props.selectedAlbums"
		@clicked="(e, id) => emits('clicked', e, id)"
		@selected="(e, id) => emits('selected', e, id)"
		@contexted="(e, id) => emits('contexted', e, id)"
	/>
	<AlbumThumbGridVirtual
		v-else-if="isGridDataReady"
		:selected-albums="props.selectedAlbums"
		@clicked="(e, id) => emits('clicked', e, id)"
		@selected="(e, id) => emits('selected', e, id)"
		@contexted="(e, id) => emits('contexted', e, id)"
	/>
</template>
<script setup lang="ts">
/**
 * Flag-on replacement for AlbumThumbPanel.vue — dispatches
 * grid vs. list the same way AlbumThumbPanel.vue does (`album_view_mode`),
 * but each branch is now a self-contained virtualizer reading straight from
 * AlbumState.ts/AlbumsState.ts (bucketsV3/boundariesV3/albums) rather than
 * taking them as props: there's no client-side timeline re-splitting here
 * (bucket-driven headers replace AlbumThumbPanel.vue's own
 * splitter.ts usage entirely), so the prop surface collapses to just
 * selectedAlbums + the three propagated events.
 */
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useAlbumStore } from "@/stores/AlbumState";
import AlbumThumbGridVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumThumbGridVirtual.vue";
import AlbumListViewVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumListViewVirtual.vue";

const lycheeStore = useLycheeStateStore();
const { album_view_mode } = storeToRefs(lycheeStore);
const albumStore = useAlbumStore();

// The grid (not list — its rows are all a uniform, bucket-independent
// height) mounts its virtualizer once, on first render, with whatever
// boundaries/albums exist at that moment. If albums arrive before the
// separate /children/buckets response does, the grid would mount with the
// flat, unbucketed fallback and then have its row structure reshaped
// (headers inserted, rows re-split into buckets) once boundaries resolve —
// a change tanstack-virtual's own internal caching doesn't fully recover
// from cleanly (rows keep the wrong height/position; virtualizer.measure()
// alone wasn't enough, confirmed directly). Waiting for bucketsV3 to have
// resolved (bucketable or not) before ever mounting the grid means it's
// built with its final row structure from the start, with no such
// after-the-fact reshaping to recover from.
const isGridDataReady = computed(() => albumStore.bucketsV3 !== undefined);

const props = defineProps<{
	selectedAlbums: string[];
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();
</script>
