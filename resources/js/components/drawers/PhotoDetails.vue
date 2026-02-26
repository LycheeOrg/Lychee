<template>
	<aside
		id="lychee_sidebar_container"
		:class="{
			'h-full relative transition-all overflow-x-clip overflow-y-scroll bg-bg-800': true,
			'w-95': areDetailsOpen,
			'w-0 ltr:translate-x-full rtl:-translate-x-full': !areDetailsOpen,
		}"
	>
		<Card v-if="photoStore.photo" id="lychee_sidebar" class="w-95 h-full ltr:pr-4 rtl:pl-4 wrap-break-word">
			<template #content>
				<div class="flex flex-col mt-8">
					<h1 class="text-center text-2xl font-bold my-4">
						{{ $t("gallery.photo.details.about") }}
					</h1>
					<!-- Title etc info -->
					<div class="flex gap-3 mb-2">
						<div>
							<MiniIcon icon="image" class="h-12 w-12" />
						</div>
						<div class="flex flex-col">
							<span class="font-bold text-lg">{{ photoStore.photo.title }}</span>
							<div class="flex gap-3 text-muted-color text-sm" id="photo-details-resolution-filesize">
								<span v-if="photoStore.photo.preformatted.resolution" dir="ltr">{{ photoStore.photo.preformatted.resolution }}</span>
								<span v-if="photoStore.photo.precomputed.is_video && photoStore.photo.preformatted.duration">
									{{ photoStore.photo.preformatted.duration }}
								</span>
								<span v-if="photoStore.photo.precomputed.is_video && photoStore.photo.preformatted.fps">
									{{ photoStore.photo.preformatted.fps }}
								</span>
								<span dir="ltr">{{ photoStore.photo.preformatted.filesize }}</span>
							</div>
						</div>
					</div>
					<div v-if="photoStore.photo.palette" class="flex gap-2 mb-4 ml-15">
						<ColourSquare :colour="photoStore.photo.palette.colour_1" />
						<ColourSquare :colour="photoStore.photo.palette.colour_2" />
						<ColourSquare :colour="photoStore.photo.palette.colour_3" />
						<ColourSquare :colour="photoStore.photo.palette.colour_4" />
						<ColourSquare :colour="photoStore.photo.palette.colour_5" />
					</div>
					<!-- Dates stuff -->
					<div class="flex-col text-muted-color">
						<div class="flex gap-1 items-center" id="photo-details-created-at">
							<span class="w-6 inline-block">
								<i v-tooltip="$t('gallery.photo.details.uploaded')" class="pi pi-file-arrow-up" />
							</span>
							<span class="text-sm">{{ photoStore.photo.preformatted.created_at }}</span>
						</div>
						<div v-if="photoStore.photo.preformatted.taken_at" class="flex gap-1 items-start" id="photo-details-taken-at">
							<span class="w-6 inline-block">
								<i v-tooltip="$t('gallery.photo.details.captured')" class="pi pi-camera w-6 pt-1 inline-block" />
							</span>
							<span class="text-sm">
								{{ photoStore.photo.preformatted.taken_at }}
								<span v-if="photoStore.photo.precomputed.is_taken_at_modified" class="text-warning-600">*</span>
							</span>
						</div>
					</div>

					<!-- Description stuff -->
					<template v-if="photoStore.photo.preformatted.description">
						<h2 class="text-muted-color-emphasis text-base font-bold mt-4 mb-1">
							{{ $t("gallery.photo.details.description") }}
						</h2>
						<div class="prose dark:prose-invert prose-sm mb-4" v-html="photoStore.photo.preformatted.description"></div>
					</template>

					<!-- Tags stuff -->
					<template v-if="photoStore.photo.tags.length > 0">
						<h2 v-if="photoStore.photo.tags.length > 0" class="text-muted-color-emphasis text-base font-bold mt-4 mb-1">
							{{ $t("gallery.photo.details.tags") }}
						</h2>
						<span class="pb-2 flex flex-wrap">
							<a
								v-for="tag in photoStore.photo.tags"
								:key="`tag-${tag}`"
								class="text-xs rounded-full py-1 px-2.5 mr-1.5 mb-2.5 bg-black/50"
							>
								{{ tag }}
							</a>
						</span>
					</template>

					<!-- Albums stuff -->
					<h2 class="text-muted-color-emphasis text-base font-bold mt-4 mb-1">
						{{ $t("gallery.photo.details.albums") }}
					</h2>
					<div v-if="albums_loading" class="flex items-center gap-2 text-muted-color text-sm">
						<ProgressSpinner style="width: 1.25rem; height: 1.25rem" strokeWidth="4" />
						<span>{{ $t("gallery.photo.details.albums_loading") }}</span>
					</div>
					<div v-else-if="albums_error" class="text-sm text-muted-color">
						<i class="pi pi-exclamation-triangle mr-1" />
						{{ $t("gallery.photo.details.albums_loading_error") }}
					</div>
					<div v-else-if="albums.length === 0" class="text-sm text-muted-color">
						{{ $t("gallery.photo.details.no_albums") }}
					</div>
					<ul v-else class="list-none p-0 m-0">
						<li v-for="album in albums" :key="album.id" class="mb-1">
							<a class="text-sm text-primary-color cursor-pointer hover:underline" @click="navigateToAlbum(album.id)">
								{{ album.title }}
							</a>
						</li>
					</ul>

					<!-- Exif stuff -->
					<template v-if="photoStore.photo.precomputed.has_exif">
						<h2 class="text-muted-color-emphasis text-base font-bold mt-4 mb-1">
							{{ $t("gallery.photo.details.exif_data") }}
						</h2>
						<div class="flex flex-wrap text-muted-color gap-y-0.5">
							<div v-if="photoStore.photo.preformatted.model" class="flex w-full gap-2 items-center">
								<img
									v-tooltip.right="$t('gallery.photo.details.type')"
									src="../../../img/icons/camera.png"
									class="dark:invert opacity-50 w-6 h-6"
								/>
								<span class="text-sm">{{ photoStore.photo.preformatted.model }}</span>
							</div>
							<div v-if="photoStore.photo.preformatted.lens" class="flex w-full gap-2 mb-2">
								<img
									v-tooltip.right="$t('gallery.photo.details.lens')"
									src="../../../img/icons/lens.png"
									class="dark:invert opacity-50 w-6 h-6"
								/>
								<span class="text-sm">{{ photoStore.photo.preformatted.lens }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<MiniIcon v-tooltip.right="$t('gallery.photo.details.aperture')" icon="aperture" class="h-4 w-6" />
								<span>ƒ / {{ photoStore.photo.preformatted.aperture }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<img
									v-tooltip.right="$t('gallery.photo.details.focal')"
									src="../../../img/icons/focal.png"
									class="dark:invert opacity-50 w-6 h-5"
								/>
								<span class="text-sm" dir="ltr">{{ photoStore.photo.preformatted.focal }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<i
									v-tooltip.right="$t('gallery.photo.details.shutter')"
									class="pi pi-stopwatch h-6 w-6 text-base text-center pt-0.5 text-muted-color"
								/>
								<span class="text-sm" dir="ltr">{{ photoStore.photo.preformatted.shutter }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<img src="../../../img/icons/iso.png" class="dark:invert opacity-50 w-6 h-6" />
								<span class="text-sm">{{ photoStore.photo.preformatted.iso }}</span>
							</div>
						</div>
					</template>

					<!-- See later what to do with this, do we want to keep it? -->
					<!-- <span class="py-0.5 px-3 text-sm" v-if="photoStore.photo.type">{{ $t("gallery.photo.details.format") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="photoStore.photo.type">{{ photoStore.photo.type }}</span> -->

					<h2 v-if="photoStore.photo.precomputed.has_location" class="col-span-2 text-muted-color-emphasis text-base font-bold mt-4 mb-1">
						{{ $t("gallery.photo.details.location") }}
					</h2>
					<MapInclude
						v-if="props.isMapVisible"
						:latitude="photoStore.photo.precomputed.latitude"
						:longitude="photoStore.photo.precomputed.longitude"
					/>
					<template v-if="photoStore.photo.precomputed.has_location">
						<div class="flex gap-x-2 text-muted-color">
							<span v-if="photoStore.photo.preformatted.latitude" class="w-full text-sm">{{
								photoStore.photo.preformatted.latitude
							}}</span>
							<span v-if="photoStore.photo.preformatted.longitude" class="w-full text-sm">{{
								photoStore.photo.preformatted.longitude
							}}</span>
							<span v-if="photoStore.photo.preformatted.altitude" class="w-full text-sm">{{
								photoStore.photo.preformatted.altitude
							}}</span>
						</div>
						<div v-if="photoStore.photo.preformatted.location" class="text-sm">
							{{ photoStore.photo.preformatted.location }}
						</div>
					</template>

					<template v-if="photoStore.photo.preformatted.license">
						<h2 class="text-muted-color-emphasis text-base font-bold mt-4 mb-1">
							{{ $t("gallery.photo.details.license") }}
						</h2>
						<span class="py-0.5 pl-0 text-sm text-muted-color">{{ photoStore.photo.preformatted.license }}</span>
					</template>

					<template v-if="photoStore.photo.statistics">
						<h2 class="text-muted-color-emphasis text-base font-bold mt-4 mb-1">
							{{ $t("gallery.photo.details.stats.header") }}
						</h2>
						<div class="flex flex-wrap text-muted-color text-sm gap-y-0.5">
							<div class="w-1/2">
								<i v-tooltip.right="$t('gallery.photo.details.stats.number_of_visits')" class="pi pi-eye mr-2" />
								{{ photoStore.photo.statistics.visit_count }}
							</div>
							<div class="w-1/2">
								<i v-tooltip.right="$t('gallery.photo.details.stats.number_of_downloads')" class="pi pi-cloud-download mr-2" />
								{{ photoStore.photo.statistics.download_count }}
							</div>
							<div class="w-1/2">
								<i v-tooltip.right="$t('gallery.photo.details.stats.number_of_shares')" class="pi pi-share-alt mr-2" />
								{{ photoStore.photo.statistics.shared_count }}
							</div>
							<div class="w-1/2">
								<i v-tooltip.right="$t('gallery.photo.details.stats.number_of_favourites')" class="pi pi-heart mr-2" />
								{{ photoStore.photo.statistics.favourite_count }}
							</div>
						</div>
					</template>

					<!-- Photo Rating Widget -->
					<PhotoRatingWidget
						v-if="photoStore.photo.rating"
						:photo-id="photoStore.photo.id"
						:rating="photoStore.photo.rating"
						:key="`rating-${photoStore.photo.id}`"
					/>

					<LinksInclude v-if="is_details_links_enabled" />
				</div>
			</template>
		</Card>
	</aside>
</template>
<script setup lang="ts">
import { Ref, ref, watch } from "vue";
import Card from "primevue/card";
import ProgressSpinner from "primevue/progressspinner";
import MapInclude from "@/components/gallery/photoModule/MapInclude.vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import ColourSquare from "@/components/gallery/photoModule/ColourSquare.vue";
import PhotoRatingWidget from "@/components/gallery/photoModule/PhotoRatingWidget.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import LinksInclude from "@/components/gallery/photoModule/LinksInclude.vue";
import { storeToRefs } from "pinia";
import { usePhotoStore } from "@/stores/PhotoState";
import { useRouter } from "vue-router";
import PhotoService from "@/services/photo-service";
import { useTogglablesStateStore } from "@/stores/ModalsState";

const photoStore = usePhotoStore();
const router = useRouter();
const togglableStore = useTogglablesStateStore();

const props = defineProps<{
	isMapVisible: boolean;
}>();

const areDetailsOpen = defineModel("areDetailsOpen", { default: true }) as Ref<boolean>;

const lycheeState = useLycheeStateStore();
const { is_details_links_enabled } = storeToRefs(lycheeState);

// Albums section state
const albums = ref<App.Http.Resources.Models.PhotoAlbumResource[]>([]);
const albums_loading = ref(false);
const albums_error = ref(false);
const albums_cached_photo_id = ref<string | null>(null);

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
	[areDetailsOpen, () => photoStore.photo?.id],
	([is_open, photo_id]) => {
		if (is_open && photo_id !== undefined && photo_id !== null) {
			fetchAlbums(photo_id);
		}
	},
	{ immediate: true },
);
</script>
