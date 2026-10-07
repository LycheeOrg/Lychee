<template>
	<InsightsSection :title="$t('insights.calendar.title')">
		<p class="text-sm text-muted mb-2">
			{{ props.year !== undefined ? $t("insights.calendar.by_day") : $t("insights.calendar.by_week") }}
		</p>
		<div class="overflow-x-auto">
			<div :style="{ width: `${gridWidth()}px` }" class="mx-auto">
				<InsightsChart :option="option" :height="gridHeight(props.calendar, props.year !== undefined)" />
			</div>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { sprintf } from "sprintf-js";
import { trans, trans_choice } from "laravel-vue-i18n";
import { useInsightsTheme } from "@/v8/composables/insights/useInsightsTheme";
import { useLtRorRtL } from "@/utils/Helpers";
import { formatNumber } from "@/v8/utils/insights/format";
import { dayGridOption, gridHeight, gridWidth, weekGridOption } from "@/v8/utils/insights/options/calendar";
import InsightsChart from "./InsightsChart.vue";
import InsightsSection from "./InsightsSection.vue";

const props = defineProps<{
	calendar: App.Http.Resources.Insights.CalendarData;
	/** Year of the day grid; undefined shows the years × weeks grid. */
	year: number | undefined;
}>();

const { palette } = useInsightsTheme();
const { isRTL } = useLtRorRtL();

const labels = {
	tooltip: (when: string, count: number) => `${when}: ${trans_choice("insights.time_span.photos", count, { count: formatNumber(count) })}`,
	week: (week: number) => sprintf(trans("insights.calendar.week"), week),
	less: trans("insights.calendar.less"),
	more: trans("insights.calendar.more"),
};

const option = computed(() =>
	props.year !== undefined
		? dayGridOption(props.calendar, props.year, palette.value, labels)
		: weekGridOption(props.calendar, palette.value, labels, isRTL()),
);
</script>
