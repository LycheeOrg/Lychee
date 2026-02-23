<template>
	<router-link
		:to="{ name: albumRoutes().album, params: { albumId: album.id } }"
		class="album-thumb block relative sm:w-[calc(25vw-1rem)] md:w-[calc(19vw-1rem)] lg:w-[calc(16vw-1rem)] xl:w-[calc(14vw-1rem)] 2xl:w-[calc(12vw-0.75rem)] 3xl:w-[calc(12vw-0.75rem)] 4xl:w-52 animate-zoomIn group"
		:class="{
			'w-[calc(100%)]': lycheeStore.number_albums_per_row_mobile === 1,
			'w-[calc(50%-0.25rem)]': lycheeStore.number_albums_per_row_mobile === 2,
			'w-[calc(33%-0.25rem)]': lycheeStore.number_albums_per_row_mobile === 3,
			blurred: lycheeStore.is_nsfw_background_blurred && props.album.is_nsfw,
			'aspect-4x5': 'aspect-4x5' === aspectRatio,
			'aspect-5x4': 'aspect-5x4' === aspectRatio,
			'aspect-2x3': 'aspect-2x3' === aspectRatio,
			'aspect-3x2': 'aspect-3x2' === aspectRatio,
			'aspect-square': 'aspect-square' === aspectRatio,
			'aspect-video': 'aspect-video' === aspectRatio,
			'opacity-25! ': cannotInteractWhileDragging,
		}"
		:data-album-id="props.album.id"
	>
		<!-- the v-if="!togglableStore.isDragging" is a work around to avoid weird behaviour in RTL mode. -->
		<AlbumThumbImage
			v-if="!togglableStore.isDragging"
			class="group-hover:border-primary-500 top-0 left-0 group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
			:thumb="props.album.thumb"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbImage
			v-if="!togglableStore.isDragging"
			class="group-hover:border-primary-500 top-0 left-0 group-hover:rotate-6 group-hover:translate-x-3 group-hover:-translate-y-2"
			:thumb="props.album.thumb"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbImage
			class="group-hover:border-primary-500 top-0 left-0"
			:thumb="props.album.thumb"
			:class="cssClass"
			:is-selectable="isSelectable"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbOverlay v-if="display_thumb_album_overlay !== 'never'" :album="props.album" />
		<span v-if="props.album.thumb?.type.includes('video')" class="w-full h-full absolute hover:opacity-70 transition-opacity duration-300">
			<img class="h-full w-full" alt="play" :src="getPlayIcon()" />
		</span>
		<div v-if="userStore.isLoggedIn" class="badges absolute -mt-px ml-1 top-0 left-0 flex">
			<ThumbBadge v-if="props.album.is_nsfw" class="bg-[#ff82ee]" icon="warning" />
			<ThumbBadge v-if="props.album.id === 'highlighted'" class="bg-yellow-500" pi="flag-fill" />
			<ThumbBadge v-if="props.album.id === 'unsorted'" class="bg-red-700" icon="list" />
			<ThumbBadge v-if="props.album.id === 'recent'" class="bg-blue-700" icon="clock" />
			<ThumbBadge v-if="props.album.id === 'on_this_day'" class="bg-green-600" icon="calendar" />
			<ThumbBadge v-if="props.album.id === 'untagged'" class="bg-gray-500" icon="tags" />
			<ThumbBadge v-if="props.album.id === 'one_star'" class="bg-yellow-500" icon="star-1" />
			<ThumbBadge v-if="props.album.id === 'two_stars'" class="bg-yellow-500" icon="star-2" />
			<ThumbBadge v-if="props.album.id === 'three_stars'" class="bg-yellow-500" icon="star-3" />
			<ThumbBadge v-if="props.album.id === 'four_stars'" class="bg-yellow-500" icon="star-4" />
			<ThumbBadge v-if="props.album.id === 'five_stars'" class="bg-yellow-500" icon="star-5" />
			<ThumbBadge v-if="props.album.id === 'best_pictures'" class="bg-cyan-500" pi="trophy" />
			<ThumbBadge v-if="props.album.id === 'my_rated_pictures'" class="bg-orange-500" pi="trophy" />
			<ThumbBadge v-if="props.album.id === 'my_best_pictures'" class="bg-yellow-500" pi="trophy" />
			<ThumbBadge v-if="props.album.is_public" :class="props.album.is_link_required ? 'bg-orange-400' : 'bg-green-600'" icon="eye" />
			<ThumbBadge v-if="props.album.is_password_required && props.album.thumb === null" class="bg-orange-400" icon="lock-locked" />
			<ThumbBadge v-if="props.album.is_password_required && props.album.thumb !== null" class="bg-red-700" icon="lock-unlocked" />
			<ThumbBadge v-if="props.album.is_tag_album" class="bg-green-600" icon="tags" />
			<ThumbBadge v-if="props.cover_id === props.album.thumb?.id" class="bg-yellow-500" icon="folder-cover" />
		</div>
		<!-- v-if="props.config.album_decoration !== 'none'" -->
		<AlbumThumbDecorations :album="props.album" />
	</router-link>
</template>
<script setup lang="ts">
import { computed } from "vue";
import ThumbBadge from "@/components/gallery/albumModule/thumbs/ThumbBadge.vue";
import AlbumThumbImage from "@/components/gallery/albumModule/thumbs/AlbumThumbImage.vue";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumThumbOverlay from "./AlbumThumbOverlay.vue";
import AlbumThumbDecorations from "./AlbumThumbDecorations.vue";
import { storeToRefs } from "pinia";
import { useImageHelpers } from "@/utils/Helpers";
import { useAlbumRoute } from "@/composables/photo/albumRoute";
import { useRouter } from "vue-router";
import { useAlbumActions } from "@/composables/album/albumActions";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";

export type AlbumThumbConfig = {
	album_thumb_css_aspect_ratio: string;
	album_subtitle_type: App.Enum.ThumbAlbumSubtitleType;
	display_thumb_album_overlay: App.Enum.VisibilityType;
	album_decoration: App.Enum.AlbumDecorationType;
	album_decoration_orientation: App.Enum.AlbumDecorationOrientation;
};

const props = defineProps<{
	isSelected: boolean;
	cover_id: string | null;
	album: App.Http.Resources.Models.ThumbAlbumResource;
}>();

const { canInteractAlbum } = useAlbumActions();
const router = useRouter();
const userStore = useUserStore();
const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();

const lycheeStore = useLycheeStateStore();

const togglableStore = useTogglablesStateStore();
const { getPlayIcon } = useImageHelpers();
const { display_thumb_album_overlay } = storeToRefs(lycheeStore);

const aspectRatio = computed(
	() => albumStore.config?.album_thumb_css_aspect_ratio ?? albumsStore.rootConfig?.album_thumb_css_aspect_ratio ?? "aspect-square",
);

const { albumRoutes } = useAlbumRoute(router);
const cannotInteractWhileDragging = computed(() => togglableStore.isDragging === true && canInteractAlbum(props.album) === false);
const isSelectable = computed(() => canInteractAlbum(props.album));

const cssClass = computed(() => {
	if (props.isSelected) {
		return "outline !outline-offset-2 outline-primary-500";
	}
	return "";
});
</script>
