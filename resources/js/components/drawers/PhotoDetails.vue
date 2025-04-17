<template>
	<aside
		id="lychee_sidebar_container"
		:class="{
			'h-full relative transition-all overflow-x-clip overflow-y-scroll bg-bg-800': true,
			'w-[380px]': areDetailsOpen,
			'w-0 translate-x-full': !areDetailsOpen,
		}"
	>
		<Card id="lychee_sidebar" v-if="props.photo" class="w-[380px] h-full pr-4 break-words">
			<template #content>
				<div class="flex flex-col mt-8">
					<h1 class="text-center text-2xl font-bold my-4">
						{{ $t("gallery.photo.details.about") }}
					</h1>
					<!-- Title etc info -->
					<div class="flex gap-3 mb-4">
						<div>
							<MiniIcon icon="image" class="h-12 w-12" />
						</div>
						<div class="flex flex-col">
							<span class="font-bold text-lg">{{ props.photo.title }}</span>
							<div class="flex gap-3 text-muted-color text-sm">
								<span v-if="props.photo.preformatted.resolution">{{ props.photo.preformatted.resolution }}</span>
								<span v-if="props.photo.precomputed.is_video && props.photo.preformatted.duration">{{
									props.photo.preformatted.duration
								}}</span>
								<span v-if="props.photo.precomputed.is_video && props.photo.preformatted.fps">{{
									props.photo.preformatted.fps
								}}</span>
								<span>{{ props.photo.preformatted.filesize }}</span>
							</div>
						</div>
					</div>
					<!-- Dates stuff -->
					<div class="flex-col text-muted-color">
						<div class="flex gap-1 items-center">
							<span class="w-6 inline-block text-left"
								><i class="pi pi-file-arrow-up" v-tooltip="$t('gallery.photo.details.uploaded')"
							/></span>
							<span class="text-sm">{{ props.photo.preformatted.created_at }}</span>
						</div>
						<div class="flex gap-1 items-start">
							<span class="w-6 inline-block text-left"
								><i class="pi pi-camera w-6 pt-1 inline-block" v-tooltip="$t('gallery.photo.details.captured')"
							/></span>
							<span v-if="props.photo.preformatted.taken_at" class="text-sm"
								>{{ props.photo.preformatted.taken_at }}
								<span v-if="props.photo.precomputed.is_taken_at_modified" class="text-warning-600">*</span>
							</span>
						</div>
					</div>

					<!-- Description stuff -->
					<template v-if="props.photo.preformatted.description">
						<h2 class="text-muted-color-emphasis text-base font-bold pt-4 pb-1">
							{{ $t("gallery.photo.details.description") }}
						</h2>
						<div class="prose dark:prose-invert prose-sm mb-4" v-html="props.photo.preformatted.description"></div>
					</template>

					<!-- Tags stuff -->
					<template v-if="props.photo.tags.length > 0">
						<h2 v-if="props.photo.tags.length > 0" class="text-muted-color-emphasis text-base font-bold pt-4 pb-1">
							{{ $t("gallery.photo.details.tags") }}
						</h2>
						<span class="pb-2">
							<a v-for="tag in props.photo.tags" class="text-xs rounded-full py-1 px-2.5 mr-1.5 mb-2.5 bg-black/50">
								{{ tag }}
							</a>
						</span>
					</template>

					<!-- Exif stuff -->
					<template v-if="props.photo.precomputed.has_exif">
						<h2 class="text-muted-color-emphasis text-base font-bold pt-4 pb-1">
							{{ $t("gallery.photo.details.exif_data") }}
						</h2>
						<div class="flex flex-wrap text-muted-color gap-y-0.5">
							<div class="flex w-full gap-2 items-center" v-if="props.photo.model">
								<img
									src="../../../img/icons/camera.png"
									class="dark:invert opacity-50 w-6 h-6"
									v-tooltip.right="$t('gallery.photo.details.type')"
								/>
								<span class="text-sm">{{ props.photo.model }}</span>
							</div>
							<div class="flex w-full gap-2 mb-2" v-if="props.photo.lens">
								<img
									src="../../../img/icons/lens.png"
									class="dark:invert opacity-50 w-6 h-6"
									v-tooltip.right="$t('gallery.photo.details.lens')"
								/>
								<span class="text-sm">{{ props.photo.lens }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<MiniIcon icon="aperture" class="h-4 w-6" v-tooltip.right="$t('gallery.photo.details.aperture')" />
								<span>ƒ / {{ props.photo.preformatted.aperture }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<img
									src="../../../img/icons/focal.png"
									class="dark:invert opacity-50 w-6 h-5"
									v-tooltip.right="$t('gallery.photo.details.focal')"
								/>
								<span class="text-sm">{{ props.photo.focal }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<i
									class="pi pi-stopwatch h-6 w-6 text-base text-center pt-0.5 text-muted-color"
									v-tooltip.right="$t('gallery.photo.details.shutter')"
								/>
								<span class="text-sm">{{ props.photo.preformatted.shutter }}</span>
							</div>
							<div class="flex w-1/2 gap-2 items-center">
								<img src="../../../img/icons/iso.png" class="dark:invert opacity-50 w-6 h-6" />
								<span class="text-sm">{{ props.photo.iso }}</span>
							</div>
						</div>
					</template>

					<!-- See later what to do with this, do we want to keep it? -->
					<!-- <span class="py-0.5 px-3 text-sm" v-if="props.photo.type">{{ $t("gallery.photo.details.format") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.type">{{ props.photo.type }}</span> -->

					<h2 v-if="props.photo.precomputed.has_location" class="col-span-2 text-muted-color-emphasis text-base font-bold pt-4 pb-1">
						{{ $t("gallery.photo.details.location") }}
					</h2>
					<MapInclude :latitude="props.photo.latitude" :longitude="props.photo.longitude" v-if="props.isMapVisible" />
					<template v-if="props.photo.precomputed.has_location">
						<div class="flex gap-x-2 text-muted-color">
							<span class="w-full text-sm" v-if="props.photo.preformatted.latitude">{{ props.photo.preformatted.latitude }}</span>
							<span class="w-full text-sm" v-if="props.photo.preformatted.longitude">{{ props.photo.preformatted.longitude }}</span>
							<span class="w-full text-sm" v-if="props.photo.preformatted.altitude">{{ props.photo.preformatted.altitude }}</span>
						</div>
						<div class="text-sm" v-if="props.photo.location">
							{{ props.photo.location }}
						</div>
					</template>

					<template v-if="props.photo.preformatted.license">
						<h2 class="text-muted-color-emphasis text-base font-bold pt-4 pb-1">
							{{ $t("gallery.photo.details.license") }}
						</h2>
						<span class="py-0.5 pl-0 text-sm text-muted-color">{{ props.photo.preformatted.license }}</span>
					</template>

					<template v-if="props.photo.statistics">
						<h2 class="text-muted-color-emphasis text-base font-bold pt-4 pb-1">
							{{ $t("gallery.photo.details.stats.header") }}
						</h2>
						<div class="flex flex-wrap text-muted-color text-sm gap-y-0.5">
							<div class="w-1/2">
								<i class="pi pi-eye mr-2" v-tooltip.right="$t('gallery.photo.details.stats.number_of_visits')" />
								{{ props.photo.statistics.visit_count }}
							</div>
							<div class="w-1/2">
								<i class="pi pi-cloud-download mr-2" v-tooltip.right="$t('gallery.photo.details.stats.number_of_downloads')" />
								{{ props.photo.statistics.download_count }}
							</div>
							<div class="w-1/2">
								<i class="pi pi-share-alt mr-2" v-tooltip.right="$t('gallery.photo.details.stats.number_of_shares')" />
								{{ props.photo.statistics.shared_count }}
							</div>
							<div class="w-1/2">
								<i class="pi pi-heart mr-2" v-tooltip.right="$t('gallery.photo.details.stats.number_of_favourites')" />
								{{ props.photo.statistics.favourite_count }}
							</div>
						</div>
					</template>
				</div>
			</template>
		</Card>
	</aside>
</template>
<script setup lang="ts">
import { Ref } from "vue";
import Card from "primevue/card";
import MapInclude from "../gallery/photoModule/MapInclude.vue";
import MiniIcon from "../icons/MiniIcon.vue";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource | undefined;
	isMapVisible: boolean;
}>();

const areDetailsOpen = defineModel("areDetailsOpen", { default: true }) as Ref<boolean>;
</script>
