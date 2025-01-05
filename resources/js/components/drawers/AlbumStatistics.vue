<template>
	<Collapse class="w-full flex flex-wrap justify-center" :when="areStatisticsOpen">
		<Panel class="border-0 w-full" :pt:header:class="'hidden'" :pt:content:class="'flex sm:justify-center flex-wrap justify-start'">
			<TotalCard v-if="total !== undefined" :total="total" />
			<SizeVariantMeter v-if="props.config.is_model_album" :album-id="props.album.id" />
		</Panel>
		<Panel
			class="border-0 w-full max-w-6xl"
			:pt:header:class="'hidden'"
			:pt:content:class="'flex justify-evenly shadow-inner shad shadow-black/10 rounded-xl p-4 bg-surface-50 dark:bg-surface-950/20'"
			v-if="photos.length > 0"
		>
			<DataTable
				:value="photosData.lens"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full border-r-surface-300 dark:border-r-surface-700 border-r"
				:dt="dtScheme"
			>
				<Column field="key" :header="$t('gallery.album.stats.lens')"></Column>
				<Column field="value" header=""></Column>
				<template #empty>{{ $t("gallery.album.stats.no_data") }}</template>
			</DataTable>
			<DataTable
				:value="photosData.shutter"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full border-r-surface-300 dark:border-r-surface-700 border-r"
				:dt="dtScheme"
			>
				<Column field="key" :header="$t('gallery.album.stats.shutter')"></Column>
				<Column field="value" header=""></Column>
				<template #empty>{{ $t("gallery.album.stats.no_data") }}</template>
			</DataTable>
			<DataTable
				:value="photosData.aperture"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full border-r-surface-300 dark:border-r-surface-700 border-r"
				:dt="dtScheme"
			>
				<Column field="key" :header="$t('gallery.album.stats.aperture')"></Column>
				<Column field="value" header=""></Column>
				<template #empty>{{ $t("gallery.album.stats.no_data") }}</template>
			</DataTable>
			<DataTable
				:value="photosData.iso"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full border-r-surface-300 dark:border-r-surface-700 border-r"
				:dt="dtScheme"
			>
				<Column field="key" :header="$t('gallery.album.stats.iso')"></Column>
				<Column field="value" header=""></Column>
				<template #empty>{{ $t("gallery.album.stats.no_data") }}</template>
			</DataTable>
			<DataTable :value="photosData.model" scrollable size="small" scrollHeight="13rem" class="max-w-xs w-full" :dt="dtScheme">
				<Column field="key" :header="$t('gallery.album.stats.model')"></Column>
				<Column field="value" header=""></Column>
				<template #empty>{{ $t("gallery.album.stats.no_data") }}</template>
			</DataTable>
		</Panel>
	</Collapse>
</template>
<script setup lang="ts">
import StatisticsService from "@/services/statistics-service";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import { Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";
import TotalCard, { TotalAlbum } from "../statistics/TotalCard.vue";
import { useAlbumsStatistics } from "@/composables/album/albumStatistics";
import SizeVariantMeter from "../statistics/SizeVariantMeter.vue";

const areStatisticsOpen = defineModel("visible", { default: false }) as Ref<boolean>;

const props = defineProps<{
	photos: App.Http.Resources.Models.PhotoResource[];
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();

const { getStatistics } = useAlbumsStatistics();

const photosData = ref(getStatistics(props.photos));
const totalAlbumSpace = ref<App.Http.Resources.Statistics.Album | undefined>(undefined);

const total = ref<TotalAlbum | undefined>(undefined);

if (props.config.is_model_album) {
	StatisticsService.getTotalAlbumSpace(props.album.id).then((response) => {
		totalAlbumSpace.value = response.data[0];
		total.value = {
			num_photos: totalAlbumSpace.value.num_photos,
			num_albums: totalAlbumSpace.value.num_descendants,
			size: totalAlbumSpace.value.size,
		};
	});
}

const dtScheme = {
	colorScheme: {
		light: {
			headerCellBackground: "{surface-50}",
		},
		dark: {
			headerCellBackground: "color-mix(in srgb, {surface-900}, {surface-950} 20%)",
		},
	},
};
</script>
