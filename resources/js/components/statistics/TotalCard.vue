<template>
	<Card class="w-56 shrink-0" :pt:body:class="'p-0 pb-4'">
		<template #content>
			<div class="flex flex-wrap w-full">
				<span class="w-full font-bold text-xl">{{ $t("statistics.total.total") }}</span>
				<span class="w-20 text-muted-color-emphasis">{{ $t("statistics.total.photos") }}:</span>
				<span class="w-[calc(100%-5rem)] font-bold">{{ props.total.num_photos }}</span>
				<template v-if="props.total.num_albums > 1">
					<span class="w-20 text-muted-color-emphasis">{{ $t("statistics.total.albums") }}:</span>
					<span class="w-[calc(100%-5rem)] font-bold">{{ props.total.num_albums }}</span>
				</template>
				<span class="w-20 text-muted-color-emphasis">{{ $t("statistics.total.size") }}:</span>
				<span class="w-[calc(100%-5rem)] font-bold">{{ sizeToUnit(props.total.size) }}</span>
			</div>
		</template>
	</Card>
</template>
<script setup lang="ts">
import { sizeToUnit } from "@/utils/StatsSizeVariantToColours";
import Card from "primevue/card";

export type TotalAlbum = {
	num_photos: number;
	num_albums: number;
	size: number;
};

const props = defineProps<{ total: TotalAlbum }>();
</script>
