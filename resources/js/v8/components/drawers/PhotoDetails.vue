<template>
	<USidebar
		v-model:open="are_details_open"
		id="lychee_sidebar_container"
		:side="isLTR() ? 'right' : 'left'"
		:style="{ '--sidebar-width': '25rem' }"
		:ui="{
			container: 'border-l-0 bg-neutral-900' + (is_full_screen ? '' : ' top-(--ui-header-height) h-[calc(100%-var(--ui-header-height))]'),
			gap: 'h-[calc(100%-var(--ui-header-height))]',
			body: 'gap-0',
			header: 'text-center text-2xl font-bold',
		}"
		:title="$t('gallery.photo.details.about')"
	>
		<!-- Title etc info -->
		<div class="flex gap-3 mb-2">
			<div>
				<MiniIcon icon="image" class="h-12 w-12" />
			</div>
			<div class="flex flex-col">
				<span class="font-bold text-lg">{{ photoStore.photo!.title }}</span>
				<div class="flex gap-3 text-muted text-sm" id="photo-details-resolution-filesize">
					<span v-if="photoStore.photo!.preformatted.resolution" dir="ltr">{{ photoStore.photo!.preformatted.resolution }}</span>
					<span v-if="photoStore.photo!.precomputed.is_video && photoStore.photo!.preformatted.duration">
						{{ photoStore.photo!.preformatted.duration }}
					</span>
					<span v-if="photoStore.photo!.precomputed.is_video && photoStore.photo!.preformatted.fps">
						{{ photoStore.photo!.preformatted.fps }}
					</span>
					<span dir="ltr">{{ photoStore.photo!.preformatted.filesize }}</span>
				</div>
			</div>
		</div>
		<div v-if="photoStore.photo!.palette" class="flex gap-2 mb-4 ml-15">
			<ColourSquare :colour="photoStore.photo!.palette.colour_1" />
			<ColourSquare :colour="photoStore.photo!.palette.colour_2" />
			<ColourSquare :colour="photoStore.photo!.palette.colour_3" />
			<ColourSquare :colour="photoStore.photo!.palette.colour_4" />
			<ColourSquare :colour="photoStore.photo!.palette.colour_5" />
		</div>
		<!-- Dates stuff -->
		<div class="flex-col text-muted">
			<div class="flex gap-1 items-center" id="photo-details-created-at">
				<span class="w-6 inline-block">
					<UTooltip :text="$t('gallery.photo.details.uploaded')">
						<UIcon name="lucide:upload" />
					</UTooltip>
				</span>
				<span class="text-sm">{{ photoStore.photo!.preformatted.created_at }}</span>
			</div>
			<div v-if="photoStore.photo!.preformatted.taken_at" class="flex gap-1 items-start" id="photo-details-taken-at">
				<span class="w-6 inline-block">
					<UTooltip :text="$t('gallery.photo.details.captured')">
						<UIcon name="lucide:camera" class="w-6 pt-1 inline-block" />
					</UTooltip>
				</span>
				<span class="text-sm">
					{{ photoStore.photo!.preformatted.taken_at }}
					<span v-if="photoStore.photo!.precomputed.is_taken_at_modified" class="text-warning-600">*</span>
				</span>
			</div>
		</div>

		<!-- Description stuff -->
		<div v-if="photoStore.photo!.preformatted.description">
			<h2 class="text-highlighted text-base font-bold mt-4 mb-1">
				{{ $t("gallery.photo.details.description") }}
			</h2>
			<div class="prose dark:prose-invert prose-sm mb-4" v-html="photoStore.photo!.preformatted.description"></div>
		</div>

		<!-- Tags stuff -->
		<div v-if="photoStore.photo!.tags.length > 0">
			<h2 v-if="photoStore.photo!.tags.length > 0" class="text-highlighted text-base font-bold mt-4 mb-1">
				{{ $t("gallery.photo.details.tags") }}
			</h2>
			<span class="pb-2 flex flex-wrap">
				<template v-if="userStore.isLoggedIn">
					<RouterLink
						v-for="tag in photoStore.photo!.tags"
						:key="`tag-${tag.id}`"
						class="text-xs rounded-full py-1 px-2.5 mr-1.5 mb-2.5 bg-black/50 cursor-pointer hover:bg-black/70 transition-colors"
						:to="{ name: 'tag', params: { tagId: tag.id } }"
					>
						{{ tag.name }}
					</RouterLink>
				</template>
				<template v-else>
					<span
						v-for="tag in photoStore.photo!.tags"
						:key="`tag-${tag.id}`"
						class="text-xs rounded-full py-1 px-2.5 mr-1.5 mb-2.5 bg-black/50 cursor-default"
					>
						{{ tag.name }}
					</span>
				</template>
			</span>
		</div>

		<!-- Albums stuff -->
		<div>
			<h2 class="text-highlighted text-base font-bold mt-4 mb-1">
				{{ $t("gallery.photo.details.albums") }}
			</h2>
			<div v-if="albums_loading" class="flex items-center gap-2 text-muted text-sm">
				<LycheeLoadingIcon fast class="text-lg" />
				<span>{{ $t("gallery.photo.details.albums_loading") }}</span>
			</div>
			<div v-else-if="albums_error" class="text-sm text-muted">
				<UIcon name="lucide:triangle-alert" class="mr-1" />
				{{ $t("gallery.photo.details.albums_loading_error") }}
			</div>
			<div v-else-if="albums.length === 0" class="text-sm text-muted">
				{{ $t("gallery.photo.details.no_albums") }}
			</div>
			<ul v-else class="list-none p-0 m-0">
				<li v-for="album in albums" :key="album.id" class="mb-1">
					<a class="text-sm text-primary-500 cursor-pointer hover:underline" @click="navigateToAlbum(album.id)">
						{{ album.title }}
					</a>
				</li>
			</ul>
		</div>

		<!-- Exif stuff -->
		<div v-if="photoStore.photo!.precomputed.has_exif">
			<h2 class="text-highlighted text-base font-bold mt-4 mb-1">
				{{ $t("gallery.photo.details.exif_data") }}
			</h2>
			<div class="flex flex-wrap text-muted gap-y-0.5">
				<div v-if="photoStore.photo!.preformatted.model" class="flex w-full gap-2 items-center">
					<UTooltip :text="$t('gallery.photo.details.type')">
						<img src="../../../../img/icons/camera.png" class="dark:invert opacity-50 w-6 h-6" />
					</UTooltip>
					<span class="text-sm">{{ cameraModel }}</span>
				</div>
				<div v-if="photoStore.photo!.preformatted.lens" class="flex w-full gap-2 mb-2">
					<UTooltip :text="$t('gallery.photo.details.lens')">
						<img src="../../../../img/icons/lens.png" class="dark:invert opacity-50 w-6 h-6" />
					</UTooltip>
					<span class="text-sm">{{ photoStore.photo!.preformatted.lens }}</span>
				</div>
				<div class="flex w-1/2 gap-2 items-center">
					<UTooltip :text="$t('gallery.photo.details.aperture')">
						<MiniIcon icon="aperture" class="h-4 w-6" />
					</UTooltip>
					<span>ƒ / {{ photoStore.photo!.preformatted.aperture }}</span>
				</div>
				<div class="flex w-1/2 gap-2 items-center">
					<UTooltip :text="$t('gallery.photo.details.focal')">
						<img src="../../../../img/icons/focal.png" class="dark:invert opacity-50 w-6 h-5" />
					</UTooltip>
					<span class="text-sm" dir="ltr">{{ photoStore.photo!.preformatted.focal }}</span>
				</div>
				<div class="flex w-1/2 gap-2 items-center">
					<UTooltip :text="$t('gallery.photo.details.shutter')">
						<UIcon name="lucide:timer" class="h-6 w-6 text-base text-center pt-0.5 text-muted" />
					</UTooltip>
					<span class="text-sm" dir="ltr">{{ photoStore.photo!.preformatted.shutter }}</span>
				</div>
				<div class="flex w-1/2 gap-2 items-center">
					<img src="../../../../img/icons/iso.png" class="dark:invert opacity-50 w-6 h-6" />
					<span class="text-sm">{{ photoStore.photo!.preformatted.iso }}</span>
				</div>
			</div>
		</div>

		<div>
			<h2 v-if="photoStore.photo!.precomputed.has_location" class="col-span-2 text-highlighted text-base font-bold mt-4 mb-1">
				{{ $t("gallery.photo.details.location") }}
			</h2>
			<MapInclude
				v-if="props.isMapVisible"
				:latitude="photoStore.photo!.precomputed.latitude"
				:longitude="photoStore.photo!.precomputed.longitude"
			/>
			<template v-if="photoStore.photo!.precomputed.has_location">
				<div class="flex gap-x-2 text-muted">
					<span v-if="photoStore.photo!.preformatted.latitude" class="w-full text-sm">{{ photoStore.photo!.preformatted.latitude }}</span>
					<span v-if="photoStore.photo!.preformatted.longitude" class="w-full text-sm">{{ photoStore.photo!.preformatted.longitude }}</span>
					<span v-if="photoStore.photo!.preformatted.altitude" class="w-full text-sm">{{ photoStore.photo!.preformatted.altitude }}</span>
				</div>
				<div v-if="photoStore.photo!.preformatted.location" class="text-sm">
					{{ photoStore.photo!.preformatted.location }}
				</div>
			</template>
		</div>

		<div v-if="photoStore.photo!.preformatted.license">
			<h2 class="text-highlighted text-base font-bold mt-4 mb-1">
				{{ $t("gallery.photo.details.license") }}
			</h2>
			<span class="py-0.5 pl-0 text-sm text-muted">{{ photoStore.photo!.preformatted.license }}</span>
		</div>

		<div v-if="photoStore.photo!.statistics">
			<h2 class="text-highlighted text-base font-bold mt-4 mb-1">
				{{ $t("gallery.photo.details.stats.header") }}
			</h2>
			<div class="flex flex-wrap text-muted text-sm gap-y-0.5">
				<div class="w-1/2">
					<UTooltip :text="$t('gallery.photo.details.stats.number_of_visits')">
						<UIcon name="lucide:eye" class="mr-2" />
					</UTooltip>
					{{ photoStore.photo!.statistics.visit_count }}
				</div>
				<div class="w-1/2">
					<UTooltip :text="$t('gallery.photo.details.stats.number_of_downloads')">
						<UIcon name="lucide:cloud-download" class="mr-2" />
					</UTooltip>
					{{ photoStore.photo!.statistics.download_count }}
				</div>
				<div class="w-1/2">
					<UTooltip :text="$t('gallery.photo.details.stats.number_of_shares')">
						<UIcon name="lucide:share-2" class="mr-2" />
					</UTooltip>
					{{ photoStore.photo!.statistics.shared_count }}
				</div>
				<div class="w-1/2">
					<UTooltip :text="$t('gallery.photo.details.stats.number_of_favourites')">
						<UIcon name="lucide:heart" class="mr-2" />
					</UTooltip>
					{{ photoStore.photo!.statistics.favourite_count }}
				</div>
			</div>
		</div>

		<!-- Photo Rating Widget -->
		<PhotoRatingWidget
			v-if="photoStore.photo!.rating"
			:photo-id="photoStore.photo!.id"
			:rating="photoStore.photo!.rating"
			:key="`rating-${photoStore.photo!.id}`"
		/>

		<!-- People in this photo -->
		<div v-if="is_face_recognition_enabled && photoFaces.length > 0">
			<h2 class="text-highlighted text-base font-bold mt-4 mb-2">
				{{ $t("people.people_in_photo") }}
			</h2>
			<div class="flex gap-3 overflow-x-auto pb-1">
				<div
					v-for="face in photoFaces.filter((f) => !f.is_dismissed)"
					:key="face.id"
					class="flex flex-col items-center gap-1 shrink-0 cursor-pointer"
					@click.exact="openFaceAssignment(face)"
					@click.ctrl.exact.prevent="dismissFace(face)"
					@click.meta.exact.prevent="dismissFace(face)"
				>
					<img
						v-if="face.crop_url"
						:src="face.crop_url"
						:alt="face.person_name ?? $t('people.unknown')"
						class="w-12 h-12 rounded-full object-cover border-2"
						:class="{
							'border-default': !ctrlHeld || isTouchDev,
							'border-2 border-dashed border-red-500': ctrlHeld && !isTouchDev,
						}"
					/>
					<div v-else class="w-12 h-12 rounded-full bg-neutral-800 flex items-center justify-center border-2 border-default">
						<UIcon name="lucide:user" class="text-xl text-muted" />
					</div>
					<span class="text-xs text-muted text-center max-w-14 truncate">
						{{ face.person_name ?? "???" }}
					</span>
				</div>
			</div>
			<FaceAssignmentModal
				v-if="faceForAssignment"
				v-model:open="is_face_assignment_visible"
				:face="faceForAssignment"
				@assigned="onFaceUpdated"
				@dismissed="onFaceUpdated"
			/>
		</div>

		<LinksInclude v-if="is_details_links_enabled" />
	</USidebar>
</template>
<script setup lang="ts">
import { ref, watch, onMounted, computed, onUnmounted } from "vue";
import MapInclude from "@/v8/components/gallery/photoModule/MapInclude.vue";
import MiniIcon from "@/v8/components/icons/MiniIcon.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import ColourSquare from "@/v8/components/gallery/photoModule/ColourSquare.vue";
import PhotoRatingWidget from "@/v8/components/gallery/photoModule/PhotoRatingWidget.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import LinksInclude from "@/v8/components/gallery/photoModule/LinksInclude.vue";
import FaceAssignmentModal from "@/v8/components/modals/faceRecog/FaceAssignmentModal.vue";
import { usePhotoFacesStore } from "@/stores/PhotoFacesState";
import { storeToRefs } from "pinia";
import { usePhotoStore } from "@/stores/PhotoState";
import { useRouter } from "vue-router";
import PhotoService from "@/services/photo-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import FaceDetectionService from "@/services/face-detection-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useUserStore } from "@/stores/UserState";
import { useLtRorRtL } from "@/utils/Helpers";

const photoStore = usePhotoStore();
const router = useRouter();
const togglableStore = useTogglablesStateStore();
const toast = useAppToast();
const isTouchDev = isTouchDevice();
const userStore = useUserStore();
const { isLTR } = useLtRorRtL();

const props = defineProps<{
	isMapVisible: boolean;
}>();

const { are_details_open, is_full_screen, is_face_assignment_visible } = storeToRefs(togglableStore);

const lycheeState = useLycheeStateStore();
const { is_details_links_enabled, is_face_recognition_enabled } = storeToRefs(lycheeState);

// Albums section state
const albums = ref<App.Http.Resources.Models.PhotoAlbumResource[]>([]);
const albums_loading = ref(false);
const albums_error = ref(false);
const albums_cached_photo_id = ref<string | null>(null);

const cameraModel = computed(() => getModel());

function getModel() {
	if (photoStore.photo === null || photoStore.photo === undefined || photoStore.photo.preformatted.model === null) {
		return "";
	}

	if (photoStore.photo.preformatted.make === null || photoStore.photo.preformatted.make === "") {
		return photoStore.photo.preformatted.model ?? "";
	}

	if (photoStore.photo.preformatted.model.startsWith(photoStore.photo.preformatted.make)) {
		return photoStore.photo.preformatted.model;
	}

	return `${photoStore.photo.preformatted.make} - ${photoStore.photo.preformatted.model}`;
}

function fetchAlbums(photo_id: string) {
	if (albums_cached_photo_id.value === photo_id) {
		return;
	}

	albums_loading.value = true;
	albums_error.value = false;
	albums.value = [];

	PhotoService.albums(photo_id)
		.then((response) => {
			albums.value = response.data;
			albums_cached_photo_id.value = photo_id;
			albums_loading.value = false;
		})
		.catch(() => {
			albums_error.value = true;
			albums_loading.value = false;
		});
}

function navigateToAlbum(album_id: string) {
	togglableStore.are_details_open = false;
	photoStore.$reset();
	router.push({ name: "album", params: { albumId: album_id } });
}

watch(
	[are_details_open, () => photoStore.photo?.id],
	([is_open, photo_id]) => {
		if (is_open && photo_id !== undefined && photo_id !== null) {
			fetchAlbums(photo_id);
		}
	},
	{ immediate: true },
);

// Face circles section
const faceForAssignment = ref<App.Http.Resources.Models.FaceResource | undefined>(undefined);
const ctrlHeld = ref(false);
const facesStore = usePhotoFacesStore();
const photoFaces = computed(() => facesStore.get(photoStore.photo?.id ?? "").faces);

function onKeyDown(e: KeyboardEvent) {
	if (e.key === "Control" || e.key === "Meta") {
		ctrlHeld.value = true;
	}
}

function onKeyUp(e: KeyboardEvent) {
	if (e.key === "Control" || e.key === "Meta") {
		ctrlHeld.value = false;
	}
}

onMounted(() => {
	if (!isTouchDev) {
		window.addEventListener("keydown", onKeyDown);
		window.addEventListener("keyup", onKeyUp);
	}
});

onUnmounted(() => {
	if (!isTouchDev) {
		window.removeEventListener("keydown", onKeyDown);
		window.removeEventListener("keyup", onKeyUp);
	}
});

function openFaceAssignment(face: App.Http.Resources.Models.FaceResource) {
	faceForAssignment.value = face;
	is_face_assignment_visible.value = true;
}

function removeFaceFromPhoto(faceId: string) {
	const photoId = photoStore.photo?.id;
	if (photoId) {
		facesStore.removeFace(photoId, faceId);
	}
}

function dismissFace(face: App.Http.Resources.Models.FaceResource) {
	if (isTouchDev) {
		// Touch devices: do not dismiss via shortcut — open modal instead
		openFaceAssignment(face);
		return;
	}

	// Immediately remove from store for instant feedback
	removeFaceFromPhoto(face.id);

	FaceDetectionService.toggleDismissed(face.id)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.assignment.dismissed"), life: 3000 });
			onFaceUpdated();
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			// On error, reload to restore face
			if (photoStore.photo?.id) {
				facesStore.invalidate(photoStore.photo.id);
			}
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function onFaceUpdated() {
	// Remove dismissed face from store immediately if modal was used
	if (faceForAssignment.value) {
		removeFaceFromPhoto(faceForAssignment.value.id);
	}

	// Refresh face payload after assignment/dismiss in the modal.
	if (photoStore.photo?.id) {
		facesStore.invalidate(photoStore.photo.id);
	}
}

watch(
	() => photoStore.photo?.id,
	(photo_id) => {
		if (photo_id === undefined || (photoStore.photo?.face_count ?? 0) <= 0) {
			return;
		}

		facesStore.fetch(photo_id);
	},
	{ immediate: true },
);
</script>
