<template>
	<router-link
		:to="{ name: 'photo', params: { albumid: props.album?.id, photoid: props.photo.id } }"
		class="photo group shadow-md shadow-black/25 animate-zoomIn transition-all ease-in duration-200 block absolute"
		:data-width="props.photo.size_variants.original?.width"
		:data-height="props.photo.size_variants.original?.height"
		:data-id="props.photo.id"
		:data-album-id="props.album?.id"
	>
		<!-- {{-- wire:navigate href="{{ route('livewire-gallery-photo',['albumId'=>$album_id, 'photoId' => $photo_id]) }}" --}}
	x-on:contextmenu.prevent='handleContextPhoto($event, $wire)'
	x-on:click='handleClickPhoto($event, $wire)'
	x-bind:class="select.selectedPhotos.includes('{{ $photo_id }}') ? 'outline outline-1 outline-primary-500' : ''" -->
		<span
			class="thumbimg w-full h-full border-none"
			:class="(props.photo.precomputed.is_video ? 'video' : '') + ' ' + (props.photo.precomputed.is_livephoto ? 'livephoto' : '')"
		>
			<img
				alt="Photo thumbnail"
				class="h-full w-full border-none object-cover object-center"
				:src="props.photo.size_variants.small?.url ?? props.photo.size_variants.thumb?.url ?? srcNoImage"
				:srcset="
					props.photo.size_variants.small2x ? props.photo.size_variants.small + ' 1x, ' + props.photo.size_variants.small2x + ' 2x' : ''
				"
				data-overlay="false"
				draggable="false"
			/>
		</span>
		<div class="overlay w-full absolute bottom-0 m-0 bg-gradient-to-t from-[#00000066] text-shadow-sm" :class="props.album">
			<h1 class="min-h-[19px] mt-3 mb-1 ml-3 text-surface-0 text-base font-bold overflow-hidden whitespace-nowrap text-ellipsis">
				{{ props.photo.title }}
			</h1>
			<span v-if="props.photo.preformatted.taken_at" class="block mt-0 mr-0 mb-2 ml-3 text-2xs text-surface-300">
				<span title="Camera Date"><MiniIcon icon="camera-slr" class="w-2 h-2 m-0 mr-1 fill-neutral-300" /></span
				>{{ props.photo.preformatted.taken_at }}
			</span>
			<span v-else class="block mt-0 mr-0 mb-2 ml-3 text-2xs text-surface-300">{{ props.photo.preformatted.created_at }}</span>
		</div>
		<div v-if="props.photo.precomputed.is_video" class="w-full top-0 h-full absolute hover:opacity-70 transition-opacity duration-300">
			<img class="absolute top-1/2 -translate-y-1/2 aspect-square w-fit h-fit" alt="play" :src="srcPlay" />
		</div>
		<div v-if="user" class="badges absolute mt-[-1px] ml-1 top-0 left-0">
			<ThumbBadge v-if="props.photo.is_starred" class="badge--cover bg-yellow-500" icon="folder-cover" />
			<ThumbBadge v-if="is_cover_id" class="badge--cover bg-yellow-500" icon="folder-cover" />
		</div>
	</router-link>
</template>
<script setup lang="ts">
import { ref, type Ref } from "vue";
import ThumbBadge from "@/components/gallery/thumbs/ThumbBadge.vue";
import { useAuthStore } from "@/stores/Auth";
import MiniIcon from "@/components/icons/MiniIcon.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource | null;
	photo: App.Http.Resources.Models.PhotoResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig | null;
}>();

const auth = useAuthStore();
const src = ref("");
const srcSet = ref("");
const srcPlay = ref(window.assets_url + "img/play-icon.png");
const srcNoImage = ref(window.assets_url + "img/no_image.png");

// @ts-expect-error
const is_cover_id = ref(props.album?.cover_id === props.photo.id);

const user = ref(null) as Ref<App.Http.Resources.Models.UserResource | null>;
auth.getUser().then((data) => {
	user.value = data;
});
</script>
