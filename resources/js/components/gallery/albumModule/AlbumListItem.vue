<template>
	<div
		class="group flex items-center gap-4 px-3 py-0.5 cursor-pointer hover:bg-primary-400/10 flex-row"
		:class="{
			'bg-primary-100 dark:bg-primary-900/50': isSelected,
		}"
		:data-album-id="album.id"
		@click="propagateClicked($event, album.id)"
		@contextmenu.prevent="propagateContexted($event, album.id)"
	>
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
			<AlbumThumbImage
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
			<span class="text-muted-color-emphasis font-medium truncate md:truncate-none">
				{{ album.title }}
			</span>

			<!-- Counts (inline on wide screens, stacked on narrow) -->
			<div class="flex gap-2 text-xs text-gray-600 dark:text-gray-400">
				<!-- Photo count (only if > 0) -->
				<span v-if="album.num_photos > 0" class="flex items-center gap-1">
					<i class="pi pi-image text-2xs"></i>
					{{ album.num_photos }}
				</span>

				<!-- Sub-album count (only if > 0) -->
				<span v-if="album.num_subalbums > 0" class="flex items-center gap-1">
					<i class="pi pi-folder text-2xs"></i>
					{{ album.num_subalbums }}
				</span>
			</div>
		</router-link>

		<!-- Badges (if any) -->
		<div class="flex gap-1">
			<ListBadge v-if="album.is_nsfw" class="fill-[#ff82ee]" icon="warning" />
			<ListBadge v-if="album.id === 'highlighted'" class="fill-yellow-500" icon="star" />
			<ListBadge v-if="album.id === 'unsorted'" class="fill-red-700" icon="list" />
			<ListBadge v-if="album.id === 'recent'" class="fill-blue-700" icon="clock" />
			<ListBadge v-if="album.id === 'on_this_day'" class="fill-green-600" icon="calendar" />
			<ListBadge v-if="album.id === 'untagged'" class="fill-gray-500" icon="tags" />
			<ListBadge v-if="album.is_public" :class="album.is_link_required ? 'fill-orange-400' : 'fill-green-600'" icon="eye" />
			<ListBadge v-if="album.is_password_required && album.thumb === null" class="fill-orange-400" icon="lock-locked" />
			<ListBadge v-if="album.is_password_required && album.thumb !== null" class="fill-red-700" icon="lock-unlocked" />
			<ListBadge v-if="album.is_tag_album" class="fill-green-600" icon="tags" />
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

const albumStore = useAlbumStore();
const albumsStore = useAlbumsStore();

const lycheeStore = useLycheeStateStore();

defineProps<{
	album: App.Http.Resources.Models.ThumbAlbumResource;
	isSelected: boolean;
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const { propagateClicked, propagateContexted } = usePropagateAlbumEvents(emits);

const aspectRatio = computed(
	() => albumStore.config?.album_thumb_css_aspect_ratio ?? albumsStore.rootConfig?.album_thumb_css_aspect_ratio ?? "aspect-square",
);
</script>
