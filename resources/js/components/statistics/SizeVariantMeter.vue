<template>
	<MeterGroup :value="sizeVariantSpaceMeter" v-if="sizeVariantSpaceMeter && sizeVariantSpaceMeter.length > 0">
		<template #label="{ value }">
			<div class="flex flex-wrap gap-2 xl:gap-6 w-full sm:justify-between justify-center">
				<template v-for="val of value" :key="val.label">
					<Card class="w-2/5 sm:w-auto border border-surface shadow-none">
						<template #content>
							<div class="flex justify-between gap-8">
								<div class="flex gap-1 flex-col">
									<span class="text-xs sm:text-sm">
										<span class="rounded-full h-3 w-3 inline-block mr-1 sm:mr-2" :style="'background-color: ' + val.color" />
										{{ val.label }}
									</span>
									<span class="font-bold text-base">{{ val.size }}</span>
								</div>
							</div>
						</template>
					</Card>
				</template>
			</div>
		</template>
	</MeterGroup>
	<div v-else class="text-center">{{ $t("statistics.no_data") }}</div>
</template>
<script setup lang="ts">
import { usePreviewData } from "@/composables/preview/getPreviewInfo";
import StatisticsService from "@/services/statistics-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { sizeToUnit, sizeVariantToColour } from "@/utils/StatsSizeVariantToColours";
import { storeToRefs } from "pinia";
import Card from "primevue/card";
import MeterGroup from "primevue/metergroup";
import { ref } from "vue";

type SizeVariantData = {
	label: string;
	value: number;
	size: string;
	color: string;
	icon: string;
};

const props = defineProps<{
	albumId: string | null;
}>();

const lycheeStore = useLycheeStateStore();
const sizeVariantSpace = ref<App.Http.Resources.Statistics.Sizes[] | undefined>(undefined);
const sizeVariantSpaceMeter = ref<SizeVariantData[] | undefined>(undefined);

const { is_se_preview_enabled } = storeToRefs(lycheeStore);

function loadSizeVariantSpace() {
	StatisticsService.getSizeVariantSpace(props.albumId).then((response) => {
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
