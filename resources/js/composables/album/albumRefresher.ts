import { ALL } from "@/config/constants";
import AlbumService from "@/services/album-service";
import { type AuthStore } from "@/stores/Auth";
import { computed, type Ref, ref } from "vue";
import { type SplitData, useSplitter } from "./splitter";

const { spliter, merge } = useSplitter();

export function useAlbumRefresher(albumId: Ref<string>, photoId: Ref<string | undefined>, auth: AuthStore, isLoginOpen: Ref<boolean>) {
	const isPasswordProtected = ref(false);
	const isLoading = ref(false);

	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
	const modelAlbum = ref<App.Http.Resources.Models.AlbumResource | undefined>(undefined);
	const tagAlbum = ref<App.Http.Resources.Models.TagAlbumResource | undefined>(undefined);
	const smartAlbum = ref<App.Http.Resources.Models.SmartAlbumResource | undefined>(undefined);
	const album = computed(() => modelAlbum.value || tagAlbum.value || smartAlbum.value);

	const transition = ref<"slide-next" | "slide-previous">("slide-next");
	const photo = ref<App.Http.Resources.Models.PhotoResource | undefined>(undefined);
	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
	const photosTimeline = ref<SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined>(undefined);

	const config = ref<App.Http.Resources.GalleryConfigs.AlbumConfig | undefined>(undefined);
	const rights = computed(() => album.value?.rights ?? undefined);

	const current_page = ref(1);
	const from = ref(0);
	const per_page = ref(0);
	const total = ref(0);

	function loadUser(): Promise<void> {
		return auth.getUser().then((data: App.Http.Resources.Models.UserResource) => {
			user.value = data;
		});
	}

	function hasPagination (
			resource: App.Http.Resources.Models.AbstractAlbumResource['resource']
		): resource is App.Http.Resources.Models.UnTaggedSmartAlbumResource {
			return resource !== null && 'from' in resource;
		}

	function loadAlbum(page: number = 1): Promise<void> {
		if (albumId.value === ALL) {
			return Promise.resolve();
		}

		if (page !== undefined) {
			current_page.value = page;
		}

		isLoading.value = true;

    	const first = (current_page.value - 1) * per_page.value;

		return AlbumService.get(albumId.value, first, per_page.value)
			.then((data) => {
				isPasswordProtected.value = false;
				config.value = data.data.config;
				modelAlbum.value = undefined;
				tagAlbum.value = undefined;
				smartAlbum.value = undefined;
				photosTimeline.value = undefined;

                const albumPhotos = data.data.resource?.photos || [];
				if (hasPagination(data.data.resource)) {
					from.value = Number(data.data.resource.from ?? 0);
					per_page.value = Number(data.data.resource.per_page ?? 0);
					total.value = Number(data.data.resource.total ?? 0);
					current_page.value = Number(data.data.resource.current_page ?? 1);
				}

				// So what is going on here?
				// The problem is that the ordering of the photos from the API is not necessarily the same
				// as the ordering of the photos in the timeline. The timeline is constructed from the photos
				// taken_at data and if not provided, created_at data.
				// This is split into different chunks based on the granularity.
				// If the ordering is done by created_at, and the order of the photos match, we do not have problems.
				// But if one of the photos has a different taken_at date, that does not match the order, then all the following
				// photos are "moved" to different place which does not match the original index ordering.
				//
				// When we click on a photo, the index returned refers to the original ordering of the photos.
				// As a result, if the timeline is enabled, we first do the split and then merge the photos so that the
				// ordering is updated to reflect the timeline.
				//
				// Note that this is not something that can be fixed in the backend as we would need to assume that all the dates are
				// set properly. Furthermore, this would make the functionality unavailable if sorting by title is done.
				// By doing it in the front-end, we are able to display the photos by blocks of time,
				// and within the block, the ordering is done as expected.
				if (data.data.config.is_photo_timeline_enabled) {
					photosTimeline.value = spliter(
						albumPhotos,
						(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
						(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
					);
					photos.value = merge(photosTimeline.value);
				} else {
					// We are not using the timeline, so we can just use the photos as is.
					photos.value = album.value?.photos ?? albumPhotos ?? [];
				}

				if (data.data.config.is_model_album) {
					modelAlbum.value = data.data.resource as App.Http.Resources.Models.AlbumResource;
				} else if (data.data.config.is_base_album) {
					tagAlbum.value = data.data.resource as App.Http.Resources.Models.TagAlbumResource;
				} else {
					smartAlbum.value = data.data.resource as App.Http.Resources.Models.SmartAlbumResource;

					if (smartAlbum.value) {
						smartAlbum.value.photos = photos.value;
					}
				}
			})
			.catch((error) => {
				if (error.response && error.response.status === 401 && error.response.data.message === "Password required") {
					isPasswordProtected.value = true;
				} else if (error.response && error.response.status === 403 && error.response.data.message === "Password required") {
					isPasswordProtected.value = true;
				} else if (error.response && error.response.status === 401) {
					isLoginOpen.value = true;
				} else {
					console.error(error);
				}
			})
			.finally(() => {
				isLoading.value = false;
			});
	}

	function refresh(): Promise<void> {
		let page = 1;
		if (from.value > 0 && per_page.value > 0) {
			page = Math.floor(from.value / per_page.value) + 1;
		}

		return Promise.all([loadUser(), loadAlbum(page)]).then(() => {
			if (photoId.value) {
				photo.value = photos.value.find((photo: App.Http.Resources.Models.PhotoResource) => photo.id === photoId.value);
			} else {
				photo.value = undefined;
			}
		});
	}

	function setTransition(photo_id: string | undefined | null) {
		if (photo_id === undefined || photo_id === null) {
			return;
		}

		if (photo.value !== undefined) {
			transition.value = photo.value.next_photo_id === photo_id ? "slide-next" : "slide-previous";
		} else {
			transition.value = "slide-next";
		}
	}

	return {
		isPasswordProtected,
		isLoading,
		albumId,
		user,
		modelAlbum,
		tagAlbum,
		smartAlbum,
		album,
		rights,
		transition,
		photo,
		photos,
		photosTimeline,
		config,
		from,
		per_page,
		total,
		loadUser,
		loadAlbum,
		refresh,
		setTransition,
	};
}
