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
				<div class="grid grid-cols-[auto minmax(0, 1fr)] mt-8">
					<h1 class="col-span-2 text-center text-lg font-bold my-4">
						{{ $t("gallery.photo.details.about") }}
					</h1>
					<h2 class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("gallery.photo.details.basics") }}
					</h2>
					<span class="py-0.5 px-3 text-sm">{{ $t("gallery.photo.details.title") }}</span>
					<span class="py-0.5 pl-0 text-sm">{{ props.photo.title }}</span>

					<span class="py-0.5 px-3 text-sm">{{ $t("gallery.photo.details.uploaded") }}</span>
					<span class="py-0.5 pl-0 text-sm">{{ props.photo.preformatted.created_at }}</span>

					<span v-if="props.photo.preformatted.description" class="col-span-2 py-0.5 pl-3 text-sm">
						{{ $t("gallery.photo.details.description") }}
					</span>
					<div
						v-if="props.photo.preformatted.description"
						class="pb-0.5 pt-4 pl-8 col-span-2 prose dark:prose-invert prose-sm"
						v-html="props.photo.preformatted.description"
					></div>

					<h2 v-if="props.photo.precomputed.is_video" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("gallery.photo.details.video") }}
					</h2>
					<h2 v-if="!props.photo.precomputed.is_video" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("gallery.photo.details.image") }}
					</h2>
					<span class="py-0.5 px-3 text-sm">{{ $t("gallery.photo.details.size") }}</span>
					<span class="py-0.5 pl-0 text-sm">{{ props.photo.preformatted.filesize }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.type">{{ $t("gallery.photo.details.format") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.type">{{ props.photo.type }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.resolution">{{ $t("gallery.photo.details.resolution") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.resolution">{{ props.photo.preformatted.resolution }}</span>

					<span class="py-0.5 px-3 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.duration">{{
						$t("gallery.photo.details.duration")
					}}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.duration">{{
						props.photo.preformatted.duration
					}}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.fps">{{
						$t("gallery.photo.details.fps")
					}}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.fps">{{
						props.photo.preformatted.fps
					}}</span>
					<h2 v-if="props.photo.tags.length > 0" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("gallery.photo.details.tags") }}
					</h2>
					<p v-if="props.photo.tags.length > 0" class="py-0.5 pl-3 col-span-2 text-sm">
						<a v-for="tag in props.photo.tags" class="text-xs cursor-pointer rounded-full py-1.5 px-2.5 mr-1.5 mb-2.5 bg-black/50">{{
							tag
						}}</a>
					</p>
					<h2 v-if="props.photo.precomputed.has_exif" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("gallery.photo.details.camera") }}
					</h2>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.taken_at"
						>{{ $t("gallery.photo.details.captured")
						}}<span v-if="props.photo.precomputed.is_taken_at_modified" class="ml-1 text-warning-600">*</span></span
					>
					<span class="py-0.5 pl-0 text-sm">{{ props.photo.preformatted.taken_at }}</span>

					<span class="py-0.5 px-3 text-sm" v-if="props.photo.make">{{ $t("gallery.photo.details.make") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.make">{{ props.photo.make }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.model">{{ $t("gallery.photo.details.type") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.model">{{ props.photo.model }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.lens">{{ $t("gallery.photo.details.lens") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.lens">{{ props.photo.lens }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.shutter">{{ $t("gallery.photo.details.shutter") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.shutter">{{ props.photo.preformatted.shutter }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.aperture">{{ $t("gallery.photo.details.aperture") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.aperture">ƒ / {{ props.photo.preformatted.aperture }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.focal">{{ $t("gallery.photo.details.focal") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.focal">{{ props.photo.focal }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.iso">{{ sprintf($t("gallery.photo.details.iso"), "") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.iso">{{ props.photo.preformatted.iso }}</span>

					<h2 v-if="props.photo.precomputed.has_location" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("gallery.photo.details.location") }}
					</h2>
					<MapInclude :latitude="props.photo.latitude" :longitude="props.photo.longitude" v-if="props.isMapVisible" />
					<template v-if="props.photo.precomputed.has_location">
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.latitude">{{ $t("gallery.photo.details.latitude") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.latitude">{{ props.photo.preformatted.latitude }}</span>
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.longitude">{{ $t("gallery.photo.details.longitude") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.longitude">{{ props.photo.preformatted.longitude }}</span>
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.altitude">{{ $t("gallery.photo.details.altitude") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.altitude">{{ props.photo.preformatted.altitude }}</span>
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.location">{{ $t("gallery.photo.details.location") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.location">{{ props.photo.location }}</span>
					</template>
					<template v-if="props.photo.preformatted.license">
						<h2 class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">{{ $t("gallery.photo.details.reuse") }}</h2>
						<span class="py-0.5 px-3 text-sm">{{ $t("gallery.photo.details.license") }}</span>
						<span class="py-0.5 pl-0 text-sm">{{ props.photo.preformatted.license }}</span>
					</template>
				</div>
			</template>
		</Card>
	</aside>
</template>
<script setup lang="ts">
import { Ref } from "vue";
import { sprintf } from "sprintf-js";
import Card from "primevue/card";
import MapInclude from "../gallery/photo/MapInclude.vue";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource | undefined;
	isMapVisible: boolean;
}>();

const areDetailsOpen = defineModel("areDetailsOpen", { default: true }) as Ref<boolean>;
</script>
