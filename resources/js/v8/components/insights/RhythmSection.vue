<template>
	<InsightsSection :title="$t('insights.rhythm.title')">
		<div class="grid gap-6 lg:grid-cols-2">
			<div class="flex flex-col gap-2 min-w-0">
				<h3 class="text-sm text-muted">{{ $t("insights.rhythm.week_hour") }}</h3>
				<div class="overflow-x-auto">
					<div :style="{ width: `${weekHourWidth()}px` }" class="mx-auto">
						<InsightsChart :option="weekHour" :height="weekHourHeight()" />
					</div>
				</div>
			</div>
			<div class="flex flex-col gap-2 min-w-0">
				<InsightsToggle v-model="axis" :options="axes" />
				<InsightsChart :option="bars" :height="240" />
			</div>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { useInsightsTheme } from "@/v8/composables/insights/useInsightsTheme";
import { useLtRorRtL } from "@/utils/Helpers";
import { monthNames, weekdayNames } from "@/v8/utils/insights/format";
import { barOption } from "@/v8/utils/insights/options/common";
import { weekHourHeight, weekHourOption, weekHourWidth } from "@/v8/utils/insights/options/rhythm";
import InsightsChart from "./InsightsChart.vue";
import InsightsSection from "./InsightsSection.vue";
import InsightsToggle from "./InsightsToggle.vue";

type Axis = "months" | "weekdays" | "hours";

const props = defineProps<{ rhythm: App.Http.Resources.Insights.RhythmData }>();

const { palette } = useInsightsTheme();
const { isRTL } = useLtRorRtL();

const axis = ref<Axis>("months");
const axes = [
	{ value: "months" as Axis, label: trans("insights.rhythm.by_month") },
	{ value: "weekdays" as Axis, label: trans("insights.rhythm.by_weekday") },
	{ value: "hours" as Axis, label: trans("insights.rhythm.by_hour") },
];

const legend = { less: trans("insights.calendar.less"), more: trans("insights.calendar.more") };

const weekHour = computed(() => weekHourOption(props.rhythm.week_hour, palette.value, legend, isRTL()));

const bars = computed(() => {
	const labels = {
		months: monthNames(),
		weekdays: weekdayNames(),
		hours: Array.from({ length: 24 }, (_, h) => String(h)),
	}[axis.value];

	return barOption({ labels, values: props.rhythm[axis.value], palette: palette.value, rtl: isRTL() });
});
</script>
