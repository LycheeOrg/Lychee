<template>
	<Collapse class="w-full flex flex-wrap justify-center" :when="areStatisticsOpen">
		<Panel
			v-if="props.album"
			class="border-0 w-full"
			:pt:header:class="'hidden'"
			:pt:content:class="'flex sm:justify-center flex-wrap justify-start'"
		>
			<TotalCard v-if="total !== undefined" :total="total" />
			<SizeVariantMeter v-if="props.config.is_model_album" :album-id="props.album.id" />
		</Panel>
		<Panel
			v-if="photos.length > 0"
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
import { Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";
import TotalCard from "@/components/statistics/TotalCard.vue";
import { TotalAlbum, useAlbumsStatistics } from "@/composables/album/albumStatistics";
import SizeVariantMeter from "@/components/statistics/SizeVariantMeter.vue";
import StatTable from "./StatTable.vue";

const areStatisticsOpen = defineModel("visible", { default: false }) as Ref<boolean>;

const props = defineProps<{
	photos: App.Http.Resources.Models.PhotoResource[];
	album?: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();

const { getStatistics } = useAlbumsStatistics();

const photosData = ref(getStatistics(props.photos));
const totalAlbumSpace = ref<App.Http.Resources.Statistics.Album | undefined>(undefined);

const total = ref<TotalAlbum | undefined>(undefined);

if (props.config.is_model_album && props.album) {
	StatisticsService.getTotalAlbumSpace(props.album.id).then((response) => {
		totalAlbumSpace.value = response.data[0];
		total.value = {
			num_photos: totalAlbumSpace.value.num_photos,
			num_albums: totalAlbumSpace.value.num_descendants,
			size: totalAlbumSpace.value.size,
		};
	});
}
</script>
