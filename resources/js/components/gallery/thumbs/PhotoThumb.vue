<template>
	<router-link
		:to="{ name: 'photo', params: { albumid: props.album?.id ?? 'search', photoid: props.photo.id } }"
		:class="cssClass"
		:data-width="props.photo.size_variants.original?.width"
		:data-height="props.photo.size_variants.original?.height"
		:data-id="props.photo.id"
		:data-album-id="props.album?.id"
	>
		<span
			class="thumbimg w-full h-full border-none"
			:class="(props.photo.precomputed.is_video ? 'video' : '') + ' ' + (props.photo.precomputed.is_livephoto ? 'livephoto' : '')"
		>
			<img
				alt="Photo thumbnail"
				class="h-full w-full border-none object-cover object-center"
				:src="props.photo.size_variants.small?.url ?? props.photo.size_variants.thumb?.url ?? srcNoImage"
				:srcset="
					props.photo.size_variants.small2x ? props.photo.size_variants.small + ' 1x, ' + props.photo.size_variants.small2x + ' 2x' : ''
				"
				data-overlay="false"
				draggable="false"
			/>
		</span>
		<div class="overlay w-full absolute bottom-0 m-0 bg-gradient-to-t from-[#00000066] text-shadow-sm" :class="cssOverlay">
			<h1 class="min-h-[19px] mt-3 mb-1 ml-3 text-surface-0 text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">
				{{ props.photo.title }}
			</h1>
			<span v-if="props.photo.preformatted.taken_at" class="block mt-0 mr-0 mb-2 ml-3 text-2xs text-surface-300">
				<span title="Camera Date"><MiniIcon icon="camera-slr" class="w-2 h-2 m-0 mr-1 fill-neutral-300" /></span
				>{{ props.photo.preformatted.taken_at }}
			</span>
			<span v-else class="block mt-0 mr-0 mb-2 ml-3 text-2xs text-surface-300">{{ props.photo.preformatted.created_at }}</span>
		</div>
		<div
			v-if="props.photo.precomputed.is_video"
			class="w-full top-0 h-full absolute hover:opacity-70 transition-opacity duration-300 flex justify-center items-center"
		>
			<img class="absolute aspect-square w-fit h-fit" alt="play" :src="srcPlay" />
		</div>
		<!-- TODO: make me an option. -->
		<div v-if="user?.id" class="badges absolute mt-[-1px] ml-1 top-0 left-0">
			<ThumbBadge v-if="props.photo.is_starred" class="bg-yellow-500" icon="star" />
			<ThumbBadge v-if="is_cover_id" class="bg-yellow-500" icon="folder-cover" />
			<ThumbBadge v-if="is_header_id" class="bg-slate-400" pi="image" />
		</div>
	</router-link>
</template>
<script setup lang="ts">
import { computed, ref, type Ref } from "vue";
import { useAuthStore } from "@/stores/Auth";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import ThumbBadge from "@/components/gallery/thumbs/ThumbBadge.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const props = defineProps<{
	isSelected: boolean;
	album:
		| App.Http.Resources.Models.AlbumResource
		| App.Http.Resources.Models.TagAlbumResource
		| App.Http.Resources.Models.SmartAlbumResource
		| undefined;
	photo: App.Http.Resources.Models.PhotoResource;
}>();

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
const srcPlay = ref((window.assets_url ?? "") + "img/play-icon.png");
const srcNoImage = ref((window.assets_url ?? "") + "img/no_images.svg");

// @ts-expect-error
const is_cover_id = computed(() => props.album?.cover_id === props.photo.id);
// @ts-expect-error
const is_header_id = computed(() => props.album?.header_id === props.photo.id);

const { user } = storeToRefs(auth);
auth.getUser();

const cssClass = computed(() => {
	let css = "photo group shadow-md shadow-black/25 animate-zoomIn transition-all ease-in duration-200 block absolute";
	if (props.isSelected) {
		css += " outline outline-1.5 outline-primary-500";
	}
	return css;
});

const cssOverlay = computed(() => {
	if (lycheeStore.display_thumb_photo_overlay === "never") {
		return "hidden";
	}
	if (lycheeStore.display_thumb_photo_overlay === "hover") {
		return "opacity-0 group-hover:opacity-100 transition-all ease-out";
	}
	return "";
});
</script>
