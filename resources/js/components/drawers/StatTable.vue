<template>
	<DataTable
		:value="props.value"
		scrollable
		size="small"
		scroll-height="13rem"
		:class="{
			'max-w-xs w-full': true,
			'border-r-surface-300 dark:border-r-surface-700 border-r': isLTR() && !props.isLast,
			'border-l-surface-300 dark:border-l-surface-700 border-l': !isLTR() && !props.isLast,
		}"
		:dt="dtScheme"
	>
		<Column v-if="isLTR()" field="key" :header="props.header"></Column>
		<Column v-else field="value" :header="props.header"></Column>
		<Column v-if="isLTR()" field="value" header=""></Column>
		<Column v-else field="key" header=""></Column>
		<template #empty>{{ $t("gallery.album.stats.no_data") }}</template>
	</DataTable>
</template>
<script setup lang="ts">
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	value: { key: string; value: number }[];
	header: string;
	isLast?: boolean;
}>();

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
