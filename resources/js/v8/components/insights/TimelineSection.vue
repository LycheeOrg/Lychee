<template>
	<InsightsSection :title="$t('insights.timeline.title')">
		<template #actions>
			<InsightsToggle v-model="category" :options="categories" />
		</template>
		<div v-if="events.length > 0" class="flex flex-col gap-3">
			<InsightsChart :option="option" :height="380" />
			<p class="text-xs text-dimmed">{{ $t("insights.timeline.hint") }}</p>
			<ol class="flex flex-col max-h-80 overflow-y-auto divide-y divide-default">
				<li v-for="(event, i) in events" :key="i" class="flex items-baseline gap-3 py-1.5 text-sm">
					<span class="w-28 shrink-0 text-muted tabular-nums">{{ formatDate(event.date) }}</span>
					<UIcon :name="ICONS[event.category]" class="size-3.5 shrink-0 self-center" :class="TONE_CLASSES[eventTone(event.kind)]" />
					<span class="text-highlighted">{{ eventText(event, sizeToUnit) }}</span>
				</li>
			</ol>
		</div>
		<p v-else class="text-sm text-muted text-center py-6">{{ $t("insights.empty") }}</p>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { useInsightsTheme } from "@/v8/composables/insights/useInsightsTheme";
import { useSizeVariantStats } from "@/v8/composables/useSizeVariantStats";
import { useLtRorRtL } from "@/utils/Helpers";
import { formatDate } from "@/v8/utils/insights/format";
import { CATEGORIES, eventText, eventTone, timelineOption, type EventTone } from "@/v8/utils/insights/options/timeline";
import InsightsChart from "./InsightsChart.vue";
import InsightsSection from "./InsightsSection.vue";
import InsightsToggle from "./InsightsToggle.vue";

type Filter = App.Enum.TimelineCategory | "all";

/** List icons matching the chart symbols (circle, diamond, triangle, square, pin). */
const ICONS: Record<App.Enum.TimelineCategory, string> = {
	first_last: "lucide:circle",
	device: "lucide:diamond",
	milestone: "lucide:triangle",
	record: "lucide:square",
	break: "lucide:map-pin",
};

/** Same colours as the chart: firsts green, lasts red, milestones yellow. */
const TONE_CLASSES: Record<EventTone, string> = {
	first: "text-success",
	last: "text-error",
	milestone: "text-warning",
	other: "text-primary",
};

const props = defineProps<{
	timeline: App.Http.Resources.Insights.TimelineEventData[];
	calendar: App.Http.Resources.Insights.CalendarData;
}>();

const { palette } = useInsightsTheme();
const { sizeToUnit } = useSizeVariantStats();
const { isRTL } = useLtRorRtL();

const category = ref<Filter>("all");
const categories: { value: Filter; label: string }[] = [
	{ value: "all", label: trans("insights.timeline.all") },
	...CATEGORIES.map((value) => ({ value, label: trans(`insights.timeline.categories.${value}`) })),
];

const events = computed(() => props.timeline.filter((event) => category.value === "all" || event.category === category.value));

const option = computed(() => timelineOption(events.value, props.calendar, palette.value, sizeToUnit, isRTL()));
</script>
