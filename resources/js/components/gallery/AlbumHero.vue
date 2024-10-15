<template>
	<div v-if="props.album.preFormattedData.url" class="w-full h-1/2 relative">
		<img class="absolute block top-0 left-0 w-full h-full object-cover object-center z-0" :src="props.album.preFormattedData.url" />
		<div class="h-full pl-7 pt-7 relative text-shadow-sm w-full bg-gradient-to-b from-black/20 via-80%">
			<h1 class="font-bold text-4xl text-surface-0">{{ props.album.title }}</h1>
			<span v-if="props.album.preFormattedData.min_max_text" class="text-surface-200 text-sm">{{
				props.album.preFormattedData.min_max_text
			}}</span>
		</div>
	</div>
	<Card class="w-full">
		<template #content>
			<div class="w-full flex flex-row-reverse">
				<div class="order-1 flex flex-col w-full">
					<h1 v-if="!props.album.preFormattedData.url" class="font-bold text-2xl">{{ props.album.title }}</h1>
					<span v-if="props.album.preFormattedData.created_at" class="block text-muted-color text-sm">
						{{ $t("lychee.ALBUM_CREATED") }} {{ props.album.preFormattedData.created_at }}
					</span>
					<span v-if="props.album.preFormattedData.copyright" class="block text-muted-color text-sm">
						{{ $t("lychee.ALBUM_COPYRIGHT") }} {{ props.album.preFormattedData.copyright }}
					</span>
					<span v-if="props.album.preFormattedData.num_children" class="block text-muted-color text-sm">
						{{ props.album.preFormattedData.num_children }} {{ $t("lychee.ALBUM_SUBALBUMS") }}
					</span>
					<span v-if="props.album.preFormattedData.num_photos" class="block text-muted-color text-sm">
						{{ props.album.preFormattedData.num_photos }} {{ $t("lychee.ALBUM_IMAGES") }}
						<span v-if="props.album.preFormattedData.license" class="text-muted-color text-sm">
							&mdash; {{ props.album.preFormattedData.license }}
						</span>
					</span>
				</div>
				<a
					v-if="props.album.rights.can_download"
					class="flex-shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-125 hover:text-color"
					:title="$t('lychee.DOWNLOAD_ALBUM')"
					@click="download"
				>
					<i class="pi pi-download" />
				</a>
				<a
					v-if="props.album.rights.can_share"
					class="flex-shrink-0 px-3 cursor-pointer text-muted-color inline-block transform duration-300 hover:scale-125 hover:text-color"
					:title="$t('lychee.SHARE_ALBUM')"
					v-on:click="openSharingModal"
				>
					<i class="pi pi-share-alt" />
				</a>
			</div>
			<div
				v-if="props.album.preFormattedData.description"
				class="w-full max-w-full my-4 text-justify text-muted-color text-base/5 prose dark:prose-invert prose-sm"
				v-html="props.album.preFormattedData.description"
			/>
		</template>
	</Card>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import Card from "primevue/card";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource;
	// config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();

const emits = defineEmits<{
	"open-sharing-modal": [];
}>();

function openSharingModal() {
	emits("open-sharing-modal");
}

function download() {
	AlbumService.download([props.album?.id]);
}
</script>
