/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import SettingsService from "@/services/settings-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAppToast } from "@/v8/composables/useAppToast";
import {
	DEFAULT_GALLERY_SIZES,
	aspectRatioToCss,
	changedGallerySizes,
	clampGallerySize,
	cssToAspectRatio,
	isNumericGallerySize,
	parseGallerySizes,
	type GallerySizeKey,
	type GallerySizes,
} from "@/v8/utils/gallerySizes";

/**
 * Feature 110 (FR-110-05, FR-110-10, FR-110-11): stored values, draft values
 * and save/revert of the 9 size settings, shared by the preview page and the
 * live drawer. Knows nothing about how the draft is shown.
 */
export function useGallerySizesDraft() {
	const lycheeStore = useLycheeStateStore();
	const layoutStore = useLayoutStore();
	const albumsStore = useAlbumsStore();
	const albumStore = useAlbumStore();
	const toast = useAppToast();

	/** The session's current values: what the gallery shows before the settings are loaded. */
	function sessionValues(): GallerySizes {
		const layout = layoutStore.config;
		return {
			album_thumb_min_width: lycheeStore.album_thumb_min_width,
			album_thumb_gap: lycheeStore.album_thumb_gap,
			default_album_thumb_aspect_ratio:
				cssToAspectRatio(albumsStore.rootConfig?.album_thumb_css_aspect_ratio) ?? DEFAULT_GALLERY_SIZES.default_album_thumb_aspect_ratio,
			number_albums_per_row_mobile: lycheeStore.number_albums_per_row_mobile,
			photo_layout_gap: layout?.photo_layout_gap ?? DEFAULT_GALLERY_SIZES.photo_layout_gap,
			photo_layout_justified_row_height: layout?.photo_layout_justified_row_height ?? DEFAULT_GALLERY_SIZES.photo_layout_justified_row_height,
			photo_layout_masonry_column_width: layout?.photo_layout_masonry_column_width ?? DEFAULT_GALLERY_SIZES.photo_layout_masonry_column_width,
			photo_layout_grid_column_width: layout?.photo_layout_grid_column_width ?? DEFAULT_GALLERY_SIZES.photo_layout_grid_column_width,
			photo_layout_square_column_width: layout?.photo_layout_square_column_width ?? DEFAULT_GALLERY_SIZES.photo_layout_square_column_width,
		};
	}

	const stored = ref<GallerySizes>(sessionValues());
	const draft = ref<GallerySizes>({ ...stored.value });
	const editable = ref<GallerySizeKey[]>([]);
	const loading = ref(true);
	const loadError = ref(false);
	const saving = ref(false);

	const changes = computed(() => changedGallerySizes(stored.value, draft.value, editable.value));
	const isDirty = computed(() => changes.value.length > 0);

	function load(): Promise<void> {
		loading.value = true;
		loadError.value = false;
		return SettingsService.getAll()
			.then((response) => {
				const parsed = parseGallerySizes(response.data, sessionValues());
				stored.value = parsed.values;
				draft.value = { ...parsed.values };
				editable.value = parsed.editable;
			})
			.catch(() => {
				loadError.value = true;
				toast.add({ severity: "error", summary: trans("gallery_sizes.load_error"), life: 5000 });
			})
			.finally(() => {
				loading.value = false;
			});
	}

	function set<K extends GallerySizeKey>(key: K, value: GallerySizes[K]): void {
		const next = isNumericGallerySize(key) ? clampGallerySize(key, value as number) : value;
		draft.value = { ...draft.value, [key]: next };
	}

	function revert(): void {
		draft.value = { ...stored.value };
	}

	/** One setting back to its saved value (double-click on its label). */
	function resetKey(key: GallerySizeKey): void {
		draft.value = { ...draft.value, [key]: stored.value[key] };
	}

	/** Re-reads what the gallery shows now (the drawer opens long after the app created this composable). */
	function resetToSession(): void {
		stored.value = sessionValues();
		draft.value = { ...stored.value };
	}

	/** FR-110-11: the session shows the saved values without a reload. */
	function applyToStores(sizes: GallerySizes): void {
		lycheeStore.album_thumb_min_width = sizes.album_thumb_min_width;
		lycheeStore.album_thumb_gap = sizes.album_thumb_gap;
		lycheeStore.number_albums_per_row_mobile = sizes.number_albums_per_row_mobile;
		layoutStore.config = {
			photo_layout_gap: sizes.photo_layout_gap,
			photo_layout_justified_row_height: sizes.photo_layout_justified_row_height,
			photo_layout_masonry_column_width: sizes.photo_layout_masonry_column_width,
			photo_layout_grid_column_width: sizes.photo_layout_grid_column_width,
			photo_layout_square_column_width: sizes.photo_layout_square_column_width,
		};
		const css = aspectRatioToCss(sizes.default_album_thumb_aspect_ratio);
		if (albumsStore.rootConfig !== undefined) {
			albumsStore.rootConfig.album_thumb_css_aspect_ratio = css;
		}
		// The open album shows the default only when it has no aspect ratio of its own.
		const hasOwnRatio = (albumStore.modelAlbum?.editable?.aspect_ratio ?? null) !== null;
		if (albumStore.config !== undefined && !hasOwnRatio) {
			albumStore.config.album_thumb_css_aspect_ratio = css;
		}
	}

	function save(): Promise<boolean> {
		if (!isDirty.value) {
			return Promise.resolve(true);
		}
		saving.value = true;
		const saved = { ...draft.value };
		return SettingsService.setConfigs({ configs: changes.value })
			.then(() => {
				stored.value = saved;
				applyToStores(saved);
				toast.add({ severity: "success", summary: trans("gallery_sizes.saved"), life: 3000 });
				return true;
			})
			.catch((e) => {
				toast.add({ severity: "error", summary: trans("gallery_sizes.save_error"), detail: e?.response?.data?.message, life: 5000 });
				return false;
			})
			.finally(() => {
				saving.value = false;
			});
	}

	return { stored, draft, editable, loading, loadError, saving, isDirty, load, set, revert, resetKey, resetToSession, save };
}

export type GallerySizesDraft = ReturnType<typeof useGallerySizesDraft>;
