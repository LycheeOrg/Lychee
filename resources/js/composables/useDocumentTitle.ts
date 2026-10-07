import { watchEffect } from "vue";
import { useRoute } from "vue-router";
import { trans } from "laravel-vue-i18n";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useLycheeStateStore } from "@/stores/LycheeState";

/**
 * Keeps the browser tab title in sync with the photo currently open in the
 * full-screen viewer, then the current album, then the site title.
 *
 * Every gallery panel (Album, Timeline, Search, Tag, PersonDetail, ...) reads
 * and writes the same shared `photoStore`, clearing `photo` via `reset()`
 * when the viewer is closed - so watching it here, once, is enough to cover
 * every place the viewer can be opened without touching each panel.
 */
export function useDocumentTitle(): void {
	const route = useRoute();
	const albumStore = useAlbumStore();
	const photoStore = usePhotoStore();
	const lycheeStore = useLycheeStateStore();

	watchEffect(() => {
		const siteTitle = trans(lycheeStore.title);
		const album = albumStore.album;
		// The album store can retain metadata after navigating to another panel.
		const albumTitle =
			(route.name === "album" || route.name === "flow-album") && album !== undefined && album.id === route.params.albumId
				? trans(album.title)
				: "";
		document.title = photoStore.photo?.title || (albumTitle ? `${albumTitle} · ${siteTitle}` : siteTitle);
	});
}
