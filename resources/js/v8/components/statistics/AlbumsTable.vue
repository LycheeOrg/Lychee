<template>
	<UTable
		v-if="albumData === undefined || albumData.length > 0"
		:data="albumData ?? []"
		:columns="columns"
		:loading="albumData === undefined"
		class="max-h-150"
	/>
</template>
<script setup lang="ts">
import { computed, h, ref } from "vue";
import StatisticsService from "@/services/statistics-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { usePreviewData } from "@/composables/preview/getPreviewInfo";
import { TotalAlbum, useAlbumsStatistics } from "@/composables/album/albumStatistics";
import { useSizeVariantStats } from "@/v8/composables/useSizeVariantStats";
import type { TableColumn } from "@nuxt/ui";
import { trans } from "laravel-vue-i18n";

const lycheeStore = useLycheeStateStore();
const { is_se_preview_enabled, are_nsfw_visible } = storeToRefs(lycheeStore);
const { getAlbumSizeData } = usePreviewData();
const { computeTotal } = useAlbumsStatistics();
const { sizeToUnit } = useSizeVariantStats();

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

const columns = computed<TableColumn<App.Http.Resources.Statistics.Album>[]>(() => {
	const cols: TableColumn<App.Http.Resources.Statistics.Album>[] = [];
	if (props.showUsername) {
		cols.push({ accessorKey: "username", header: trans("statistics.table.username") });
	}
	cols.push({ accessorKey: "title", header: trans("statistics.table.title") });
	cols.push({ accessorKey: "num_photos", header: trans("statistics.table.photos") });
	cols.push({ accessorKey: "num_descendants", header: trans("statistics.table.descendants") });
	cols.push({
		accessorKey: "size",
		header: trans("statistics.table.size"),
		cell: ({ row }) => h("span", {}, sizeToUnit(row.original.size)),
	});
	return cols;
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
