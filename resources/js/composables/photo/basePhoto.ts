import { computed, Ref, ref } from "vue";

export function usePhotoBaseFunction(photoId: Ref<string>, videoElement: Ref<HTMLVideoElement | null>) {
	const photo = ref<App.Http.Resources.Models.PhotoResource | undefined>(undefined);
	const album = ref<App.Http.Resources.Models.AbstractAlbumResource | null>(null);
	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);

	function hasPrevious(): boolean {
		return photo.value?.previous_photo_id !== null;
	}

	function hasNext(): boolean {
		return photo.value?.next_photo_id !== null;
	}

	const previousStyle = computed(() => {
		if (!hasPrevious()) {
			return "";
		}

		const previousId = photo.value?.previous_photo_id;
		const previousPhoto = photos.value.find((p) => p.id === previousId);
		if (previousPhoto === undefined) {
			return "";
		}
		return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + previousPhoto.size_variants.thumb?.url + "')";
	});

	const nextStyle = computed(() => {
		if (!hasNext()) {
			return "";
		}

		const nextId = photo.value?.next_photo_id;
		const nextPhoto = photos.value.find((p) => p.id === nextId);
		if (nextPhoto === undefined) {
			return "";
		}
		return "background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('" + nextPhoto.size_variants.thumb?.url + "')";
	});

	const srcSetMedium = computed(() => {
		const medium = photo.value?.size_variants.medium ?? null;
		const medium2x = photo.value?.size_variants.medium2x ?? null;
		if (medium === null || medium2x === null) {
			return "";
		}

		return `${medium.url} ${medium.width}w, ${medium2x.url} ${medium2x.width}w`;
	});

	const style = computed(() => {
		if (!photo.value?.precomputed.is_livephoto) {
			return "background-image: url(" + photo.value?.size_variants.small?.url + ")";
		}
		if (photo.value?.size_variants.medium !== null) {
			return "width: " + photo.value?.size_variants.medium.width + "px; height: " + photo.value?.size_variants.medium.height + "px";
		}
		if (photo.value?.size_variants.original === null) {
			return "";
		}
		return "width: " + photo.value?.size_variants.original.width + "px; height: " + photo.value?.size_variants.original.height + "px";
	});

	const imageViewMode = computed(() => {
		if (photo.value?.precomputed.is_video) {
			return 0;
		}
		if (photo.value?.precomputed.is_raw) {
			if (photo.value?.size_variants.medium !== null) {
				return 2;
			}
			return 1;
		}

		if (!photo.value?.precomputed.is_livephoto) {
			if (photo.value?.size_variants.medium !== null) {
				return 2;
			}
			return 3;
		}
		if (photo.value?.size_variants.medium !== null) {
			return 4;
		}
		return 5;
	});

	function refresh(): void {
		photo.value = photos.value.find((p: App.Http.Resources.Models.PhotoResource) => p.id === photoId.value);

		// handle videos.
		const videoElementValue = videoElement.value;
		if (photo.value?.precomputed?.is_video && videoElementValue) {
			videoElementValue.src = photo.value?.size_variants?.original?.url ?? "";
			videoElementValue.load();
		}
	}

	return {
		photo,
		album,
		photos,
		previousStyle,
		nextStyle,
		srcSetMedium,
		style,
		imageViewMode,
		refresh,
		hasPrevious,
		hasNext,
	};
}
