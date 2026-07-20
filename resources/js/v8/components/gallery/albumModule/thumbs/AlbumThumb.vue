<template>
	<router-link
		:to="{ name: albumRoutes().album, params: { albumId: album.id } }"
		class="album-thumb block relative sm:w-[calc(25vw-1rem)] md:w-[calc(19vw-1rem)] lg:w-[calc(16vw-1rem)] xl:w-[calc(14vw-1rem)] 2xl:w-[calc(12vw-0.75rem)] 3xl:w-[calc(12vw-0.75rem)] 4xl:w-52 animate-zoomIn group"
		:class="{
			'w-[calc(100%)]': number_albums_per_row_mobile === 1,
			'w-[calc(50%-0.25rem)]': number_albums_per_row_mobile === 2,
			'w-[calc(33%-0.25rem)]': number_albums_per_row_mobile === 3,
			blurred: is_nsfw_background_blurred && props.album.is_nsfw,
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
			class="group-hover:border-primary top-0 left-0 group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
			:thumb="props.album.thumb"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbImage
			v-if="!togglableStore.isDragging"
			class="group-hover:border-primary top-0 left-0 group-hover:rotate-6 group-hover:translate-x-3 group-hover:-translate-y-2"
			:thumb="props.album.thumb"
			:is-password-protected="props.album.is_password_required"
		/>
		<AlbumThumbImage
			class="group-hover:border-primary top-0 left-0"
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
			<template v-if="isSmartAlbum && is_smart_album_flags_enabled">
				<ThumbBadge v-if="props.album.id === 'highlighted'" :class="ALBUM_BADGE_BG.favorite" :pi="`lucide:flag ${FILL_OVERRIDE_CLASS}`" />
				<ThumbBadge v-if="props.album.id === 'unsorted'" :class="ALBUM_BADGE_BG.danger" icon="list" />
				<ThumbBadge v-if="props.album.id === 'recent'" :class="ALBUM_BADGE_BG.info" icon="clock" />
				<ThumbBadge v-if="props.album.id === 'on_this_day'" :class="ALBUM_BADGE_BG.success" icon="calendar" />
				<ThumbBadge v-if="props.album.id === 'untagged'" :class="ALBUM_BADGE_BG.neutral" icon="tags" />
				<ThumbBadge v-if="props.album.id === 'one_star'" :class="ALBUM_BADGE_BG.favorite" icon="star-1" />
				<ThumbBadge v-if="props.album.id === 'two_stars'" :class="ALBUM_BADGE_BG.favorite" icon="star-2" />
				<ThumbBadge v-if="props.album.id === 'three_stars'" :class="ALBUM_BADGE_BG.favorite" icon="star-3" />
				<ThumbBadge v-if="props.album.id === 'four_stars'" :class="ALBUM_BADGE_BG.favorite" icon="star-4" />
				<ThumbBadge v-if="props.album.id === 'five_stars'" :class="ALBUM_BADGE_BG.favorite" icon="star-5" />
				<ThumbBadge v-if="props.album.id === 'best_pictures'" :class="ALBUM_BADGE_BG.trophy" pi="lucide:trophy" />
				<ThumbBadge v-if="props.album.id === 'my_rated_pictures'" :class="ALBUM_BADGE_BG.rated" pi="lucide:trophy" />
				<ThumbBadge v-if="props.album.id === 'my_best_pictures'" :class="ALBUM_BADGE_BG.favorite" pi="lucide:trophy" />
			</template>
			<ThumbBadge v-if="showSensitiveFlag" :class="ALBUM_BADGE_BG.nsfw" icon="warning" />
			<ThumbBadge v-if="showPublicHiddenFlag" :class="ALBUM_BADGE_BG.link" icon="eye" />
			<ThumbBadge v-if="showPublicVisibleFlag" :class="ALBUM_BADGE_BG.success" icon="eye" />
			<ThumbBadge v-if="showPasswordFlag && props.album.thumb === null" :class="ALBUM_BADGE_BG.link" icon="lock-locked" />
			<ThumbBadge v-if="showPasswordFlag && props.album.thumb !== null" :class="ALBUM_BADGE_BG.danger" icon="lock-unlocked" />
			<ThumbBadge v-if="scopeFlagsEnabled && props.album.is_tag_album" :class="ALBUM_BADGE_BG.success" icon="tags" />
			<ThumbBadge v-if="scopeFlagsEnabled && props.album.is_person_album" :class="ALBUM_BADGE_BG.person" pi="lucide:users" />
			<ThumbBadge
				v-if="is_cover_id_flag_enabled && props.cover_id === props.album.thumb?.id"
				:class="ALBUM_BADGE_BG.favorite"
				icon="folder-cover"
			/>
		</div>
		<AlbumThumbDecorations :album="props.album" />
		<!-- Touch select overlay: stops the click from reaching the router-link navigate handler -->
		<div v-if="is_touch_select_mode" class="absolute inset-0 z-20" @click.stop="(e: MouseEvent) => emits('touchSelect', e)" />
		<!-- Touch select mode indicator -->
		<div
			v-if="is_touch_select_mode"
			class="absolute top-1.5 ltr:right-1.5 rtl:left-1.5 z-30 w-5 h-5 rounded-full pointer-events-none flex items-center justify-center"
			:class="{
				'border border-white bg-black/40': !props.isSelected,
			}"
		>
			<UIcon v-if="props.isSelected" name="lucide:check-circle" class="text-lg text-primary" />
		</div>
	</router-link>
</template>
<script setup lang="ts">
import { computed, toRef } from "vue";
import ThumbBadge from "@/v8/components/gallery/albumModule/thumbs/ThumbBadge.vue";
import AlbumThumbImage from "@/v8/components/gallery/albumModule/thumbs/AlbumThumbImage.vue";
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
import { ALBUM_BADGE_BG } from "@/v8/utils/albumBadgeColors";
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";
import { useAlbumFlags } from "@/v8/composables/album/albumFlags";

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

const emits = defineEmits<{
	touchSelect: [event: MouseEvent];
}>();

const { canInteractAlbum } = useAlbumActions();
const router = useRouter();
const userStore = useUserStore();
const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();

const lycheeStore = useLycheeStateStore();

const togglableStore = useTogglablesStateStore();
const { getPlayIcon } = useImageHelpers();
const {
	display_thumb_album_overlay,
	number_albums_per_row_mobile,
	is_nsfw_background_blurred,
	is_smart_album_flags_enabled,
	is_cover_id_flag_enabled,
} = storeToRefs(lycheeStore);
const { is_touch_select_mode } = storeToRefs(togglableStore);

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

const { isSmartAlbum, scopeFlagsEnabled, showSensitiveFlag, showPublicHiddenFlag, showPublicVisibleFlag, showPasswordFlag } = useAlbumFlags(
	toRef(props, "album"),
);
</script>
