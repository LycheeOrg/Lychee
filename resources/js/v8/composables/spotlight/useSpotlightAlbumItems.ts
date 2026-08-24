import { computed, type ComputedRef } from "vue";
import { Router } from "vue-router";
import type { AlbumListStore } from "@/stores/AlbumListState";
import type { SpotlightItem } from "./types";

export function useSpotlightAlbumItems(albumListStore: AlbumListStore, router: Router, close: () => void): ComputedRef<SpotlightItem[]> {
	return computed(() =>
		albumListStore.rows.map((row) => ({
			label: row.title,
			description: albumListStore.buildBreadcrumb(row.id),
			kind: "album" as const,
			albumId: row.id,
			photoId: row.coverId,
			onSelect: () => {
				close();
				router.push({ name: "album", params: { albumId: row.id } });
			},
		})),
	);
}
