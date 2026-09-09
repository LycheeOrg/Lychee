<template>
	<div
		class="group flex items-center gap-4 px-3 py-1 cursor-pointer hover:bg-primary-400/10 ltr:flex-row rtl:flex-row-reverse absolute w-full"
		:class="{
			'bg-primary-100 dark:bg-primary-900/50 ring-2 ring-primary-500': isSelected,
		}"
		:style="boxStyle"
		:data-photo-id="photo.id"
		:aria-label="ariaLabel"
		role="row"
		tabindex="0"
		@click="emits('clicked', $event, photo.id)"
		@contextmenu="emits('contexted', $event, photo.id)"
		@keydown.enter="emits('clicked', $event, photo.id)"
	>
		<!-- Thumbnail -->
		<div class="relative block shrink-0 w-12 h-12 md:w-16 md:h-16">
			<Thumb class="w-full h-full object-cover object-center rounded" :album-id="albumId" :photo-id="photo.id" type="thumb" />
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

				<!-- File size: always absent for an SoA-sourced tile until an
				     on-demand `details` fetch resolves it (NG11/Q-065-06) — this
				     branch never renders for a photo the lightbox/a dialog hasn't
				     already opened. -->
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
			<ListBadge v-if="showHighlightedFlag" :class="`text-yellow-500 ${FILL_OVERRIDE_CLASS}`" pi="lucide:flag" />
			<ListBadge v-if="showCoverIdFlag" class="fill-yellow-500" icon="folder-cover" />
			<ListBadge v-if="showHeaderIdFlag" class="text-slate-400" pi="lucide:image" />
		</div>
	</div>
</template>

<script setup lang="ts">
/**
 * Forked from `PhotoListItem.vue` — the `list`-mode row mounted per photo by
 * `PhotoGridVirtual.vue` (FR-065-10). Same two deliberate changes as
 * `PhotoThumbVirtual.vue`: thumbnail via `<Thumb>` instead of
 * `photo.size_variants`, position/size from the `box` prop instead of
 * external layout. Face-prefetch-on-hover is dropped entirely (not just
 * made inert) — `PhotoThumbVirtual.vue`'s own doc comment covers why
 * (NG11/Q-065-06); the file-size chip stays in the markup unchanged since
 * `photo.preformatted.filesize` being empty already hides it via the
 * existing `v-if`, exactly the accepted-regression mechanism intended.
 */
import { computed, toRef } from "vue";
import ListBadge from "@/v8/components/gallery/albumModule/thumbs/ListBadge.vue";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";
import { usePhotoFlags } from "@/v8/composables/photo/photoFlags";
import type { PhotoBox } from "@/v8/composables/photo/analyticPhotoLayout";

const props = defineProps<{
	photo: App.Http.Resources.Models.PhotoResource;
	isSelected: boolean;
	isCoverId: boolean;
	isHeaderId: boolean;
	albumId: string;
	box: PhotoBox;
}>();

const emits = defineEmits<{
	clicked: [event: MouseEvent | KeyboardEvent, id: string];
	contexted: [event: MouseEvent, id: string];
}>();

const lycheeStore = useLycheeStateStore();
const { rating_album_view_mode } = storeToRefs(lycheeStore);

const { showHighlightedFlag, showCoverIdFlag, showHeaderIdFlag } = usePhotoFlags(
	toRef(props, "photo"),
	toRef(props, "isCoverId"),
	toRef(props, "isHeaderId"),
);

const showRating = computed(() => rating_album_view_mode.value !== "never");

const boxStyle = computed(() => ({
	top: `${props.box.top}px`,
	left: `${props.box.left}px`,
	width: `${props.box.width}px`,
	height: `${props.box.height}px`,
}));

const ariaLabel = computed(() => {
	const title = props.photo.title ?? "Photo";
	const date = props.photo.preformatted.taken_at ?? props.photo.preformatted.created_at ?? "";
	return `${title}, ${date}`;
});
</script>
