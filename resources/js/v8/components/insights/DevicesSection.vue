<template>
	<InsightsSection :title="$t('insights.devices.title')">
		<template #actions>
			<USelect v-model="metric" :items="metrics" size="xs" class="w-40" :aria-label="$t('insights.devices.metric')" />
		</template>
		<div class="flex flex-col gap-3">
			<div class="flex flex-wrap items-center justify-between gap-2">
				<InsightsToggle v-model="grouping" :options="groupings" />
				<InsightsToggle v-model="category" :options="categories" />
			</div>
			<InsightsChart v-if="bars.length > 0" :option="option" :height="40 + bars.length * 28" />
			<p v-else class="text-sm text-muted text-center py-6">{{ $t("insights.empty") }}</p>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { useInsightsTheme } from "@/v8/composables/insights/useInsightsTheme";
import { useLtRorRtL } from "@/utils/Helpers";
import { barOption } from "@/v8/utils/insights/options/common";
import { deviceBars, type DeviceCategoryFilter, type DeviceMetric } from "@/v8/utils/insights/options/devices";
import InsightsChart from "./InsightsChart.vue";
import InsightsSection from "./InsightsSection.vue";
import InsightsToggle from "./InsightsToggle.vue";

type Grouping = "devices" | "manufacturers" | "lenses";

const props = defineProps<{ devices: App.Http.Resources.Insights.DevicesData }>();

const { palette } = useInsightsTheme();
const { isRTL } = useLtRorRtL();

const grouping = ref<Grouping>("devices");
const category = ref<DeviceCategoryFilter>("all");
const metric = ref<DeviceMetric>("all");

const groupings = [
	{ value: "devices" as Grouping, label: trans("insights.devices.device") },
	{ value: "manufacturers" as Grouping, label: trans("insights.devices.manufacturer") },
	{ value: "lenses" as Grouping, label: trans("insights.devices.lens") },
];
const categories: { value: DeviceCategoryFilter; label: string }[] = [
	{ value: "all", label: trans("insights.devices.category.all") },
	{ value: "camera", label: trans("insights.devices.category.camera") },
	{ value: "mobile", label: trans("insights.devices.category.mobile") },
	{ value: "other", label: trans("insights.devices.category.other") },
];
const metrics: { value: DeviceMetric; label: string }[] = [
	{ value: "all", label: trans("insights.devices.all") },
	{ value: "photos", label: trans("insights.devices.photos") },
	{ value: "videos", label: trans("insights.devices.videos") },
	{ value: "highlighted", label: trans("insights.devices.highlighted") },
	{ value: "located", label: trans("insights.devices.located") },
	{ value: "with_people", label: trans("insights.devices.with_people") },
];

const bars = computed(() => deviceBars(props.devices[grouping.value], metric.value, category.value));

const option = computed(() =>
	barOption({
		labels: bars.value.map((bar) => (bar.is_other ? trans("insights.other") : (bar.name ?? trans("insights.unknown")))),
		values: bars.value.map((bar) => bar.count),
		palette: palette.value,
		rtl: isRTL(),
		horizontal: true,
	}),
);
</script>
