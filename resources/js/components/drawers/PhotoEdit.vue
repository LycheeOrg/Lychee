<template>
	<Drawer :closeOnEsc="false" v-model:visible="isEditOpen" position="right" pt:root:class="w-full p-card border-transparent">
		<Card id="lychee_sidebar" v-if="props.photo" class="h-full pr-4 break-words max-w-4xl mx-auto">
			<template #content>
				<div class="grid grid-cols-[auto minmax(0, 1fr)] mt-16">
					<h1 class="col-span-2 text-center text-lg font-bold my-4">
						{{ $t("lychee.ALBUM_ABOUT") }}
					</h1>
					<h2 class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("lychee.PHOTO_BASICS") }}
					</h2>
					<span class="py-0.5 px-3 text-sm">{{ $t("lychee.PHOTO_TITLE") }}</span>
					<span class="py-0.5 pl-0 text-sm">{{ props.photo.title }}</span>

					<span class="py-0.5 px-3 text-sm">{{ $t("lychee.PHOTO_UPLOADED") }}</span>
					<span class="py-0.5 pl-0 text-sm">{{ props.photo.preformatted.created_at }}</span>

					<span v-if="props.photo.preformatted.description" class="col-span-2 py-0.5 pl-3 text-sm">{{
						$t("lychee.PHOTO_DESCRIPTION")
					}}</span>
					<div
						v-if="props.photo.preformatted.description"
						class="pb-0.5 pt-4 pl-8 col-span-2 prose prose-invert prose-sm"
						v-html="props.photo.preformatted.description"
					></div>

					<h2 v-if="props.photo.precomputed.is_video" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("lychee.PHOTO_VIDEO") }}
					</h2>
					<h2 v-if="!props.photo.precomputed.is_video" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("lychee.PHOTO_IMAGE") }}
					</h2>
					<span class="py-0.5 px-3 text-sm">{{ $t("lychee.PHOTO_SIZE") }}</span>
					<span class="py-0.5 pl-0 text-sm">{{ props.photo.preformatted.filesize }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.type">{{ $t("lychee.PHOTO_FORMAT") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.type">{{ props.photo.type }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.resolution">{{ $t("lychee.PHOTO_RESOLUTION") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.resolution">{{ props.photo.preformatted.resolution }}</span>

					<span class="py-0.5 px-3 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.duration">{{
						$t("lychee.PHOTO_DURATION")
					}}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.duration">{{
						props.photo.preformatted.duration
					}}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.fps">{{
						$t("lychee.PHOTO_FPS")
					}}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.precomputed.is_video && props.photo.preformatted.fps">{{
						props.photo.preformatted.fps
					}}</span>
					<h2 v-if="props.photo.tags.length > 0" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("lychee.PHOTO_TAGS") }}
					</h2>
					<p v-if="props.photo.tags.length > 0" class="py-0.5 pl-3 col-span-2 text-sm">
						<a v-for="tag in props.photo.tags" class="text-xs cursor-pointer rounded-full py-1.5 px-2.5 mr-1.5 mb-2.5 bg-black/50">{{
							tag
						}}</a>
					</p>
					<h2 v-if="props.photo.precomputed.has_exif" class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
						{{ $t("lychee.PHOTO_CAMERA") }}
					</h2>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.taken_at">{{ $t("lychee.PHOTO_CAPTURED") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.taken_at">{{ props.photo.preformatted.taken_at }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.make">{{ $t("lychee.PHOTO_MAKE") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.make">{{ props.photo.make }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.model">{{ $t("lychee.PHOTO_TYPE") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.model">{{ props.photo.model }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.lens">{{ $t("lychee.PHOTO_LENS") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.lens">{{ props.photo.lens }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.shutter">{{ $t("lychee.PHOTO_SHUTTER") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.shutter">{{ props.photo.preformatted.shutter }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.aperture">{{ $t("lychee.PHOTO_APERTURE") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.aperture">ƒ / {{ props.photo.preformatted.aperture }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.focal">{{ $t("lychee.PHOTO_FOCAL") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.focal">{{ props.photo.focal }}</span>
					<span class="py-0.5 px-3 text-sm" v-if="props.photo.iso">{{ sprintf($t("lychee.PHOTO_ISO"), "") }}</span>
					<span class="py-0.5 pl-0 text-sm" v-if="props.photo.iso">{{ props.photo.preformatted.iso }}</span>

					<template v-if="props.photo.precomputed.has_location">
						<h2 class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">
							{{ $t("lychee.PHOTO_LOCATION") }}
						</h2>
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.latitude">{{ $t("lychee.PHOTO_LATITUDE") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.latitude">{{ props.photo.preformatted.latitude }}</span>
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.longitude">{{ $t("lychee.PHOTO_LONGITUDE") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.longitude">{{ props.photo.preformatted.longitude }}</span>
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.preformatted.altitude">{{ $t("lychee.PHOTO_ALTITUDE") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.preformatted.altitude">{{ props.photo.preformatted.altitude }}</span>
						<span class="py-0.5 px-3 text-sm" v-if="props.photo.location">{{ $t("lychee.PHOTO_LOCATION") }}</span>
						<span class="py-0.5 pl-0 text-sm" v-if="props.photo.location">{{ props.photo.location }}</span>
					</template>
					<template v-if="props.photo.preformatted.license">
						<h2 class="col-span-2 text-muted-color font-bold px-3 pt-4 pb-3">{{ $t("lychee.PHOTO_REUSE") }}</h2>
						<span class="py-0.5 px-3 text-sm">{{ $t("lychee.PHOTO_LICENSE") }}</span>
						<span class="py-0.5 pl-0 text-sm">{{ props.photo.preformatted.license }}</span>
					</template>
				</div>
			</template>
		</Card>
	</Drawer>
</template>
<script setup lang="ts">
import Card from "primevue/card";
import Drawer from "primevue/drawer";
import { sprintf } from "sprintf-js";
import { ref, Ref } from "vue";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const isEditOpen = defineModel("isEditOpen", { default: false }) as Ref<boolean>;
</script>
