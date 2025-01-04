<template>
	<DataTable
		:value="albumData"
		size="small"
		scrollable
		scrollHeight="600px"
		:loading="albumData === undefined"
		v-if="albumData === undefined || albumData.length > 0"
	>
		<Column v-if="props.showUsername" field="username" :header="$t('statistics.table.username')" class="w-32"></Column>
		<Column field="title" sortable :header="$t('statistics.table.title')"></Column>
		<Column field="num_photos" sortable :header="$t('statistics.table.photos')" class="w-16"></Column>
		<Column field="num_descendants" sortable :header="$t('statistics.table.descendants')" class="w-16"></Column>
		<Column field="size" :header="$t('statistics.table.size')" sortable class="w-32">
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
import { useAlbumsStatistics } from "@/composables/album/albumStatistics";

const lycheeStore = useLycheeStateStore();
const { is_se_preview_enabled, are_nsfw_visible } = storeToRefs(lycheeStore);
const { getAlbumSizeData } = usePreviewData();
const { computeTotal } = useAlbumsStatistics();

const props = defineProps<{
	showUsername: boolean;
	isTotal: boolean;
	albumId: string | undefined;
}>();

const emits = defineEmits<{
	total: [total: TotalAlbum];
}>();

const albumSpace = ref<App.Http.Resources.Statistics.Album[] | undefined>(undefined);
const albumData = computed(() => {
	return albumSpace.value?.filter((a) => !a.is_nsfw || are_nsfw_visible.value);
});

function loadAlbumSpace() {
	if (is_se_preview_enabled.value === true) {
		getAlbumSizeData().then(function (data) {
			albumSpace.value = data;
			emits("total", computeTotal(data));
		});
	} else {
		StatisticsService.getAlbumSpace().then(function (response) {
			albumSpace.value = response.data;
			emits("total", computeTotal(response.data));
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

if (props.isTotal) {
	loadTotalAlbumSpace();
} else {
	loadAlbumSpace();
}
</script>
