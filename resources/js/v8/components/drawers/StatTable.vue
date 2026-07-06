<template>
	<UTable
		:data="props.value"
		:columns="columns"
		class="max-w-xs w-full h-52 overflow-y-auto"
		:class="{
			'border-r-default border-r': isLTR() && !props.isLast,
			'border-l-default border-l': !isLTR() && !props.isLast,
		}"
	>
		<template #empty>{{ $t("gallery.album.stats.no_data") }}</template>
	</UTable>
</template>
<script setup lang="ts">
import type { TableColumn } from "@nuxt/ui";
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();

const props = defineProps<{
	value: { key: string; value: number }[];
	header: string;
	isLast?: boolean;
}>();

const columns: TableColumn<{ key: string; value: number }>[] = isLTR()
	? [
			{ accessorKey: "key", header: props.header },
			{ accessorKey: "value", header: "" },
		]
	: [
			{ accessorKey: "value", header: props.header },
			{ accessorKey: "key", header: "" },
		];
</script>
