import { LycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";
import { computed, ComputedRef, Ref } from "vue";

export function useSearchComputed(
	config: Ref<App.Http.Resources.GalleryConfigs.AlbumConfig | undefined>,
	album: ComputedRef<
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined
	>,
	albums: Ref<App.Http.Resources.Models.ThumbAlbumResource[] | undefined>,
	photos: Ref<App.Http.Resources.Models.PhotoResource[] | undefined>,
	lycheeStore: LycheeStateStore,
) {
	const albumsForSelection = computed(() => albums.value ?? []);
	const photosForSelection = computed(() => photos.value ?? []);

	const noData = computed<boolean>(
		() => albums.value !== undefined && photos.value !== undefined && albums.value.length === 0 && photos.value.length === 0,
	);

	const configForMenu = computed<App.Http.Resources.GalleryConfigs.AlbumConfig>(() => {
		if (config.value !== undefined) {
			return config.value;
		}
		return {
			is_base_album: false,
			is_model_album: false,
			is_accessible: true,
			is_password_protected: false,
			is_map_accessible: false,
			is_mod_frame_enabled: false,
			is_search_accessible: false,
			is_nsfw_warning_visible: false,
			album_thumb_css_aspect_ratio: "aspect-square",
			photo_layout: "justified",
			is_album_timeline_enabled: false,
			is_photo_timeline_enabled: false,
		};
	});

	const title = computed<string>(() => {
		if (album.value === undefined) {
			return trans(lycheeStore.title);
		}
		return album.value.title;
	});

	return {
		albumsForSelection,
		photosForSelection,
		noData,
		configForMenu,
		title,
	};
}
