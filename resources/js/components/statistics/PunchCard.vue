<template>
	<Panel class="max-w-5xl mx-auto border-0" :header="$t('statistics.punch_card.title')">
		<div class="flex justify-center flex-col items-center">
			<div>{{ sprintf($t("statistics.punch_card.photo-taken"), total) }}<span v-tooltip="$t('statistics.punch_card.with-exif')">*</span></div>
			<table class="border-separate border-spacing-1">
				<thead>
					<tr>
						<td></td>
						<td v-for="(m, i) in months" :key="'month' + i" :colspan="m.colspan" class="text-xs">{{ m.month }}</td>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(d, i) in days" :key="'d' + d">
						<td class="text-2xs w-8">{{ i % 2 ? "" : formatDay(d) }}</td>
						<td
							v-for="(week, i) in weeks"
							:key="'week' + i"
							class="h-3 w-3"
							v-tooltip.top="{ value: format(week[d]), dt: { maxWidth: '500px' } }"
							:class="{
								'h-3 w-3 border border-surface-400 dark:border-surface-700 m-0.5 rounded-xs': true,
								'bg-transparent': week[d].count === 0,
								'bg-sky-100 dark:bg-sky-100/60': week[d].count > 0 && week[d].count < low,
								'bg-sky-300 dark:bg-sky-300/80': week[d].count >= low && week[d].count < medium,
								'bg-sky-500 dark:bg-sky-500/80': week[d].count >= medium && week[d].count < high,
								'bg-sky-700 dark:bg-sky-700': week[d].count >= high,
							}"
						></td>
					</tr>
				</tbody>
			</table>
			<div class="flex justify-end text-muted-color-emphasis">
				<div>
					{{ $t("statistics.punch_card.less") }}
					<span
						v-tooltip="'= 0'"
						class="mx-0.5 inline-block h-3 w-3 bg-transparent border border-surface-400 dark:border-surface-700"
					></span>
					<span
						v-tooltip="`0 < ${low}`"
						class="mx-0.5 inline-block h-3 w-3 bg-sky-100 dark:bg-sky-100/60 border border-surface-400 dark:border-surface-700"
					></span>
					<span
						v-tooltip="`${low} < ${medium}`"
						class="mx-0.5 inline-block h-3 w-3 bg-sky-300 dark:bg-sky-300/80 border border-surface-400 dark:border-surface-700"
					></span>
					<span
						v-tooltip="`${medium} < ${high}`"
						class="mx-0.5 inline-block h-3 w-3 bg-sky-500 dark:bg-sky-500/80 border border-surface-400 dark:border-surface-700"
					></span>
					<span
						v-tooltip="`≥ ${high}`"
						class="mx-0.5 inline-block h-3 w-3 bg-sky-700 dark:bg-sky-700 border border-surface-400 dark:border-surface-700"
					></span>
					{{ $t("statistics.punch_card.more") }}
				</div>
			</div>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import StatisticsService from "@/services/statistics-service";
import { trans } from "laravel-vue-i18n";
import Panel from "primevue/panel";
import { sprintf } from "sprintf-js";
import { onMounted, ref } from "vue";

type DayData = { date: Date; count: number };
type MonthCol = { month: string; colspan: number };

const min = ref(365);
const max = ref(0);
const type = ref<"taken_at" | "created_at">("taken_at");
const startDay = ref(1);

const weeks = ref<DayData[][]>([]);
const months = ref<MonthCol[]>([]);
const days = ref<number[]>([0, 1, 2, 3, 4, 5, 6]);

const total = ref(0);

const low = ref(10);
const medium = ref(50);
const high = ref(100);

const counts = ref<App.Http.Resources.Statistics.DayCount[]>([]);

function load() {
	StatisticsService.getCountsOverTime(min.value, max.value, type.value).then((response) => {
		counts.value = response.data.data;
		low.value = response.data.low_number_of_shoots_per_day;
		medium.value = response.data.medium_number_of_shoots_per_day;
		high.value = response.data.high_number_of_shoots_per_day;
		transformData();
		prepMonths();
	});
}

function transformData() {
	weeks.value = [];
	total.value = 0;
	const startDate = new Date();
	// Sunday = 0, Monday = 1, ... (See below):
	startDate.setDate(startDate.getDate() - 7 * 52 + 1);
	let day = startDate.getDay();
	if (startDay.value === 1) {
		day = (day + 6) % 7;
	}

	for (let w = 0; w < 52; w++) {
		let week: DayData[] = [];

		// First loop to fill the week.
		for (let d = 0; d < day; d++) {
			let date = new Date(startDate);
			date.setDate(startDate.getDate() + w * 7 + d);
			week.push({ date, count: 0 });
		}

		for (let d = day; d < 7; d++) {
			let date = new Date(startDate);
			date.setDate(startDate.getDate() + w * 7 + d);
			const candidate = date.toISOString().slice(0, 10);
			const count = counts.value.find((c) => c.date === candidate)?.count ?? 0;
			week.push({ date, count });
			total.value += count;
		}
		day = 0;
		weeks.value.push(week);
	}
}

function prepMonths() {
	months.value = [];
	let previous = "";
	const formatter = new Intl.DateTimeFormat(navigator.language, { month: "short" });
	let j = 0;
	for (let i = 0; i < 52; i++) {
		const candidate = formatter.format(weeks.value[i][0].date);
		if (previous !== candidate && j > 0) {
			months.value.push({ month: previous, colspan: j });
			previous = candidate;
			j = 0;
		}
		previous = candidate;
		j += 1;
	}
	months.value.push({ month: previous, colspan: j });
}

function formatDay(dayIdx: number): string {
	const formatter = new Intl.DateTimeFormat(navigator.language, { weekday: "short" });

	var baseDate = new Date(Date.UTC(2017, 0, 1 + startDay.value)); // just a Sunday or Monday
	baseDate.setDate(baseDate.getDate() + dayIdx);

	return formatter.format(baseDate);
}

function format(d: DayData) {
	const formatter = new Intl.DateTimeFormat(navigator.language, { month: "long", day: "numeric" });
	return sprintf(trans("statistics.punch_card.tooltip"), d.count, formatter.format(d.date));
}

onMounted(() => {
	load();
});
</script>
