import { computed, ref, watch, type ComputedRef, type Ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { RouteLocationNormalizedLoadedGeneric } from "vue-router";
import type { AlbumStore } from "@/stores/AlbumState";
import type { AlbumsStore } from "@/stores/AlbumsState";
import type { PhotosStore } from "@/stores/PhotosState";
import type { PhotoStore } from "@/stores/PhotoState";
import type { TogglablesStateStore } from "@/stores/ModalsState";
import type { LycheeStateStore } from "@/stores/LycheeState";
import type { ToastLike } from "@/composables/toast-contract";
import FaceDetectionService from "@/services/face-detection-service";
import UsersService from "@/services/users-service";
import { useUserStore } from "@/stores/UserState";
import { needSizeVariantsWatermark } from "@/utils/watermarkHelpers";
import type { SpotlightItem } from "./types";

export type SpotlightGalleryActionToggles = {
	toggleCreateAlbum: () => void;
	toggleCreateTagAlbum: () => void;
	toggleUpload: () => void;
	toggleApplyRenamer: () => void;
	toggleWatermarkConfirm: () => void;
};

/**
 * The 10 context-aware album/gallery actions (create/move/edit/share/upload/tracks/
 * watermark/scan-faces/apply-renamer), gated exactly like their existing non-Spotlight
 * triggers (Hero buttons, "+" menus, AlbumEdit's own section gates).
 *
 * The dialogs these actions open only ever get mounted while the matching route is
 * active (see Album.vue/Albums.vue), so gating visibility on the route is enough - no
 * dialog relocation is needed.
 */
export function useSpotlightGalleryActions(
	route: RouteLocationNormalizedLoadedGeneric,
	albumStore: AlbumStore,
	albumsStore: AlbumsStore,
	photosStore: PhotosStore,
	photoStore: PhotoStore,
	togglableStore: TogglablesStateStore,
	lycheeStore: LycheeStateStore,
	toast: ToastLike,
	initData: Ref<App.Http.Resources.Rights.GlobalRightsResource | undefined>,
	toggles: SpotlightGalleryActionToggles,
	close: () => void,
): ComputedRef<SpotlightItem[]> {
	const insideAlbum = computed(() => (route.name === "album" || route.name === "flow-album") && albumStore.isLoaded);
	const atGalleryRoot = computed(() => route.name === "gallery");

	// Mirrors AlbumEdit.vue's own `canShare` gate exactly, since the share action opens that
	// same drawer/section rather than its own dialog. `UsersService.count()` is cached, so this
	// costs nothing extra once AlbumEdit.vue (or another instance of this composable) has fetched it.
	// A guest can never share (no `can_share_with_users` right), so skip the request entirely
	// for them - SpotlightSearch.vue mounts this composable unconditionally on every page.
	// SpotlightSearch.vue mounts before LeftMenu's async userStore.load() resolves, so isLoggedIn
	// must be watched rather than checked once - a one-shot check would run while still logged out.
	const userStore = useUserStore();
	const numUsers = ref(0);
	watch(
		() => userStore.isLoggedIn,
		(isLoggedIn) => {
			if (isLoggedIn) {
				UsersService.count().then((data) => {
					numUsers.value = data.data;
				});
			} else {
				numUsers.value = 0;
			}
		},
		{ immediate: true },
	);

	return computed(() => {
		const items: SpotlightItem[] = [];
		const album = albumStore.album;

		if (
			(atGalleryRoot.value && albumsStore.rootRights?.can_upload) ||
			(insideAlbum.value && albumStore.rights?.can_upload && albumStore.config?.is_model_album)
		) {
			items.push({
				label: trans("gallery.menus.new_album"),
				icon: "lucide:folder",
				kind: "nav",
				onSelect: () => {
					close();
					toggles.toggleCreateAlbum();
				},
			});
		}

		if (atGalleryRoot.value && albumsStore.rootRights?.can_upload) {
			items.push({
				label: trans("gallery.menus.new_tag_album"),
				icon: "lucide:tags",
				kind: "nav",
				onSelect: () => {
					close();
					toggles.toggleCreateTagAlbum();
				},
			});
		}

		// Restricted to model albums with no photo open: MoveDialog's photo-open binding in
		// Album.vue always targets the current photo, leaving no slot for an album move, and
		// there's no existing precedent for moving a tag/smart/person album.
		if (insideAlbum.value && !photoStore.isLoaded && albumStore.rights?.can_move && albumStore.config?.is_model_album && album !== undefined) {
			items.push({
				label: trans("gallery.menus.move"),
				icon: "lucide:folder",
				kind: "nav",
				onSelect: () => {
					close();
					togglableStore.move_album_override = { id: album.id, title: album.title } as App.Http.Resources.Models.ThumbAlbumResource;
					togglableStore.is_move_visible = true;
				},
			});
		}

		if (insideAlbum.value && albumStore.rights?.can_edit) {
			items.push({
				label: trans("gallery.album.hero.edit"),
				icon: "lucide:settings",
				kind: "nav",
				onSelect: () => {
					close();
					togglableStore.is_album_edit_open = true;
				},
			});
		}

		if (insideAlbum.value && albumStore.rights?.can_share_with_users && numUsers.value > 1 && albumStore.config?.is_base_album) {
			items.push({
				label: trans("gallery.album.hero.share"),
				icon: "lucide:share-2",
				kind: "nav",
				onSelect: () => {
					close();
					togglableStore.is_share_section_pending = true;
					togglableStore.is_album_edit_open = true;
				},
			});
		}

		// Matches AlbumEdit.vue's own `canTracks` gate exactly: tracks are a model-album-only,
		// can_edit-gated section of the edit drawer, not their own dialog.
		if (insideAlbum.value && albumStore.rights?.can_edit && albumStore.config?.is_model_album) {
			items.push({
				label: trans("gallery.album.tracks.add"),
				icon: "lucide:route",
				kind: "nav",
				onSelect: () => {
					close();
					togglableStore.is_track_upload_pending = true;
					togglableStore.is_album_edit_open = true;
				},
			});
		}

		if ((atGalleryRoot.value && albumsStore.rootRights?.can_upload) || (insideAlbum.value && albumStore.rights?.can_upload)) {
			items.push({
				label: trans("gallery.menus.upload_photo"),
				icon: "lucide:upload",
				kind: "nav",
				onSelect: () => {
					close();
					toggles.toggleUpload();
				},
			});
		}

		if (
			insideAlbum.value &&
			albumStore.rights?.can_edit &&
			initData.value?.modules.is_watermarker_enabled &&
			photosStore.photos.some((p) => needSizeVariantsWatermark(p.size_variants))
		) {
			items.push({
				label: trans("gallery.album.hero.watermark"),
				icon: "lucide:barcode",
				kind: "nav",
				onSelect: () => {
					close();
					toggles.toggleWatermarkConfirm();
				},
			});
		}

		if (
			insideAlbum.value &&
			albumStore.rights?.can_edit &&
			lycheeStore.is_face_recognition_enabled &&
			photosStore.photos.length > 0 &&
			album !== undefined
		) {
			items.push({
				label: trans("people.scan_faces"),
				icon: "lucide:smile",
				kind: "nav",
				onSelect: () => {
					close();
					FaceDetectionService.scanAlbum(album.id)
						.then(() => {
							toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.scan_success"), life: 3000 });
						})
						.catch((e) => {
							toast.add({
								severity: "error",
								summary: trans("toasts.error"),
								detail: e.response?.data?.message || trans("toasts.error"),
								life: 3000,
							});
						});
				},
			});
		}

		if (insideAlbum.value && albumStore.rights?.can_edit && initData.value?.modules.is_mod_renamer_enabled) {
			items.push({
				label: trans("gallery.menus.apply_renamer"),
				icon: "lucide:pencil",
				kind: "nav",
				onSelect: () => {
					close();
					toggles.toggleApplyRenamer();
				},
			});
		}

		return items;
	});
}
