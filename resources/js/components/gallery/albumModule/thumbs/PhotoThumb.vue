<template>
	<a
		:class="{
			'photo group shadow-md shadow-black/25 animate-zoomIn transition-all ease-in duration-200 block absolute cursor-pointer': true,
			'outline outline-1.5 outline-primary-500': props.isSelected,
		}"
		:data-width="props.photo.size_variants.original?.width"
		:data-height="props.photo.size_variants.original?.height"
		:data-photo-id="props.photo.id"
	>
		<span
			class="thumbimg relative w-full h-full border-none overflow-hidden"
			:class="(props.photo.precomputed.is_video ? 'video' : '') + ' ' + (props.photo.precomputed.is_livephoto ? 'livephoto' : '')"
		>
			<img
				v-show="props.photo.size_variants.placeholder?.url"
				:alt="$t('gallery.placeholder')"
				class="absolute w-full h-full top-0 left-0 blur-md"
				:class="{ 'animate-fadeout animate-fill-forwards': isImageLoaded }"
				:src="props.photo.size_variants.placeholder?.url ?? ''"
				data-overlay="false"
				draggable="false"
			/>

			<img
				:alt="$t('gallery.thumbnail')"
				class="h-full w-full border-none object-cover object-center"
				:src="props.photo.size_variants.small?.url ?? props.photo.size_variants.thumb?.url ?? srcNoImage"
				:srcset="
					props.photo.size_variants.small2x?.url
						? props.photo.size_variants.small?.url + ' 1x, ' + props.photo.size_variants.small2x.url + ' 2x'
						: ''
				"
				data-overlay="false"
				draggable="false"
				loading="lazy"
				@load="onImageLoad"
			/>
		</span>
		<div
			:class="{
				'overlay w-full absolute bottom-0 m-0 bg-linear-to-t from-[#00000066] text-shadow-sm': true,
				'opacity-0 group-hover:opacity-100 transition-all ease-out': display_thumb_photo_overlay === 'hover',
				hidden: display_thumb_photo_overlay === 'never',
			}"
		>
			<template v-if="photo_thumb_info === 'title'">
				<h1 class="min-h-4.75 mt-3 mb-1 ltr:ml-3 rtl:mr-3 text-surface-0 text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">
					{{ props.photo.title }}
				</h1>
				<div class="last:mb-2">
					<span v-if="props.photo.preformatted.taken_at" class="block mt-0 ltr:mr-0 ltr:ml-3 rtl:mr-3 text-2xs text-surface-300">
						<span title="Camera Date"><MiniIcon icon="camera-slr" class="w-2 h-2 m-0 ltr:mr-1 rtl:ml-1 fill-neutral-300" /></span
						>{{ props.photo.preformatted.taken_at }}
					</span>
					<span v-else class="block mt-0 mr-0 ltr:ml-3 rtl:mr-3 text-2xs text-surface-300">{{ props.photo.preformatted.created_at }}</span>
				</div>
				<div v-if="is_photo_thumb_tags_enabled" class="last:mb-2">
					<span
						v-for="(tag, idx) in props.photo.tags"
						:key="`photo-thumb${props.photo.id}-tag-${idx}`"
						class="inline-block ltr:ml-3 rtl:mr-3 text-xs text-surface-300 bg-surface-800/50 rounded px-1.5 py-0.5"
					>
						{{ tag }}
					</span>
				</div>
			</template>
			<template v-else>
				<h1
					class="min-h-4.75 mt-3 mb-1 ltr:ml-3 rtl:mr-3 text-base text-ellipsis prose-invert line-clamp-3"
					v-html="props.photo.preformatted.description"
				></h1>
			</template>
		</div>
		<div
			v-if="props.photo.precomputed.is_video"
			class="w-full top-0 h-full absolute hover:opacity-70 transition-opacity duration-300 flex justify-center items-center"
		>
			<img class="absolute aspect-square w-fit h-fit" alt="play" :src="srcPlay" />
		</div>
		<div class="absolute top-0 ltr:right-0 rtl:left-0 w-1/4 flex flex-row-reverse px-1">
			<ThumbBuyMe :is-in-basket="isInBasket" @click="toggleBuyMe" v-if="props.isBuyable" />
			<ThumbFavourite v-if="is_favourite_enabled" :is-favourite="isFavourite" @click="toggleFavourite" />
		</div>
		<!-- TODO: make me an option. -->
		<div class="badges absolute -mt-px ltr:ml-1 rtl:mr-1 top-0 ltr:left-0 rtl:right-0 flex">
			<ThumbBadge
				v-if="(albumsStore.rootRights?.can_highlight || albumStore.rights?.can_edit) && props.photo.is_highlighted"
				class="bg-yellow-500"
				pi="flag-fill"
			/>
			<ThumbBadge v-if="userStore.isLoggedIn && props.isCoverId" class="bg-yellow-500" icon="folder-cover" />
			<ThumbBadge v-if="userStore.isLoggedIn && props.isHeaderId" class="bg-slate-400 hidden sm:block" pi="image" />
		</div>
		<!-- Rating Overlay -->
		<ThumbRatingOverlay v-if="rating_album_view_mode !== 'never' && props.photo.rating !== null" :rating="props.photo.rating" />
	</a>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import MiniIcon from "@/components/icons/MiniIcon.vue";
import ThumbBadge from "@/components/gallery/albumModule/thumbs/ThumbBadge.vue";
import ThumbRatingOverlay from "@/components/gallery/albumModule/thumbs/ThumbRatingOverlay.vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useImageHelpers } from "@/utils/Helpers";
import { useFavouriteStore } from "@/stores/FavouriteState";
import ThumbFavourite from "./ThumbFavourite.vue";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import ThumbBuyMe from "./ThumbBuyMe.vue";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useUserStore } from "@/stores/UserState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";

const { getNoImageIcon, getPlayIcon } = useImageHelpers();

const props = defineProps<{
	isSelected: boolean;
	isCoverId: boolean;
	isHeaderId: boolean;
	photo: App.Http.Resources.Models.PhotoResource;
	isBuyable: boolean;
}>();

const emits = defineEmits<{
	toggleBuyMe: [];
}>();

const router = useRouter();
const { getParentId } = usePhotoRoute(router);
const userStore = useUserStore();
const favourites = useFavouriteStore();
const lycheeStore = useLycheeStateStore();
const orderStore = useOrderManagementStore();
const albumsStore = useAlbumsStore();
const albumStore = useAlbumStore();
const { is_favourite_enabled, display_thumb_photo_overlay, photo_thumb_info, is_photo_thumb_tags_enabled, rating_album_view_mode } =
	storeToRefs(lycheeStore);
const srcPlay = ref(getPlayIcon());
const srcNoImage = ref(getNoImageIcon());
const isImageLoaded = ref(false);
const photoId = ref(props.photo.id);

function toggleFavourite() {
	favourites.toggle(props.photo, getParentId());
}

function toggleBuyMe() {
	emits("toggleBuyMe");
}

function onImageLoad() {
	isImageLoaded.value = true;
}

const isFavourite = computed(() => favourites.getPhotoIds.includes(props.photo.id));
const isInBasket = computed(
	() => orderStore?.order?.items?.some((item: App.Http.Resources.Shop.OrderItemResource) => item.photo_id === photoId.value) ?? false,
);

watch(
	() => props.photo.id,
	(newId, oldId) => {
		if (newId === oldId) {
			// No blur to be added needed
			return;
		}

		photoId.value = newId;
		isImageLoaded.value = false;
	},
);
</script>
