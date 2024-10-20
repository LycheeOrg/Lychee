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
import { TotalAlbum } from "@/views/Statistics.vue";
import { storeToRefs } from "pinia";
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import { computed, Ref, ref } from "vue";
import { Collapse } from "vue-collapsed";

const lycheeStore = useLycheeStateStore();
const { are_nsfw_visible } = storeToRefs(lycheeStore);

const areStatisticsOpen = defineModel("visible", { default: false }) as Ref<boolean>;

export type DataForTable = { key: string; value: number };

export type PhotoStats = {
	iso: DataForTable[];
	focal: DataForTable[];
	lens: DataForTable[];
	model: DataForTable[];
	shutter: DataForTable[];
	aperture: DataForTable[];
	year: DataForTable[];
	month: DataForTable[];
	day: DataForTable[];
};

const props = defineProps<{
	photos: App.Http.Resources.Models.PhotoResource[];
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.SmartAlbumResource | App.Http.Resources.Models.TagAlbumResource;
	config: App.Http.Resources.GalleryConfigs.AlbumConfig;
}>();

const photosData = ref(getStatistics(props.photos));
const albumSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Album[]);
const totalAlbumSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Album[]);
const is_collapsed = ref(false);

const albumData = computed(() => {
	if (is_collapsed.value === false) {
		return albumSpace.value?.filter((a) => !a.is_nsfw || are_nsfw_visible.value);
	}
	return totalAlbumSpace.value?.filter((a) => !a.is_nsfw || are_nsfw_visible.value);
});

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

function getStatistics(photos: App.Http.Resources.Models.PhotoResource[]): PhotoStats {
	const stats = {
		iso: {} as Record<string, number>,
		focal: {} as Record<string, number>,
		lens: {} as Record<string, number>,
		model: {} as Record<string, number>,
		shutter: {} as Record<string, number>,
		aperture: {} as Record<string, number>,
		year: {} as Record<string, number>,
		month: {} as Record<string, number>,
		day: {} as Record<string, number>,
	};
	for (const photo of photos) {
		if (photo.precomputed.is_video || photo.precomputed.is_raw) {
			continue;
		}

		if (photo.iso) {
			stats.iso[photo.iso] = stats.iso[photo.iso] ? stats.iso[photo.iso] + 1 : 1;
		}
		if (photo.focal) {
			stats.focal[photo.focal] = stats.focal[photo.focal] ? stats.focal[photo.focal] + 1 : 1;
		}
		if (photo.preformatted.aperture) {
			stats.aperture["ƒ / " + photo.preformatted.aperture] = stats.aperture["ƒ / " + photo.preformatted.aperture]
				? stats.aperture["ƒ / " + photo.preformatted.aperture] + 1
				: 1;
		}
		if (photo.lens) {
			stats.lens[photo.lens] = stats.lens[photo.lens] ? stats.lens[photo.lens] + 1 : 1;
		}
		if (photo.model) {
			stats.model[photo.model] = stats.model[photo.model] ? stats.model[photo.model] + 1 : 1;
		}
		if (photo.preformatted.shutter) {
			stats.shutter[photo.preformatted.shutter] = stats.shutter[photo.preformatted.shutter] ? stats.shutter[photo.preformatted.shutter] + 1 : 1;
		}
		if (photo.taken_at) {
			const year = photo.taken_at.slice(0, 4);
			const month = photo.taken_at.slice(0, 7);
			const day = photo.taken_at.slice(0, 10);
			stats.year[year] = stats.year[year] ? stats.year[year] + 1 : 1;
			stats.month[month] = stats.month[month] ? stats.month[month] + 1 : 1;
			stats.day[day] = stats.day[day] ? stats.day[day] + 1 : 1;
		}
	}

	return {
		iso: recordToType(stats.iso),
		focal: recordToType(stats.focal),
		lens: recordToType(stats.lens),
		model: recordToType(stats.model),
		shutter: recordToType(stats.shutter),
		aperture: recordToType(stats.aperture),
		year: recordToType(stats.year),
		month: recordToType(stats.month),
		day: recordToType(stats.day),
	};
}

function recordToType(record: Record<string, number>): DataForTable[] {
	const data = [] as DataForTable[];
	Object.entries(record).forEach(([key, value]) => {
		data.push({ key, value });
	});
	return data.sort((a, b) => b.value - a.value);
}

if (props.config.is_base_album) {
	StatisticsService.getAlbumSpace(props.album.id).then((response) => {
		albumSpace.value = response.data;
	});

	StatisticsService.getTotalAlbumSpace(props.album.id).then((response) => {
		totalAlbumSpace.value = response.data;
	});
}
</script>
