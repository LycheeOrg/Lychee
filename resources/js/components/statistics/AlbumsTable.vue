<template>
	<DataTable
		:value="albumData"
		size="small"
		scrollable
		scrollHeight="600px"
		:loading="albumData === undefined"
		v-if="albumData === undefined || albumData.length > 0"
	>
		<Column v-if="props.showUsername" field="username" header="Owner" class="w-32"></Column>
		<Column field="title" sortable header="Title"></Column>
		<Column field="num_photos" sortable header="Photos" class="w-16"></Column>
		<Column field="num_descendants" sortable header="Children" class="w-16"></Column>
		<Column field="size" header="Size" sortable class="w-32">
			<template #body="slotProps">{{ sizeToUnit(slotProps.data.size) }}</template>
		</Column>
	</DataTable>
</template>
<script setup lang="ts">
import { sizeToUnit } from "@/utils/StatsSizeVariantToColours";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import { TotalAlbum } from "./TotalCard.vue";
import { computed, ref } from "vue";
import StatisticsService from "@/services/statistics-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { usePreviewData } from "@/composables/preview/getPreviewInfo";

const lycheeStore = useLycheeStateStore();
const { is_se_preview_enabled, are_nsfw_visible } = storeToRefs(lycheeStore);
const { getAlbumSizeData } = usePreviewData();

const props = defineProps<{
	showUsername: boolean;
	isTotal: boolean;
	albumId: string | undefined;
}>();

const emits = defineEmits<{
	total: [total: TotalAlbum];
}>();

const albumSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Album[]);
const albumData = computed(() => {
	return albumSpace.value?.filter((a) => !a.is_nsfw || are_nsfw_visible.value);
});

function loadAlbumSpace() {
	if (is_se_preview_enabled.value === true) {
		getAlbumSizeData().then(function (data) {
			albumSpace.value = data;
			emits("total", getTotalAlbumData());
		});
	} else {
		StatisticsService.getAlbumSpace().then(function (response) {
			albumSpace.value = response.data;
			emits("total", getTotalAlbumData());
		});
	}
}

function loadTotalAlbumSpace() {
	if (is_se_preview_enabled.value === true) {
		getAlbumSizeData().then((data) => (albumSpace.value = data));
	} else {
		StatisticsService.getTotalAlbumSpace().then((response) => (albumSpace.value = response.data));
	}
}

function getTotalAlbumData(): TotalAlbum {
	const sumData: TotalAlbum = {
		size: 0,
		num_photos: 0,
		num_albums: 0,
	};

	albumData.value?.reduce((acc, a) => {
		sumData.size += a.size;
		sumData.num_photos += a.num_photos;
		return acc;
	}, sumData);

	sumData.num_albums = albumData.value?.length ?? 0;
	return sumData;
}

if (props.isTotal) {
	loadTotalAlbumSpace();
} else {
	loadAlbumSpace();
}
</script>
