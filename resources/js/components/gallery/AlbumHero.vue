<template>
	<template v-if="props.album !== null">
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
					<a v-if="props.album.rights.can_download" class="flex-shrink-0 px-3 cursor-pointer" :title="$t('lychee.DOWNLOAD_ALBUM')">
						<!-- href="{{ route('download', ['albumIDs' => $this->albumId]) }}" -->
						<MiniIcon class="my-0 w-4 h-4 mr-0 ml-0" icon="cloud-download" />
					</a>
					<a
						v-if="props.album.rights.can_share"
						class="flex-shrink-0 px-3 cursor-pointer"
						:title="$t('lychee.SHARE_ALBUM')"
						v-on:click="openSharingModal"
					>
						<MiniIcon class="my-0 w-4 h-4 mr-0 ml-0" icon="share-ion" />
					</a>
				</div>
				<div
					v-if="props.album.preFormattedData.description"
					class="w-full max-w-full my-4 text-justify text-muted-color text-base/5 prose prose-invert prose-sm"
					v-html="props.album.preFormattedData.description"
				/>
				<!-- Fix me: output markdown here -->
				<!-- {{ props.album.preFormattedData.description }} -->
				<!-- </div> -->
			</template>
		</Card>
	</template>
</template>
<script setup lang="ts">
import Card from "primevue/card";
import MiniIcon from "@/components/icons/MiniIcon.vue";

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource | App.Http.Resources.Models.SmartAlbumResource | null;
	// config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();

const emits = defineEmits<{
	(e: "open-sharing-modal"): void;
}>();

function openSharingModal() {
	emits("open-sharing-modal");
}
</script>
