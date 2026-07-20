<template>
	<div
		class="group flex items-center gap-4 px-3 py-1 cursor-pointer hover:bg-primary-400/10 ltr:flex-row rtl:flex-row-reverse"
		:class="{
			'bg-primary-100 dark:bg-primary-900/50 ring-2 ring-primary-500': isSelected,
		}"
		:data-photo-id="photo.id"
		:aria-label="ariaLabel"
		role="row"
		tabindex="0"
		@click="emits('clicked', $event, photo.id)"
		@contextmenu="emits('contexted', $event, photo.id)"
		@keydown.enter="emits('clicked', $event, photo.id)"
		@mouseenter="prefetchFaces"
	>
		<!-- Thumbnail -->
		<div class="relative block shrink-0 w-12 h-12 md:w-16 md:h-16">
			<img
				:alt="photo.title ?? $t('gallery.thumbnail')"
				class="w-full h-full object-cover object-center rounded"
				:src="photo.size_variants.small?.url ?? photo.size_variants.thumb?.url ?? srcNoImage"
				:srcset="photo.size_variants.small2x?.url ? photo.size_variants.small?.url + ' 1x, ' + photo.size_variants.small2x.url + ' 2x' : ''"
				draggable="false"
				loading="lazy"
			/>
			<!-- Video play icon overlay -->
			<div v-if="photo.precomputed.is_video" class="absolute inset-0 flex items-center justify-center">
				<UIcon name="lucide:play-circle" class="text-white text-xl drop-shadow-lg" />
			</div>
		</div>

		<!-- Content (title + metadata) -->
		<div class="flex-1 min-w-0 flex flex-col md:flex-row md:items-center md:gap-4 ltr:text-left rtl:text-right">
			<!-- Title -->
			<span class="text-toned font-medium truncate md:flex-1">
				{{ photo.title }}
			</span>

			<!-- Metadata row -->
			<div class="flex items-center gap-3 text-xs text-gray-600 dark:text-gray-400 flex-wrap md:flex-nowrap">
				<!-- Type badge -->
				<span v-if="photo.precomputed.is_video" class="flex items-center gap-1 text-blue-500">
					<UIcon name="lucide:video" class="text-2xs" />
					<span class="hidden sm:inline">Video</span>
				</span>
				<span v-else-if="photo.precomputed.is_livephoto" class="flex items-center gap-1 text-purple-500">
					<UIcon name="lucide:smartphone" class="text-2xs" />
					<span class="hidden sm:inline">Live</span>
				</span>
				<span v-else-if="photo.precomputed.is_raw" class="flex items-center gap-1 text-orange-500">
					<span class="text-2xs font-bold">RAW</span>
				</span>
				<span v-else class="flex items-center gap-1">
					<UIcon name="lucide:image" class="text-2xs" />
					<span class="hidden sm:inline">Photo</span>
				</span>

				<!-- Date -->
				<span v-if="photo.preformatted.taken_at" class="flex items-center gap-1 whitespace-nowrap">
					<UIcon name="lucide:calendar" class="text-2xs" />
					{{ photo.preformatted.taken_at }}
				</span>
				<span v-else-if="photo.preformatted.created_at" class="flex items-center gap-1 whitespace-nowrap">
					<UIcon name="lucide:calendar" class="text-2xs" />
					{{ photo.preformatted.created_at }}
				</span>

				<!-- File size -->
				<span v-if="photo.preformatted.filesize" class="hidden sm:flex items-center gap-1 whitespace-nowrap">
					<UIcon name="lucide:file" class="text-2xs" />
					{{ photo.preformatted.filesize }}
				</span>
			</div>
		</div>

		<!-- Rating stars (if present) -->
		<div v-if="showRating && photo.rating !== null" class="hidden sm:flex items-center gap-0.5">
			<UIcon
				v-for="star in 5"
				:key="star"
				class="text-2xs"
				name="lucide:star"
				:class="
					star <= (photo.rating?.rating_user ?? 0) ? `text-yellow-500 ${FILL_OVERRIDE_CLASS}` : 'text-neutral-300 dark:text-neutral-600'
				"
			/>
		</div>

		<!-- Badges -->
		<div class="flex gap-1 items-center">
			<ListBadge
				v-if="
					lycheeStore.is_highlighted_flag_enabled &&
					photo.is_highlighted &&
					(albumsStore.rootRights?.can_highlight || albumStore.rights?.can_edit)
				"
				:class="`text-yellow-500 ${FILL_OVERRIDE_CLASS}`"
				pi="lucide:flag"
			/>
			<ListBadge v-if="lycheeStore.is_cover_id_flag_enabled && userStore.isLoggedIn && isCoverId" class="fill-yellow-500" icon="folder-cover" />
			<ListBadge v-if="lycheeStore.is_header_id_flag_enabled && userStore.isLoggedIn && isHeaderId" class="text-slate-400" pi="lucide:image" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import ListBadge from "./thumbs/ListBadge.vue";
import { useImageHelpers } from "@/utils/Helpers";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useUserStore } from "@/stores/UserState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoFacesStore } from "@/stores/PhotoFacesState";
import { storeToRefs } from "pinia";
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
	isSelected: boolean;
	isCoverId: boolean;
	isHeaderId: boolean;
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent | KeyboardEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const { getNoImageIcon } = useImageHelpers();
const facesStore = usePhotoFacesStore();
const lycheeStore = useLycheeStateStore();
const userStore = useUserStore();
const albumsStore = useAlbumsStore();
const albumStore = useAlbumStore();

const { rating_album_view_mode } = storeToRefs(lycheeStore);
const srcNoImage = getNoImageIcon();

const showRating = computed(() => rating_album_view_mode.value !== "never");

const ariaLabel = computed(() => {
	const title = props.photo.title ?? "Photo";
	const date = props.photo.preformatted.taken_at ?? props.photo.preformatted.created_at ?? "";
	return `${title}, ${date}`;
});

function prefetchFaces() {
	if (lycheeStore.is_face_recognition_enabled && (props.photo.face_count ?? 0) > 0) {
		facesStore.prefetch(props.photo.id);
	}
}
</script>
