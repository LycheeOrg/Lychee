import { type AuthStore } from "@/stores/Auth";
import { type Ref, ref } from "vue";
import { type SplitData } from "@/composables/album/splitter";
import TagsService from "@/services/tags-service";

export function useTagRefresher(tagId: Ref<string>, photoId: Ref<string | undefined>, auth: AuthStore, isLoginOpen: Ref<boolean>) {
	const isLoading = ref(false);
	const user = ref<App.Http.Resources.Models.UserResource | undefined>(undefined);
	const transition = ref<"slide-next" | "slide-previous">("slide-next");
	const tag = ref<App.Http.Resources.Tags.TagResource | undefined>(undefined);
	const photo = ref<App.Http.Resources.Models.PhotoResource | undefined>(undefined);
	const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
	const photosTimeline = ref<SplitData<App.Http.Resources.Models.PhotoResource>[] | undefined>(undefined);

	const photoLayout = ref<App.Enum.PhotoLayoutType>("justified");

	function loadUser(): Promise<void> {
		return auth.getUser().then((data: App.Http.Resources.Models.UserResource) => {
			user.value = data;
		});
	}

	function loadTag(): Promise<void> {
		isLoading.value = true;

		return TagsService.get(tagId.value)
			.then((data) => {
				photos.value = data.data.photos ?? [];
				tag.value = { name: data.data.name, id: data.data.id, num: photos.value.length };
			})
			.catch((error) => {
				if (error.response && error.response.status === 401) {
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
		return Promise.all([loadUser(), loadTag()]).then(() => {
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
		tag,
		isLoading,
		user,
		transition,
		photo,
		photos,
		photosTimeline,
		photoLayout,
		loadUser,
		loadTag,
		refresh,
		setTransition,
	};
}
