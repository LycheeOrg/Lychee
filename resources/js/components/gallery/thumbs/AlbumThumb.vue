<template>
	<router-link
		:to="{ name: 'album', params: { albumid: album.id } }"
		class="album-thumb block relative sm:w-[calc(25vw-1rem)] md:w-[calc(19vw-1rem)] lg:w-[calc(16vw-1rem)] xl:w-[calc(14vw-1rem)] 2xl:w-[calc(12vw-0.75rem)] 3xl:w-[calc(12vw-0.75rem)] 4xl:w-52 animate-zoomIn group"
		:class="linkClass"
		:data-id="props.album.id"
	>
		<AlbumThumbImage
			class="group-hover:border-primary-500 group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
			:class="cssClass"
			:thumb="props.album.thumb"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbImage
			class="group-hover:border-primary-500 group-hover:rotate-6 group-hover:translate-x-3 group-hover:-translate-y-2"
			:class="cssClass"
			:thumb="props.album.thumb"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbImage
			class="group-hover:border-primary-500"
			:class="cssClass"
			:thumb="props.album.thumb"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbOverlay v-if="props.config.display_thumb_album_overlay !== 'never'" :album="props.album" :config="props.config" />
		<span v-if="album.thumb?.type.includes('video')" class="w-full h-full absolute hover:opacity-70 transition-opacity duration-300">
			<img class="h-full w-full" alt="play" :src="getPlayIcon()" />
		</span>
		<div v-if="user?.id !== null" class="badges absolute mt-[-1px] ml-1 top-0 left-0">
			<ThumbBadge v-if="props.album.is_nsfw" class="bg-[#ff82ee]" icon="warning" />
			<ThumbBadge v-if="props.album.id === 'starred'" class="bg-yellow-500" icon="star" />
			<ThumbBadge v-if="props.album.id === 'unsorted'" class="bg-red-700" icon="list" />
			<ThumbBadge v-if="props.album.id === 'recent'" class="bg-blue-700" icon="clock" />
			<ThumbBadge v-if="props.album.id === 'on_this_day'" class="bg-green-600" icon="calendar" />
			<ThumbBadge v-if="props.album.is_public" :class="props.album.is_link_required ? 'bg-orange-400' : 'bg-green-600'" icon="eye" />
			<ThumbBadge v-if="props.album.is_password_required && props.album.thumb === null" class="bg-orange-400" icon="lock-locked" />
			<ThumbBadge v-if="props.album.is_password_required && props.album.thumb !== null" class="bg-red-700" icon="lock-unlocked" />
			<ThumbBadge v-if="props.album.is_tag_album" class="bg-green-600" icon="tags" />
			<ThumbBadge v-if="props.cover_id === props.album.thumb?.id" class="bg-yellow-500" icon="folder-cover" />
		</div>
		<!-- v-if="props.config.album_decoration !== 'none'" -->
		<AlbumThumbDecorations :album="props.album" :config="props.config" />
	</router-link>
</template>
<script setup lang="ts">
import { computed } from "vue";
import ThumbBadge from "@/components/gallery/thumbs/ThumbBadge.vue";
import AlbumThumbImage from "@/components/gallery/thumbs/AlbumThumbImage.vue";
import { useAuthStore } from "@/stores/Auth";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumThumbOverlay from "./AlbumThumbOverlay.vue";
import AlbumThumbDecorations from "./AlbumThumbDecorations.vue";
import { storeToRefs } from "pinia";
import { useImageHelpers } from "@/utils/Helpers";

export type AlbumThumbConfig = {
	album_thumb_css_aspect_ratio: string;
	album_subtitle_type: App.Enum.ThumbAlbumSubtitleType;
	display_thumb_album_overlay: App.Enum.ThumbOverlayVisibilityType;
	album_decoration: App.Enum.AlbumDecorationType;
	album_decoration_orientation: App.Enum.AlbumDecorationOrientation;
};

const props = defineProps<{
	isSelected: boolean;
	cover_id: string | null;
	album: App.Http.Resources.Models.ThumbAlbumResource;
	config: AlbumThumbConfig;
}>();

const auth = useAuthStore();
const lycheeStore = useLycheeStateStore();
const { getPlayIcon } = useImageHelpers();
const { user } = storeToRefs(auth);

const cssClass = computed(() => {
	let css = "";
	if (props.isSelected) {
		css += "outline outline-1.5 outline-primary-500";
	}
	return css;
});

const linkClass = computed(
	() =>
		(lycheeStore.number_albums_per_row_mobile === 1 ? "w-[calc(100%)] " : "") +
		(lycheeStore.number_albums_per_row_mobile === 2 ? "w-[calc(50%-0.25rem)] " : "") +
		(lycheeStore.number_albums_per_row_mobile === 3 ? "w-[calc(33%-0.25rem)] " : "") +
		props.config.album_thumb_css_aspect_ratio +
		(lycheeStore.is_nsfw_background_blurred && props.album.is_nsfw ? " blurred" : ""),
);
auth.getUser();
</script>
