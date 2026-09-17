<template>
	<div
		class="w-full md:w-2xl rounded-2xl overflow-hidden"
		:style="{
			'--header-height': props.config.image_header_height + 'rem',
			'--carousel-height': props.config.carousel_height + 'rem',
			'--top-images-height': props.config.image_header_height / 2 + 'rem',
		}"
	>
		<AlbumCardSkeletonV3 v-if="props.config.is_image_header_enabled && (loadState === 'loading' || loadState === 'idle')" />
		<template v-else>
			<div v-if="headerPhotoId !== undefined" class="w-full h-(--header-height) relative">
				<Thumb :album-id="props.album.id" :photo-id="headerPhotoId" type="small" class="w-full h-full object-cover" @click="headerClicked" />
				<Blur v-if="props.album.isNsfw">
					<UIcon name="lucide:eye-off" class="text-6xl text-white" />
				</Blur>
			</div>
			<div
				v-else-if="props.config.is_image_header_enabled && photos.length > 0"
				class="w-full bg-neutral-800 flex h-(--header-height) gap-1 flex-wrap"
			>
				<div v-for="photo in photos.slice(0, 5)" :key="`top-${photo.id}`" class="relative shrink grow flex-1/4 h-(--top-images-height)">
					<Thumb
						:album-id="props.album.id"
						:photo-id="photo.id"
						type="small"
						class="object-cover h-full w-full"
						@click="carouselClicked(photo.id)"
					/>
					<Blur v-if="props.album.isNsfw" />
				</div>
			</div>
			<div
				v-if="props.config.is_image_header_enabled && props.config.is_carousel_enabled && photos.length > 1"
				class="w-full bg-neutral-800 overflow-x-scroll flex pt-1 gap-1"
			>
				<div v-for="photo in carouselPhotos" :key="`carousel-${photo.id}`" class="block shrink-0 grow relative">
					<Thumb
						:album-id="props.album.id"
						:photo-id="photo.id"
						type="thumb"
						class="h-(--carousel-height) w-full object-cover"
						@click="carouselClicked(photo.id)"
					/>
					<Blur v-if="props.album.isNsfw" />
				</div>
			</div>
		</template>
		<div class="p-6 bg-elevated">
			<h3>
				<span class="text-2xl font-semibold text-highlighted">{{ props.album.title }}</span>
				<span v-if="props.album.ownerName" class="text-muted mx-2">{{ sprintf($t("flow.by_author"), props.album.ownerName) }}</span>
			</h3>
			<p v-if="!seeMore" class="text-muted text-xs">
				<UTooltip :text="props.album.publishedCreatedAt">
					<span>{{ props.album.diffPublishedCreatedAt }}</span>
				</UTooltip>
			</p>
			<template v-else>
				<p v-if="!props.album.minMaxText" class="text-sm text-muted">
					{{ props.album.publishedCreatedAt }}
				</p>
				<p v-if="props.album.minMaxText" class="text-sm text-muted">
					<MiniIcon icon="camera-slr" class="w-3 h-3 m-0 mr-1 -mt-1 fill-muted" />{{ props.album.minMaxText }}
				</p>
				<p v-if="props.album.numChildren" class="block text-muted text-sm">
					{{ props.album.numChildren }} {{ $t("gallery.album.hero.subalbums") }}
				</p>
				<p v-if="props.album.numPhotos" class="block text-muted text-sm">{{ props.album.numPhotos }} {{ $t("gallery.album.hero.images") }}</p>
			</template>
			<p
				v-if="props.album.description"
				:class="{
					'text-sm text-muted prose prose-sm dark:prose-invert max-w-full my-8': true,
					'line-clamp-3': !seeMore && hasMore,
				}"
				v-html="props.album.description"
			></p>
			<div class="flex justify-between items-end flex-row-reverse">
				<UButton
					v-if="props.config.is_display_open_album_button"
					variant="solid"
					as="a"
					:href="router.resolve({ name: 'flow-album', params: { albumId: props.album.id } }).href"
					color="neutral"
					class="font-bold -mb-2 -mx-2"
				>
					{{ $t("flow.open_album") }}
					<UIcon v-if="isLTR()" name="lucide:chevrons-right" />
					<UIcon v-if="!isLTR()" name="lucide:chevrons-left" />
				</UButton>
				<UButton
					v-if="hasMore && !seeMore"
					variant="ghost"
					class="font-bold text-muted hover:text-highlighted cursor-pointer p-0"
					color="neutral"
					@click="
						() => {
							seeMore = true;
						}
					"
				>
					{{ $t("flow.show_more") }}
				</UButton>
				<AlbumStatistics v-if="props.album.statistics && seeMore" :stats="props.album.statistics" />
			</div>
		</div>
	</div>
</template>
<script setup lang="ts">
/**
 * Feature 068 (FR-068-01/04/09): v3 SoA counterpart to `AlbumCard.vue`.
 * Reads album-level fields straight off the already-loaded `AdaptedFlowTile`
 * (FR-068-01's whole-scope listing); this card's own photo preview is
 * fetched lazily via `FlowState.requestCardPhotos()` the moment this
 * component is instantiated (rendering it only ever happens once the
 * virtualizer decides this card is visible ± overscan, so mounting *is*
 * the lazy trigger - no separate IntersectionObserver needed here).
 * Images render through `<Thumb>` (the existing v3 Asset-endpoint wrapper),
 * never a nested `PhotoResource[]` embedded in the Flow response itself.
 */
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import Blur from "./Blur.vue";
import AlbumCardSkeletonV3 from "./AlbumCardSkeletonV3.vue";
import AlbumStatistics from "@/v8/components/gallery/albumModule/AlbumStatistics.vue";
import MiniIcon from "@/v8/components/icons/MiniIcon.vue";
import { sprintf } from "sprintf-js";
import { useLtRorRtL } from "@/utils/Helpers";
import { useFlowStateStore } from "@/stores/FlowState";
import { storeToRefs } from "pinia";
import type { AdaptedFlowTile } from "@/v8/utils/adaptFlowTile";

const { isLTR } = useLtRorRtL();
const router = useRouter();
const seeMore = ref(false);

const props = defineProps<{
	album: AdaptedFlowTile;
	config: App.Http.Resources.Flow.InitResource;
}>();

const emits = defineEmits<{
	setSelection: [albumId: string, photoId: string];
}>();

const flowState = useFlowStateStore();
const { cardPhotosV3, cardLoadStateV3 } = storeToRefs(flowState);

const loadState = computed(() => cardLoadStateV3.value[props.album.id] ?? "idle");
const photos = computed(() => cardPhotosV3.value[props.album.id] ?? []);

onMounted(() => {
	void flowState.requestCardPhotos(props.album.id);
});

function headerClicked() {
	if (headerPhotoId.value !== undefined) {
		emits("setSelection", props.album.id, headerPhotoId.value);
	}
}

function carouselClicked(photoId: string) {
	emits("setSelection", props.album.id, photoId);
}

const hasMore = computed(() => props.config.is_compact_mode_enabled && props.config.is_image_header_enabled);

// Mirrors AlbumCard.vue's `header` computed: the explicit cover when set and
// not overridden by `is_highlight_first_picture`, else the first loaded
// preview photo. `undefined` falls through to the TopImages-style grid,
// exactly like v2.
const headerPhotoId = computed<string | undefined>(() => {
	if (!props.config.is_image_header_enabled) {
		return undefined;
	}
	if (!props.config.is_highlight_first_picture && props.album.coverId !== null) {
		return props.album.coverId;
	}
	return photos.value[0]?.id;
});

// Mirrors CarouselImages.vue's `dropFirst` prop - the header already shows
// the first photo when highlighting it, so the carousel skips it.
const carouselPhotos = computed(() => (props.config.is_highlight_first_picture ? photos.value.slice(1) : photos.value));
</script>
