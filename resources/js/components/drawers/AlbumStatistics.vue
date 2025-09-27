<template>
	<Collapse class="w-full flex flex-wrap justify-center" :when="areStatisticsOpen">
		<Panel
			v-if="albumStore.album"
			class="border-0 w-full"
			:pt:header:class="'hidden'"
			:pt:content:class="'flex sm:justify-center flex-wrap justify-start'"
		>
			<TotalCard v-if="total !== undefined" :total="total" />
			<SizeVariantMeter v-if="albumStore.config?.is_model_album" />
		</Panel>
		<Panel
			v-if="photosStore.photos.length > 0 && photosData !== undefined"
			class="border-0 w-full max-w-7xl"
			:pt:header:class="'hidden'"
			:pt:content:class="'flex justify-evenly flex-wrap lg:flex-nowrap shadow-inner shad shadow-black/10 rounded-xl p-4 bg-surface-50 dark:bg-surface-950/20'"
		>
			<StatTable :value="photosData.lens" :header="$t('gallery.album.stats.lens')" />
			<StatTable :value="photosData.shutter" :header="$t('gallery.album.stats.shutter')" />
			<StatTable :value="photosData.aperture" :header="$t('gallery.album.stats.aperture')" />
			<StatTable :value="photosData.aperture" :header="$t('gallery.album.stats.aperture')" />
			<StatTable :value="photosData.iso" :header="$t('gallery.album.stats.iso')" />
			<StatTable :value="photosData.model" :header="$t('gallery.album.stats.model')" :is-last="true" />
		</Panel>
	</Collapse>
</template>
<script setup lang="ts">
import StatisticsService from "@/services/statistics-service";
import Panel from "primevue/panel";
import { onMounted, Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";
import TotalCard from "@/components/statistics/TotalCard.vue";
import { PhotoStats, TotalAlbum, useAlbumsStatistics } from "@/composables/album/albumStatistics";
import SizeVariantMeter from "@/components/statistics/SizeVariantMeter.vue";
import StatTable from "./StatTable.vue";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumStore } from "@/stores/AlbumState";

const areStatisticsOpen = defineModel("visible", { default: false }) as Ref<boolean>;

const { getStatistics } = useAlbumsStatistics();

const photosStore = usePhotosStore();
const albumStore = useAlbumStore();

const photosData = ref(undefined as PhotoStats | undefined);
const totalAlbumSpace = ref<App.Http.Resources.Statistics.Album | undefined>(undefined);
const total = ref<TotalAlbum | undefined>(undefined);

onMounted(() => {
	if (albumStore.config?.is_model_album && albumStore.album) {
		StatisticsService.getTotalAlbumSpace(albumStore.album.id).then((response) => {
			totalAlbumSpace.value = response.data[0];
			total.value = {
				num_photos: totalAlbumSpace.value.num_photos,
				num_albums: totalAlbumSpace.value.num_descendants,
				size: totalAlbumSpace.value.size,
			};
		});
	}
	photosData.value = getStatistics(photosStore.photos);
});
</script>
