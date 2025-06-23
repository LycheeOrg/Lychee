<template>
	<DataTable
		:value="props.value"
		scrollable
		size="small"
		scrollHeight="13rem"
		:class="{
			'max-w-xs w-full': true,
			'border-r-surface-300 dark:border-r-surface-700 border-r': isLTR() && !props.isLast,
			'border-l-surface-300 dark:border-l-surface-700 border-l': !isLTR() && !props.isLast,
		}"
		:dt="dtScheme"
	>
		<Column field="key" :header="props.header" v-if="isLTR()"></Column>
		<Column field="value" :header="props.header" v-else></Column>
		<Column field="value" header="" v-if="isLTR()"></Column>
		<Column field="key" header="" v-else></Column>
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
