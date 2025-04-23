<template>
	<table class="border-separate border-spacing-0.5">
		<thead>
			<tr>
				<td></td>
				<template v-for="(m, i) in months" :key="`mo${i}${m.month}${m.colspan}`">
					<td :colspan="m.colspan" class="text-xs">{{ m.colspan > 1 ? m.month : "" }}</td>
				</template>
			</tr>
		</thead>
		<tbody>
			<tr v-for="(d, i) in days" :key="`d${d}:${genDayKey(d)}`">
				<td class="text-2xs w-8">{{ i % 2 ? "" : formatDay(d) }}</td>
				<template v-for="(week, i) in weeks" :key="`week${i}:${genWeekKey(week)}`">
					<td
						v-if="week[d].count < 0 || (props.year !== undefined && week[d].date.getFullYear() > props.year)"
						class="h-3 w-3 m-0.5 rounded-xs"
					></td>
					<td
						v-else
						v-tooltip.top="{ value: format(week[d]), dt: { maxWidth: '500px' } }"
						:class="{
							'h-3 w-3 m-0.5 rounded-xs border border-surface-400 dark:border-surface-700': true,
							'bg-transparent': week[d].count === 0,
							'bg-sky-100 dark:bg-sky-100/60': week[d].count > 0 && week[d].count < low,
							'bg-sky-300 dark:bg-sky-300/80': week[d].count >= low && week[d].count < medium,
							'bg-sky-500 dark:bg-sky-500/80': week[d].count >= medium && week[d].count < high,
							'bg-sky-700 dark:bg-sky-700': week[d].count >= high,
							'!border-sky-400': week[d].date.getMonth() === 0 && week[d].date.getDate() === 1,
						}"
					></td>
				</template>
			</tr>
		</tbody>
	</table>
</template>
<script setup lang="ts">
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { onMounted, ref } from "vue";

const props = defineProps<{
	low: number;
	medium: number;
	high: number;
	data: App.Http.Resources.Statistics.DayCount[];
	year: number | undefined;
}>();

type DayData = { date: Date; count: number };
type MonthCol = { month: string; colspan: number };

const startDay = ref(1);

const weeks = ref<DayData[][]>([]);
const months = ref<MonthCol[]>([]);
const days = ref<number[]>([0, 1, 2, 3, 4, 5, 6]);

function transformData(data: App.Http.Resources.Statistics.DayCount[], year: number | undefined) {
	weeks.value = [];
	let startDate: Date;

	if (year === undefined) {
		startDate = new Date();
		startDate.setDate(startDate.getDate() - 7 * 52 - 1);
	} else {
		startDate = new Date(year, 0, 1);
	}

	// Sunday = 0, Monday = 1, ... (See below):
	let day = startDate.getDay();
	if (startDay.value === 1) {
		day = (day + 6) % 7;
	}
	const offset = day;

	for (let w = 0; w < 53; w++) {
		const week: DayData[] = [];

		// First loop to fill the week.
		for (let d = 0; d < day; d++) {
			const date = new Date(startDate);
			date.setDate(startDate.getDate() + w * 7 + d);
			week.push({ date, count: -1 });
		}

		if (w < 52 || year !== undefined) {
			for (let d = day; d < 7; d++) {
				const date = new Date(startDate);
				date.setDate(startDate.getDate() + w * 7 + d - offset);
				const candidate = date.toISOString().slice(0, 10);
				const count = data.find((c) => c.date === candidate)?.count ?? 0;
				week.push({ date, count });
			}
		} else {
			// Last week: w = 52
			for (let d = day; d < 3; d++) {
				const date = new Date(startDate);
				date.setDate(startDate.getDate() + w * 7 + d - offset);
				const candidate = date.toISOString().slice(0, 10);
				const count = data.find((c) => c.date === candidate)?.count ?? 0;
				week.push({ date, count });
			}
			// We are in the future
			for (let d = 3; d < 7; d++) {
				const date = new Date(startDate);
				date.setDate(startDate.getDate() + w * 7 + d);
				week.push({ date, count: -1 });
			}
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

function genWeekKey(week: DayData[]) {
	return week.map((w) => w.count).join("|");
}

function genDayKey(day: number): string {
	return weeks.value.map((w) => w[day].count).join("|");
}

function formatDay(dayIdx: number): string {
	const formatter = new Intl.DateTimeFormat(navigator.language, { weekday: "short" });

	const baseDate = new Date(Date.UTC(2017, 0, 1 + startDay.value)); // just a Sunday or Monday
	baseDate.setDate(baseDate.getDate() + dayIdx);

	return formatter.format(baseDate);
}

function format(d: DayData) {
	const formatter = new Intl.DateTimeFormat(navigator.language, { month: "long", day: "numeric" });
	return sprintf(trans("statistics.punch_card.tooltip"), d.count, formatter.format(d.date));
}

onMounted(() => {
	transformData(props.data, props.year);
	prepMonths();
});
</script>
