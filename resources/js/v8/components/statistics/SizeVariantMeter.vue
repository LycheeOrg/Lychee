<template>
	<div
		v-if="sizeVariantSpaceMeter && sizeVariantSpaceMeter.length > 0"
		class="flex flex-wrap gap-2 xl:gap-6 w-full sm:justify-between justify-center"
	>
		<template v-for="val of sizeVariantSpaceMeter" :key="val.label">
			<UCard class="w-2/5 sm:w-auto border border-default shadow-none">
				<div class="flex justify-between gap-8">
					<div class="flex gap-1 flex-col">
						<span class="text-xs sm:text-sm">
							<span
								class="rounded-full h-3 w-3 inline-block ltr:mr-1 rtl:mk-1 ltr:sm:mr-2 rtl:sm:ml-2"
								:style="'background-color: ' + val.color"
							/>
							{{ val.label }}
						</span>
						<span class="font-bold text-base rtl:text-right" dir="ltr">{{ val.size }}</span>
					</div>
				</div>
			</UCard>
		</template>
	</div>
	<div v-else class="text-center">{{ $t("statistics.no_data") }}</div>
</template>
<script setup lang="ts">
import { usePreviewData } from "@/composables/preview/getPreviewInfo";
import StatisticsService from "@/services/statistics-service";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { sizeToUnit, sizeVariantToColour } from "@/utils/StatsSizeVariantToColours";
import { storeToRefs } from "pinia";
import { ref } from "vue";

type SizeVariantData = {
	label: string;
	value: number;
	size: string;
	color: string;
	icon: string;
};

const albumStore = useAlbumStore();
const lycheeStore = useLycheeStateStore();
const sizeVariantSpace = ref<App.Http.Resources.Statistics.Sizes[] | undefined>(undefined);
const sizeVariantSpaceMeter = ref<SizeVariantData[] | undefined>(undefined);

const { is_se_preview_enabled } = storeToRefs(lycheeStore);

function loadSizeVariantSpace() {
	StatisticsService.getSizeVariantSpace(albumStore.albumId).then((response) => {
		sizeVariantSpace.value = response.data;
		prepSizeVariantData();
	});
}

function prepSizeVariantData() {
	if (sizeVariantSpace.value === undefined) {
		return;
	}

	const total = sizeVariantSpace.value.reduce((acc, sv) => acc + sv.size, 0);
	sizeVariantSpaceMeter.value = sizeVariantSpace.value.map((sv: App.Http.Resources.Statistics.Sizes) => {
		return {
			label: sv.label,
			value: (sv.size / total) * 100,
			size: sizeToUnit(sv.size),
			color: sizeVariantToColour(sv.type),
			icon: "",
		};
	});
}

if (is_se_preview_enabled.value === true) {
	const { getSizeVariantSizeData } = usePreviewData();
	sizeVariantSpace.value = getSizeVariantSizeData();
	prepSizeVariantData();
} else {
	loadSizeVariantSpace();
}
</script>
