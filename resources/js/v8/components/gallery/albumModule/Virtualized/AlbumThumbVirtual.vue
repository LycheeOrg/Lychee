<template>
	<router-link
		:to="{ name: albumRoutes().album, params: { albumId: album.id } }"
		class="album-thumb block relative w-full h-full animate-zoomIn group"
		:class="{
			blurred: is_nsfw_background_blurred && props.album.is_nsfw,
			'aspect-4x5': 'aspect-4x5' === aspectRatio,
			'aspect-5x4': 'aspect-5x4' === aspectRatio,
			'aspect-2x3': 'aspect-2x3' === aspectRatio,
			'aspect-3x2': 'aspect-3x2' === aspectRatio,
			'aspect-square': 'aspect-square' === aspectRatio,
			'aspect-video': 'aspect-video' === aspectRatio,
			'opacity-25!': cannotInteractWhileDragging,
		}"
		:data-album-id="props.album.id"
	>
		<!-- the v-if="!togglableStore.isDragging" is a work around to avoid weird behaviour in RTL mode. -->
		<template v-if="props.album.cover_id !== null">
			<Thumb
				v-if="!togglableStore.isDragging"
				class="thumbimg absolute w-full h-full m-0 p-0 object-cover top-0 left-0 ease-out transition-transform group-hover:-rotate-2 group-hover:-translate-x-3 group-hover:translate-y-2"
				:class="[chromeClass, cornerClass]"
				:album-id="props.album.id"
				:photo-id="props.album.cover_id"
				type="small"
			/>
			<Thumb
				v-if="!togglableStore.isDragging"
				class="thumbimg absolute w-full h-full m-0 p-0 object-cover top-0 left-0 ease-out transition-transform group-hover:rotate-6 group-hover:translate-x-3 group-hover:-translate-y-2"
				:class="[chromeClass, cornerClass]"
				:album-id="props.album.id"
				:photo-id="props.album.cover_id"
				type="small"
			/>
			<Thumb
				class="thumbimg absolute w-full h-full m-0 p-0 object-cover top-0 left-0 ease-out transition-transform"
				:class="[chromeClass, cornerClass, cssClass]"
				:album-id="props.album.id"
				:photo-id="props.album.cover_id"
				type="small"
			/>
		</template>
		<template v-else>
			<!-- No cover_id (the no-cover fallback): a password icon when
			     the subalbum itself is password-protected, a generic no-image
			     icon otherwise — the one AlbumThumbImage.vue fallback branch that
			     doesn't depend on thumb.type/thumb.placeholder (tier 2 has neither). -->
			<!-- <span class="thumbimg absolute w-full h-full m-0 p-0 top-0 left-0 flex items-center justify-center" > -->
			<img
				class="thumbimg absolute w-full h-full m-0 p-0 object-cover top-0 left-0 ease-out transition-transform"
				:alt="$t('gallery.thumbnail')"
				:src="noCoverIconSrc"
				:class="[chromeClass, cornerClass]"
				draggable="false"
			/>
			<!-- </span> -->
		</template>
		<AlbumThumbOverlay v-if="display_thumb_album_overlay !== 'never'" :album="props.album" />
		<div v-if="userStore.isLoggedIn" class="badges absolute -mt-px ml-1 top-0 left-0 flex">
			<ThumbBadge v-if="showSensitiveFlag" :class="ALBUM_BADGE_BG.nsfw" icon="warning" />
			<ThumbBadge v-if="showPublicHiddenFlag" :class="ALBUM_BADGE_BG.link" icon="eye" />
			<ThumbBadge v-if="showPublicVisibleFlag" :class="ALBUM_BADGE_BG.success" icon="eye" />
			<ThumbBadge v-if="showPasswordFlag && props.album.cover_id === null" :class="ALBUM_BADGE_BG.link" icon="lock-locked" />
			<ThumbBadge v-if="showPasswordFlag && props.album.cover_id !== null" :class="ALBUM_BADGE_BG.danger" icon="lock-unlocked" />
			<ThumbBadge
				v-if="is_cover_id_flag_enabled && props.cover_id !== null && props.cover_id === props.album.cover_id"
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
/**
 * Forked from AlbumThumb.vue — the tile component mounted per
 * child by AlbumThumbPanelVirtualList.vue/AlbumListItemVirtual.vue on the
 * flag-on path. Reuses AlbumThumbOverlay.vue/AlbumThumbDecorations.vue/
 * ThumbBadge.vue and the selection/drag styling unchanged; replaces
 * AlbumThumbImage.vue's pre-built-URL lookup with <Thumb>, since tier 2
 * supplies only cover_id, no URL (Feature 061 Non-Goals).
 *
 * Two accepted regressions vs. the flag-off AlbumThumbImage.vue (Non-Goals):
 * no video play-icon overlay, no blur-up placeholder fade-in — both need
 * thumb.type/thumb.placeholder, which tier 2 deliberately excludes.
 *
 * Sizing note: unlike AlbumThumb.vue (which sets its own responsive
 * sm:/md:/.../w-[...] classes), this component fills whatever box its
 * parent's virtualized row already sized it to (w-full h-full) — the
 * analytic itemsPerRow/tileWidth computation (albumTileWidth.ts)
 * is the one place those breakpoint widths now live.
 */
import { computed, toRef } from "vue";
import ThumbBadge from "@/v8/components/gallery/albumModule/thumbs/ThumbBadge.vue";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import AlbumThumbOverlay from "@/v8/components/gallery/albumModule/thumbs/AlbumThumbOverlay.vue";
import AlbumThumbDecorations from "@/v8/components/gallery/albumModule/thumbs/AlbumThumbDecorations.vue";
import { storeToRefs } from "pinia";
import { useImageHelpers } from "@/utils/Helpers";
import { useAlbumRoute } from "@/composables/photo/albumRoute";
import { useRouter } from "vue-router";
import { useAlbumActions } from "@/composables/album/albumActions";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { ALBUM_BADGE_BG } from "@/v8/utils/albumBadgeColors";
import { useAlbumFlags } from "@/v8/composables/album/albumFlags";
import type { AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";

const props = defineProps<{
	isSelected: boolean;
	cover_id: string | null;
	album: AdaptedAlbumTile;
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
const { getNoImageIcon, getPaswwordIcon } = useImageHelpers();
const { display_thumb_album_overlay, is_nsfw_background_blurred, is_cover_id_flag_enabled, is_rounded_corners_enabled, is_album_border_enabled } =
	storeToRefs(lycheeStore);
const { is_touch_select_mode } = storeToRefs(togglableStore);

const aspectRatio = computed(
	() => albumStore.config?.album_thumb_css_aspect_ratio ?? albumsStore.rootConfig?.album_thumb_css_aspect_ratio ?? "aspect-square",
);

const { albumRoutes } = useAlbumRoute(router);
const cannotInteractWhileDragging = computed(() => togglableStore.isDragging === true && canInteractAlbum(props.album) === false);

const noCoverIconSrc = computed(() => (props.album.is_password_required ? getPaswwordIcon() : getNoImageIcon()));

const chromeClass = computed(() => (togglableStore.isDragging && !canInteractAlbum(props.album) ? "" : "group-hover:border-primary"));

// Matches AlbumThumbImage.vue's own root-span classes (forking this
// component from AlbumThumb.vue/AlbumThumbImage.vue missed these).
const cornerClass = computed(() => ({
	"rounded-lg": is_rounded_corners_enabled.value,
	"border-solid border border-accented": is_album_border_enabled.value,
}));

const cssClass = computed(() => {
	if (props.isSelected) {
		return "outline !outline-offset-2 outline-primary-500";
	}
	return "";
});

const { showSensitiveFlag, showPublicHiddenFlag, showPublicVisibleFlag, showPasswordFlag } = useAlbumFlags(toRef(props, "album"));
</script>
