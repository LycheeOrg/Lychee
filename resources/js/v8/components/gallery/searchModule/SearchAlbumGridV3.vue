<template>
	<div v-if="albums.length > 0" class="w-full">
		<h2 class="w-full px-3 py-2 font-semibold text-toned text-lg">{{ props.header }}</h2>
		<div class="grid gap-4 px-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
			<AlbumThumbVirtual
				v-for="album in albums"
				:key="album.id"
				:album="album"
				:cover_id="null"
				:is-selected="props.selectedAlbums.includes(album.id)"
				@click="(e: MouseEvent) => emits('clicked', e, album.id)"
				@contextmenu="(e: MouseEvent) => emits('contexted', e, album.id)"
				@touch-select="(e: MouseEvent) => emits('selected', e, album.id)"
			/>
		</div>
	</div>
</template>

<script setup lang="ts">
/**
 * Album half of the v3 search result (Feature 069, FR-069-07).
 *
 * Deliberately **not** virtualized, unlike its album-browsing counterpart
 * `AlbumThumbGridVirtual.vue`. That component reads its tiles, buckets and
 * boundaries straight out of `AlbumState`/`AlbumsState` rather than from props,
 * so reusing it here would mean either feeding a search result through the
 * album-browsing stores (exactly the state bleed FR-069-20 exists to stop — and
 * it would read a *previously browsed album's* bucket boundaries) or adding a
 * prop-override path to a shipped component for no real gain.
 *
 * No gain, because the premise of Q-069-03 is that album hits are few: that is
 * the same reason the album half has no bucket tier. A plain responsive grid is
 * the right shape for a handful of tiles.
 *
 * The tile itself is reused as-is — `AlbumThumbVirtual.vue` is fully
 * prop-driven and already renders its cover through `<Thumb>`, i.e. through the
 * v3 Asset endpoint, which is what tier 2's `cover_ids` are for (NG8).
 */
import { computed } from "vue";
import AlbumThumbVirtual from "@/v8/components/gallery/albumModule/Virtualized/AlbumThumbVirtual.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useSearchStore } from "@/stores/SearchState";

const searchStore = useSearchStore();
const lycheeStore = useLycheeStateStore();

const props = defineProps<{
	header: string;
	selectedAlbums: string[];
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

// Mirrors the NSFW filtering every other album grid applies before rendering.
const albums = computed(() => searchStore.albumTilesV3.filter((album) => !album.is_nsfw || lycheeStore.are_nsfw_visible));
</script>
