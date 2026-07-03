<template>
	<Collapse class="w-full flex flex-wrap justify-center" :when="areStatisticsOpen">
		<UCard v-if="albumStore.album" class="border-0 w-full" :ui="{ header: 'hidden', body: 'flex sm:justify-center flex-wrap justify-start' }">
			<TotalCard v-if="total !== undefined" :total="total" />
			<SizeVariantMeter v-if="albumStore.config?.is_model_album" />
		</UCard>
		<UCard
			v-if="photosStore.photos.length > 0 && photosData !== undefined"
			class="border-0 w-full max-w-7xl"
			:ui="{ header: 'hidden', body: 'flex justify-evenly flex-wrap lg:flex-nowrap shadow-inner shad shadow-black/10 rounded-xl p-4 bg-elevated/50' }"
		>
			<StatTable :value="photosData.lens" :header="$t('gallery.album.stats.lens')" />
			<StatTable :value="photosData.shutter" :header="$t('gallery.album.stats.shutter')" />
			<StatTable :value="photosData.aperture" :header="$t('gallery.album.stats.aperture')" />
			<StatTable :value="photosData.aperture" :header="$t('gallery.album.stats.aperture')" />
			<StatTable :value="photosData.iso" :header="$t('gallery.album.stats.iso')" />
			<StatTable :value="photosData.model" :header="$t('gallery.album.stats.model')" :is-last="true" />
		</UCard>
	</Collapse>
</template>
<script setup lang="ts">
import StatisticsService from "@/services/statistics-service";
import { onMounted, Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";
import TotalCard from "@/v8/components/statistics/TotalCard.vue";
import { PhotoStats, TotalAlbum, useAlbumsStatistics } from "@/composables/album/albumStatistics";
import SizeVariantMeter from "@/v8/components/statistics/SizeVariantMeter.vue";
import StatTable from "./StatTable.vue";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumStore } from "@/stores/AlbumState";
import { watch } from "vue";

const areStatisticsOpen = defineModel("open", { default: false }) as Ref<boolean>;

const { getStatistics } = useAlbumsStatistics();

const photosStore = usePhotosStore();
const albumStore = useAlbumStore();

const photosData = ref<PhotoStats | undefined>(undefined);
const totalAlbumSpace = ref<App.Http.Resources.Statistics.Album | undefined>(undefined);
const total = ref<TotalAlbum | undefined>(undefined);

function handleAlbumChange(isModelAlbum: boolean, albumId: string | undefined) {
	if (isModelAlbum && albumId !== undefined) {
		StatisticsService.getTotalAlbumSpace(albumId).then((response) => {
			const stats = response.data[0];
			totalAlbumSpace.value = stats;
			total.value = {
				num_photos: stats.num_photos,
				num_albums: stats.num_descendants,
				size: stats.size,
			};
		});
		photosData.value = getStatistics(photosStore.photos);
		return;
	}

	photosData.value = undefined;
	totalAlbumSpace.value = undefined;
	total.value = undefined;
}

onMounted(() => {
	handleAlbumChange(albumStore.config?.is_model_album ?? false, albumStore.album?.id);
});

watch(
	() => [albumStore.config?.is_model_album, albumStore.album?.id],
	([isModelAlbum, albumId], [_isModelAlbumOld, oldAlbumId]) => {
		if (albumId === oldAlbumId) {
			return;
		}
		handleAlbumChange(<boolean | null>isModelAlbum ?? false, <string | undefined>albumId);
	},
);
</script>
