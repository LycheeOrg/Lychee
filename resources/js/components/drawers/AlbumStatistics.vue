<template>
	<Collapse class="w-full flex justify-center flex-wrap flex-row-reverse" :when="areStatisticsOpen">
		<Panel class="border-0 w-full" :pt:content:class="'flex justify-evenly flex-wrap'">
			<Card v-if="total !== undefined" class="max-w-xs w-full">
				<template #content>
					<div class="flex flex-wrap">
						<span class="w-full font-bold text-xl">{{ "Total" }}</span>
						<span class="w-20 text-muted-color-emphasis">{{ "Photos" }}:</span>
						<span class="w-[calc(100%-5rem)] font-bold">{{ total.num_photos }}</span>
						<span class="w-20 text-muted-color-emphasis">{{ "Albums" }}:</span>
						<span class="w-[calc(100%-5rem)] font-bold">{{ total.num_albums }}</span>
						<span class="w-20 text-muted-color-emphasis">{{ "Size" }}:</span>
						<span class="w-[calc(100%-5rem)] font-bold">{{ sizeToUnit(total.size) }}</span>
					</div>
				</template>
			</Card>

			<SizeVariantMeter v-if="props.config.is_model_album" :album-id="props.album.id" />

			<DataTable
				v-if="photosData.lens.length > 1"
				:value="photosData.lens"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full"
			>
				<Column field="key" header="Lens"></Column>
				<Column field="value" header="Count"></Column>
			</DataTable>
			<DataTable
				v-if="photosData.shutter.length > 1"
				:value="photosData.shutter"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full"
			>
				<Column field="key" header="Shutter speed"></Column>
				<Column field="value" header="Count"></Column>
			</DataTable>
			<DataTable
				v-if="photosData.aperture.length > 1"
				:value="photosData.aperture"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full"
			>
				<Column field="key" header="Aperture"></Column>
				<Column field="value" header="Count"></Column>
			</DataTable>
			<DataTable v-if="photosData.iso.length > 1" :value="photosData.iso" scrollable size="small" scrollHeight="13rem" class="max-w-xs w-full">
				<Column field="key" header="ISO"></Column>
				<Column field="value" header="Count"></Column>
			</DataTable>
			<DataTable
				v-if="photosData.model.length > 1"
				:value="photosData.model"
				scrollable
				size="small"
				scrollHeight="13rem"
				class="max-w-xs w-full"
			>
				<Column field="key" header="ISO"></Column>
				<Column field="value" header="Count"></Column>
			</DataTable>
		</Panel>
	</Collapse>
</template>
<script setup lang="ts">
import StatisticsService from "@/services/statistics-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { sizeToUnit } from "@/utils/StatsSizeVariantToColours";
import { storeToRefs } from "pinia";
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import { computed, Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";
import { TotalAlbum } from "../statistics/TotalCard.vue";
import { useAlbumsStatistics } from "@/composables/album/albumStatistics";
import SizeVariantMeter from "../statistics/SizeVariantMeter.vue";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const areStatisticsOpen = defineModel("visible", { default: false }) as Ref<boolean>;

const props = defineProps<{
	photos: App.Http.Resources.Models.PhotoResource[];
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();

const { getStatistics } = useAlbumsStatistics();

const photosData = ref(getStatistics(props.photos));
const albumSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Album[]);
const totalAlbumSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Album[]);

const total = computed(() => {
	if (albumSpace.value === undefined) {
		return undefined;
	}

	const sumData: TotalAlbum = {
		size: 0,
		num_photos: 0,
		num_albums: 0,
	};

	albumSpace.value
		?.filter((a) => !a.is_nsfw || are_nsfw_visible.value)
		?.reduce((acc, a) => {
			sumData.size += a.size;
			sumData.num_photos += a.num_photos;
			return acc;
		}, sumData);

	sumData.num_albums = albumSpace.value?.filter((a) => !a.is_nsfw || are_nsfw_visible.value)?.length ?? 0;

	return sumData;
});

if (props.config.is_base_album) {
	StatisticsService.getAlbumSpace(props.album.id).then((response) => {
		albumSpace.value = response.data;
	});

	StatisticsService.getTotalAlbumSpace(props.album.id).then((response) => {
		totalAlbumSpace.value = response.data;
	});
}
</script>
