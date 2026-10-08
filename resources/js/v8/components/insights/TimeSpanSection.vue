<template>
	<InsightsSection :title="$t('insights.time_span.title')">
		<div class="flex flex-col gap-6">
			<div class="grid gap-4 sm:grid-cols-3">
				<div v-for="moment in moments" :key="moment.label" class="flex items-center gap-3 min-w-0">
					<img v-if="moment.thumb" :src="moment.thumb" alt="" class="size-14 shrink-0 rounded-lg object-cover shadow-md shadow-black/25" />
					<div v-else class="size-14 shrink-0 rounded-lg bg-elevated" />
					<div class="flex flex-col min-w-0">
						<span class="text-sm text-muted">{{ moment.label }}</span>
						<span class="font-bold text-highlighted truncate">{{ moment.value }}</span>
						<span v-if="moment.note" class="text-xs text-dimmed">{{ moment.note }}</span>
					</div>
				</div>
			</div>
			<div class="grid gap-6 grid-cols-2 lg:grid-cols-5">
				<InsightsStat :label="$t('insights.time_span.days_with_photos')" :value="formatNumber(span.days_with_photos)" :note="daysShare" />
				<InsightsStat :label="$t('insights.time_span.undated')" :value="formatNumber(span.undated)" />
				<InsightsStat :label="$t('insights.time_span.longest_break')" :value="days(span.longest_break)" :note="between(span.longest_break)" />
				<InsightsStat
					:label="$t('insights.time_span.longest_daily')"
					:value="days(span.longest_daily_streak)"
					:note="between(span.longest_daily_streak)"
				/>
				<InsightsStat
					:label="$t('insights.time_span.longest_weekly')"
					:value="weeks(span.longest_weekly_streak)"
					:note="between(span.longest_weekly_streak)"
				/>
			</div>
		</div>
	</InsightsSection>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { sprintf } from "@/utils/sprintf";
import { trans, trans_choice } from "laravel-vue-i18n";
import { formatDate, formatNumber, formatPercent } from "@/v8/utils/insights/format";
import InsightsSection from "./InsightsSection.vue";
import InsightsStat from "./InsightsStat.vue";

const props = defineProps<{ timeSpan: App.Http.Resources.Insights.TimeSpanData }>();

const span = computed(() => props.timeSpan);

const daysShare = computed(() =>
	span.value.calendar_days > 0
		? sprintf(trans("insights.time_span.share_of_days"), formatPercent(span.value.days_with_photos / span.value.calendar_days))
		: undefined,
);

const moments = computed(() => [
	{
		label: trans("insights.time_span.first"),
		value: span.value.first === null ? "—" : formatMoment(span.value.first.taken_at),
		thumb: span.value.first?.thumb_url ?? null,
		note: undefined,
	},
	{
		label: trans("insights.time_span.last"),
		value: span.value.last === null ? "—" : formatMoment(span.value.last.taken_at),
		thumb: span.value.last?.thumb_url ?? null,
		note: undefined,
	},
	{
		label: trans("insights.time_span.busiest"),
		value: span.value.busiest_day === null ? "—" : formatDate(span.value.busiest_day.date),
		thumb: span.value.busiest_day?.thumb_url ?? null,
		note:
			span.value.busiest_day === null
				? undefined
				: trans_choice("insights.time_span.photos", span.value.busiest_day.count, { count: formatNumber(span.value.busiest_day.count) }),
	},
]);

/** Local `Y-m-d H:i` → localised date and time. */
function formatMoment(takenAt: string): string {
	const [date, time] = takenAt.split(" ");
	return `${formatDate(date)} ${time}`;
}

function days(run: App.Http.Resources.Insights.SpanData | null): string {
	return run === null ? "—" : trans_choice("insights.time_span.days", run.length, { count: formatNumber(run.length) });
}

function weeks(run: App.Http.Resources.Insights.SpanData | null): string {
	return run === null ? "—" : trans_choice("insights.time_span.weeks", run.length, { count: formatNumber(run.length) });
}

function between(run: App.Http.Resources.Insights.SpanData | null): string | undefined {
	return run === null ? undefined : `${formatDate(run.from)} – ${formatDate(run.to)}`;
}
</script>
