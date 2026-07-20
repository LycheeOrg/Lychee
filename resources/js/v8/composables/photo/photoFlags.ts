import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useUserStore } from "@/stores/UserState";
import { storeToRefs } from "pinia";
import { computed, type Ref } from "vue";

export function usePhotoFlags(photo: Ref<App.Http.Resources.Models.PhotoResource>, isCoverId: Ref<boolean>, isHeaderId: Ref<boolean>) {
	const lycheeStore = useLycheeStateStore();
	const userStore = useUserStore();
	const albumStore = useAlbumStore();
	const albumsStore = useAlbumsStore();

	const { is_highlighted_flag_enabled, is_cover_id_flag_enabled, is_header_id_flag_enabled, is_validated_flag_enabled } =
		storeToRefs(lycheeStore);

	const canHighlight = computed(() => albumsStore.rootRights?.can_highlight || albumStore.rights?.can_edit);

	const showHighlightedFlag = computed(() => is_highlighted_flag_enabled.value && photo.value.is_highlighted && canHighlight.value);

	const showCoverIdFlag = computed(() => is_cover_id_flag_enabled.value && userStore.isLoggedIn && isCoverId.value);

	const showHeaderIdFlag = computed(() => is_header_id_flag_enabled.value && userStore.isLoggedIn && isHeaderId.value);

	const showValidatedFlag = computed(() => is_validated_flag_enabled.value && !photo.value.is_validated);

	return {
		showHighlightedFlag,
		showCoverIdFlag,
		showHeaderIdFlag,
		showValidatedFlag,
	};
}
