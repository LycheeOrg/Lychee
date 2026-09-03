<template>
	<div
		class="group relative flex items-center gap-4 px-3 py-0.5 cursor-pointer hover:bg-primary-400/10 flex-row w-full h-full"
		:class="{
			'bg-primary-100 dark:bg-primary-900/50': isSelected,
		}"
		:data-album-id="album.id"
		@click="maySelect($event, album.id)"
		@contextmenu="propagateContexted($event, album.id)"
	>
		<!-- Touch select overlay: sits above router-links so they don't capture clicks -->
		<div v-if="is_touch_select_mode" class="absolute inset-0 z-10" />
		<!-- Touch select mode indicator -->
		<div
			v-if="is_touch_select_mode"
			class="relative z-20 shrink-0 w-5 h-5 rounded-full flex items-center justify-center"
			:class="{
				'bg-primary-500 border-2 border-white': isSelected,
				'border-2 border-accented bg-neutral-100 dark:bg-neutral-800': !isSelected,
			}"
		>
			<UIcon v-if="isSelected" name="lucide:check" class="text-white" style="font-size: 0.6rem" />
		</div>
		<!-- Thumbnail -->
		<router-link
			:to="{ name: 'album', params: { albumId: album.id } }"
			class="relative block h-8 md:h-5 shrink-0"
			:class="{
				blurred: is_nsfw_background_blurred && album.is_nsfw,
				'aspect-4x5': 'aspect-4x5' === aspectRatio,
				'aspect-5x4': 'aspect-5x4' === aspectRatio,
				'aspect-2x3': 'aspect-2x3' === aspectRatio,
				'aspect-3x2': 'aspect-3x2' === aspectRatio,
				'aspect-square': 'aspect-square' === aspectRatio,
				'aspect-video': 'aspect-video' === aspectRatio,
			}"
		>
			<Thumb
				v-if="album.cover_id !== null"
				class="thumbimg absolute w-full h-full m-0 p-0 border-0 object-cover top-0 left-0 hover:scale-800 hover:ltr:-translate-x-full hover:rtl:translate-x-full ltr:origin-left rtl:origin-right hover:z-30"
				:album-id="album.id"
				:photo-id="album.cover_id"
				type="small"
			/>
			<span v-else class="absolute w-full h-full m-0 p-0 border-0 flex items-center justify-center">
				<img class="w-1/3 h-1/3 object-contain opacity-60" :alt="$t('gallery.thumbnail')" :src="noCoverIconSrc" draggable="false" />
			</span>
		</router-link>

		<!-- Content (title + counts) -->
		<router-link
			:to="{ name: 'album', params: { albumId: album.id } }"
			class="flex-1 min-w-0 flex flex-col md:flex-row md:items-center md:gap-4 ltr:text-left rtl:text-right"
		>
			<!-- Title -->
			<span class="text-highlighted font-medium truncate md:truncate-none">
				{{ album.title }}
			</span>

			<!-- Counts (inline on wide screens, stacked on narrow) -->
			<div class="flex gap-2 text-xs text-neutral-600 dark:text-neutral-400">
				<!-- Photo count (only if > 0) -->
				<span v-if="album.num_photos > 0" class="flex items-center gap-1">
					<UIcon name="lucide:image" class="text-2xs" />
					{{ album.num_photos }}
				</span>

				<!-- Sub-album count (only if > 0) -->
				<span v-if="album.num_subalbums > 0" class="flex items-center gap-1">
					<UIcon name="lucide:folder" class="text-2xs" />
					{{ album.num_subalbums }}
				</span>
			</div>
		</router-link>

		<!-- Badges (if any) -->
		<div class="flex gap-1">
			<ListBadge v-if="showSensitiveFlag" :class="ALBUM_BADGE_FILL.nsfw" icon="warning" />
			<ListBadge v-if="showPublicHiddenFlag" :class="ALBUM_BADGE_FILL.link" icon="eye" />
			<ListBadge v-if="showPublicVisibleFlag" :class="ALBUM_BADGE_FILL.success" icon="eye" />
			<ListBadge v-if="showPasswordFlag && album.cover_id === null" :class="ALBUM_BADGE_FILL.link" icon="lock-locked" />
			<ListBadge v-if="showPasswordFlag && album.cover_id !== null" :class="ALBUM_BADGE_FILL.danger" icon="lock-unlocked" />
		</div>
	</div>
</template>

<script setup lang="ts">
/**
 * Forked from AlbumListItem.vue, same relationship
 * AlbumThumbVirtual.vue has to AlbumThumb.vue: replaces AlbumThumbImage.vue's
 * pre-built-URL lookup with <Thumb> (tier 2 supplies only cover_id, no URL),
 * and drops the isSmartAlbum branch and the tag/person-album badges — all
 * dead code for adapted tiles, since direct children of a real album are
 * never a smart/tag/person album themselves (same reasoning as
 * AlbumThumbVirtual.vue).
 *
 * Sizing note: fills whatever box AlbumListVirtual.vue's virtualized row
 * already sized it to (w-full h-full) — row height itself is a fixed
 * constant, not aspect-ratio-derived like the grid.
 */
import { computed, toRef } from "vue";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import ListBadge from "@/v8/components/gallery/albumModule/thumbs/ListBadge.vue";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { ALBUM_BADGE_FILL } from "@/v8/utils/albumBadgeColors";
import { useAlbumFlags } from "@/v8/composables/album/albumFlags";
import { useImageHelpers } from "@/utils/Helpers";
import type { AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";

const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const { is_touch_select_mode } = storeToRefs(togglableStore);
const { is_nsfw_background_blurred } = storeToRefs(lycheeStore);
const { getNoImageIcon, getPaswwordIcon } = useImageHelpers();

const props = defineProps<{
	album: AdaptedAlbumTile;
	isSelected: boolean;
}>();

const { showSensitiveFlag, showPublicHiddenFlag, showPublicVisibleFlag, showPasswordFlag } = useAlbumFlags(toRef(props, "album"));

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	selected: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const { propagateClicked, propagateContexted } = usePropagateAlbumEvents(emits);

function maySelect(e: MouseEvent, id: string) {
	if (is_touch_select_mode.value) {
		emits("selected", e, id);
		return;
	}
	propagateClicked(e, id);
}

const aspectRatio = computed(
	() => albumStore.config?.album_thumb_css_aspect_ratio ?? albumsStore.rootConfig?.album_thumb_css_aspect_ratio ?? "aspect-square",
);

const noCoverIconSrc = computed(() => (props.album.is_password_required ? getPaswwordIcon() : getNoImageIcon()));
</script>
