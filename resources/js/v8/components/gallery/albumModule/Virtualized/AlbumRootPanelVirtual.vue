<template>
	<div v-if="props.header !== undefined && isGridDataReady && tileCount > 0" class="border-0 w-full px-4">
		<!-- Simple, always-expanded title — unlike AlbumThumbPanel.vue's collapsible
		     header, this doesn't offer a collapse/expand toggle (a deliberate,
		     bounded simplification for this addendum's virtualized sections).
		     Only "own" scope passes a header — "shared" scope's per-owner sticky
		     bucket headers (tier 1 labels, already owner display names) already
		     serve that purpose, an outer title here would be redundant. -->
		<div class="flex items-center gap-2 ltr:pr-4 rtl:pl-4 font-semibold text-highlighted py-2">
			{{ $t(props.header) }}
		</div>
	</div>
	<AlbumRootListViewVirtual
		v-if="album_view_mode === 'list' && tileCount > 0"
		:scope="props.scope"
		:selected-albums="props.selectedAlbums"
		@clicked="(e, id) => emits('clicked', e, id)"
		@selected="(e, id) => emits('selected', e, id)"
		@contexted="(e, id) => emits('contexted', e, id)"
	/>
	<AlbumRootGridVirtual
		v-else-if="isGridDataReady && tileCount > 0"
		:scope="props.scope"
		:selected-albums="props.selectedAlbums"
		@clicked="(e, id) => emits('clicked', e, id)"
		@selected="(e, id) => emits('selected', e, id)"
		@contexted="(e, id) => emits('contexted', e, id)"
	/>
</template>
<script setup lang="ts">
/**
 * Flag-on replacement for the root gallery's own/shared `<AlbumThumbPanel>`
 * calls (2026-09-02 root-SoA addendum) — root-scope fork of
 * `AlbumThumbPanelVirtual.vue`, dispatching grid vs. list the
 * same way, reading straight from `AlbumsState.ts`'s own/shared bucket-tier
 * state (selected via the `scope` prop) rather than `AlbumState.ts`/
 * `AlbumsState.ts.albums` unconditionally.
 */
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import AlbumRootGridVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumRootGridVirtual.vue";
import AlbumRootListViewVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumRootListViewVirtual.vue";

const lycheeStore = useLycheeStateStore();
const { album_view_mode } = storeToRefs(lycheeStore);
const albumsStore = useAlbumsStore();

const props = defineProps<{
	scope: App.Enum.AlbumListingScope;
	selectedAlbums: string[];
	/** Section title (i18n key), shown only for "own" scope — see template comment. */
	header?: string;
}>();

// See AlbumThumbPanelVirtual.vue's identical comment: wait for tier 1
// (buckets) to have resolved before ever mounting the grid, so it's built
// with its final row structure from the start.
const isGridDataReady = computed(() => (props.scope === "own" ? albumsStore.ownBucketsV3 !== undefined : albumsStore.sharedBucketsV3 !== undefined));
const tileCount = computed(() => (props.scope === "own" ? albumsStore.albums.length : albumsStore.sharedAlbumsV3.length));

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();
</script>
