<template>
	<UCard class="w-56 shrink-0" :ui="{ body: 'p-0 pb-4' }">
		<div class="flex flex-wrap w-full">
			<span class="w-full font-bold text-xl">{{ $t("statistics.total.total") }}</span>
			<span class="w-20 text-highlighted">{{ $t("statistics.total.photos") }}:</span>
			<span class="w-[calc(100%-5rem)] font-bold">{{ props.total.num_photos }}</span>
			<template v-if="props.total.num_albums > 1">
				<span class="w-20 text-highlighted">{{ $t("statistics.total.albums") }}:</span>
				<span class="w-[calc(100%-5rem)] font-bold">{{ props.total.num_albums }}</span>
			</template>
			<span class="w-20 text-highlighted">{{ $t("statistics.total.size") }}:</span>
			<span class="w-[calc(100%-5rem)] font-bold rtl:text-right" dir="ltr">{{ sizeToUnit(props.total.size) }}</span>
		</div>
	</UCard>
</template>
<script setup lang="ts">
import { TotalAlbum } from "@/composables/album/albumStatistics";
import { useSizeVariantStats } from "@/v8/composables/useSizeVariantStats";

const { sizeToUnit } = useSizeVariantStats();

const props = defineProps<{ total: TotalAlbum }>();
</script>
