<template>
	<div
		class="group relative flex items-center gap-4 px-3 py-0.5 cursor-pointer hover:bg-primary-400/10 flex-row"
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
			class="relative block h-8 md:h-5"
			:class="{
				blurred: lycheeStore.is_nsfw_background_blurred && album.is_nsfw,
				'aspect-4x5': 'aspect-4x5' === aspectRatio,
				'aspect-5x4': 'aspect-5x4' === aspectRatio,
				'aspect-2x3': 'aspect-2x3' === aspectRatio,
				'aspect-3x2': 'aspect-3x2' === aspectRatio,
				'aspect-square': 'aspect-square' === aspectRatio,
				'aspect-video': 'aspect-video' === aspectRatio,
			}"
		>
			<ListBadge v-if="album.id === 'highlighted'" :class="ALBUM_BADGE_FILL.favorite" icon="star" />
			<ListBadge v-else-if="album.id === 'unsorted'" :class="ALBUM_BADGE_FILL.danger" icon="list" />
			<ListBadge v-else-if="album.id === 'recent'" :class="ALBUM_BADGE_FILL.info" icon="clock" />
			<ListBadge v-else-if="album.id === 'on_this_day'" :class="ALBUM_BADGE_FILL.success" icon="calendar" />
			<ListBadge v-else-if="album.id === 'untagged'" :class="ALBUM_BADGE_FILL.neutral" icon="tags" />
			<ListBadge v-else-if="album.id === 'one_star'" :class="ALBUM_BADGE_FILL.favorite" icon="star-1" />
			<ListBadge v-else-if="album.id === 'two_stars'" :class="ALBUM_BADGE_FILL.favorite" icon="star-2" />
			<ListBadge v-else-if="album.id === 'three_stars'" :class="ALBUM_BADGE_FILL.favorite" icon="star-3" />
			<ListBadge v-else-if="album.id === 'four_stars'" :class="ALBUM_BADGE_FILL.favorite" icon="star-4" />
			<ListBadge v-else-if="album.id === 'five_stars'" :class="ALBUM_BADGE_FILL.favorite" icon="star-5" />
			<ListBadge v-else-if="album.id === 'best_pictures'" :class="ALBUM_BADGE_TEXT.trophy" pi="lucide:trophy" />
			<ListBadge v-else-if="album.id === 'my_rated_pictures'" :class="ALBUM_BADGE_TEXT.rated" pi="lucide:trophy" />
			<ListBadge v-else-if="album.id === 'my_best_pictures'" :class="ALBUM_BADGE_TEXT.favorite" pi="lucide:trophy" />
			<AlbumThumbImage
				v-else
				class="border-none! hover:scale-800 hover:ltr:-translate-x-full hover:rtl:translate-x-full ltr:origin-left rtl:origin-right hover:z-30 top-0 left-0"
				:thumb="album.thumb"
				:is-password-protected="album.is_password_required"
			/>
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
			<ListBadge v-if="album.is_nsfw" :class="ALBUM_BADGE_FILL.nsfw" icon="warning" />
			<ListBadge v-if="album.is_public" :class="album.is_link_required ? ALBUM_BADGE_FILL.link : ALBUM_BADGE_FILL.success" icon="eye" />
			<ListBadge v-if="album.is_password_required && album.thumb === null" :class="ALBUM_BADGE_FILL.link" icon="lock-locked" />
			<ListBadge v-if="album.is_password_required && album.thumb !== null" :class="ALBUM_BADGE_FILL.danger" icon="lock-unlocked" />
			<ListBadge v-if="album.is_tag_album" :class="ALBUM_BADGE_FILL.success" icon="tags" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import AlbumThumbImage from "./thumbs/AlbumThumbImage.vue";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import ListBadge from "./thumbs/ListBadge.vue";
import { usePropagateAlbumEvents } from "@/composables/album/propagateEvents";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { ALBUM_BADGE_FILL, ALBUM_BADGE_TEXT } from "@/v8/utils/albumBadgeColors";

const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const { is_touch_select_mode } = storeToRefs(togglableStore);

defineProps<{
	album: App.Http.Resources.Models.ThumbAlbumResource;
	isSelected: boolean;
}>();

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
</script>
