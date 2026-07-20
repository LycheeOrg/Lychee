import { useLycheeStateStore } from "@/stores/LycheeState";
import { isSmartAlbumId } from "@/v8/utils/smartAlbum";
import { storeToRefs } from "pinia";
import { computed, type Ref } from "vue";

export function useAlbumFlags(album: Ref<App.Http.Resources.Models.ThumbAlbumResource>) {
	const lycheeStore = useLycheeStateStore();
	const {
		is_smart_album_flags_enabled,
		is_album_flags_enabled,
		is_sensitive_flag_enabled,
		is_public_hidden_flag_enabled,
		is_public_visible_flag_enabled,
		is_password_flag_enabled,
	} = storeToRefs(lycheeStore);

	const isSmartAlbum = computed(() => isSmartAlbumId(album.value.id));
	const scopeFlagsEnabled = computed(() => (isSmartAlbum.value ? is_smart_album_flags_enabled.value : is_album_flags_enabled.value));

	const showSensitiveFlag = computed(() => scopeFlagsEnabled.value && is_sensitive_flag_enabled.value && album.value.is_nsfw);

	const showPublicHiddenFlag = computed(
		() => scopeFlagsEnabled.value && album.value.is_public && album.value.is_link_required && is_public_hidden_flag_enabled.value,
	);

	const showPublicVisibleFlag = computed(
		() => scopeFlagsEnabled.value && album.value.is_public && !album.value.is_link_required && is_public_visible_flag_enabled.value,
	);

	const showPasswordFlag = computed(() => scopeFlagsEnabled.value && is_password_flag_enabled.value && album.value.is_password_required);

	return {
		isSmartAlbum,
		scopeFlagsEnabled,
		showSensitiveFlag,
		showPublicHiddenFlag,
		showPublicVisibleFlag,
		showPasswordFlag,
	};
}
