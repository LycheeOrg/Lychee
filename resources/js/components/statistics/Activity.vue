<template>
	<Panel class="max-w-5xl mx-auto border-0" :header="$t('statistics.punch_card.title')" v-if="counts !== undefined">
		<div class="flex justify-center">
			{{ caption }}
			<span class="ml-1" v-if="isTakenAt" v-tooltip="$t('statistics.punch_card.with-exif')">*</span>
		</div>
		<div class="flex items-start justify-center gap-4 w-[calc(1024px-2rem)]">
			<div class="flex justify-center flex-col items-center">
				<PunchCard :low="low" :medium="medium" :high="high" :data="counts" :year="year" :key="getKey(counts)" />
				<PunchCardCaption :low="low" :medium="medium" :high="high" />
			</div>
			<div class="flex flex-col gap-2 w-32">
				<div class="flex items-center">
					<ToggleSwitch id="pp_taken_at_created_at" v-model="isTakenAt" class="mr-2" @change="load" />
					<label class="text-sm" for="pp_taken_at_created_at">{{
						isTakenAt ? $t("statistics.punch_card.taken_at") : $t("statistics.punch_card.created_at")
					}}</label>
				</div>
				<div class="text-right">
					<ScrollPanel class="w-32 h-24" pt:content:class="flex flex-col">
						<span
							v-for="y in years"
							class="hover:text-primary cursor-pointer mr-4"
							:class="{ 'text-primary': y === year }"
							@click="handleYear(y)"
							>{{ y }}</span
						>
					</ScrollPanel>
				</div>
			</div>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import StatisticsService from "@/services/statistics-service";
import Panel from "primevue/panel";
import { sprintf } from "sprintf-js";
import { computed, onMounted, ref } from "vue";
import PunchCard from "./PunchCard.vue";
import PunchCardCaption from "./PunchCardCaption.vue";
import ToggleSwitch from "primevue/toggleswitch";
import { trans } from "laravel-vue-i18n";
import ScrollPanel from "primevue/scrollpanel";

const isTakenAt = ref(true);
const min = ref(365);
const max = ref(0);

const total = ref(0);
const low = ref(10);
const medium = ref(50);
const high = ref(100);

const year = ref<number | undefined>(undefined);
const caption = ref("");
const counts = ref<App.Http.Resources.Statistics.DayCount[] | undefined>(undefined);
const minCreatedAt = ref(new Date().getFullYear());
const minTakenAt = ref(new Date().getFullYear());

const years = computed(() => {
	if (counts.value === undefined) {
		return [];
	}

	const maxYear = new Date().getFullYear();
	const minYear = isTakenAt.value ? minCreatedAt.value : minCreatedAt.value;
	const listYears = [];
	for (var i = maxYear; i >= minYear; i--) {
		listYears.push(i);
	}

	return listYears;
});

function setCaption() {
	switch (true) {
		case isTakenAt.value === true && year.value === undefined:
			caption.value = sprintf(trans("statistics.punch_card.photo-taken"), total.value);
			break;
		case isTakenAt.value === false && year.value === undefined:
			caption.value = sprintf(trans("statistics.punch_card.photo-uploaded"), total.value);
			break;
		case isTakenAt.value === true && year.value !== undefined:
			caption.value = sprintf(trans("statistics.punch_card.photo-taken-in"), total.value, year.value);
			break;
		case isTakenAt.value === false && year.value !== undefined:
			caption.value = sprintf(trans("statistics.punch_card.photo-uploaded-in"), total.value, year.value);
			break;
		default:
			"";
	}
}

function load() {
	StatisticsService.getCountsOverTime(min.value, max.value, isTakenAt.value ? "taken_at" : "created_at").then((response) => {
		counts.value = response.data.data;
		low.value = response.data.low_number_of_shoots_per_day;
		medium.value = response.data.medium_number_of_shoots_per_day;
		high.value = response.data.high_number_of_shoots_per_day;
		minCreatedAt.value = parseInt(response.data.min_created_at, 10);
		minTakenAt.value = parseInt(response.data.min_taken_at, 10);
		total.value = 0;
		for (const d of counts.value) {
			total.value += d.count;
		}

		setCaption();
	});
}

function handleYear(y: number) {
	year.value = y;
	// min is the number of days since the first day of the year till now.
	min.value = Math.max(0, Math.floor((new Date().getTime() - new Date(y, 0, 1).getTime()) / 86400000) + 1);
	// max is the number of days since the first day of the next year till now.
	max.value = Math.max(0, Math.floor((new Date().getTime() - new Date(y + 1, 0, 1).getTime()) / 86400000));
	load();
}

/**
 * Generate a hash code for the given string.
 * This is used to have a unique key for the data.
 *
 * @param str
 */
function hashCode(str: string) {
	var hash = 0,
		i = 0,
		len = str.length;
	while (i < len) {
		hash = ((hash << 5) - hash + str.charCodeAt(i++)) << 0;
	}
	return hash;
}

function getKey(data: App.Http.Resources.Statistics.DayCount[]) {
	return hashCode(data.map((d) => d.date + d.count).join("|"));
}

onMounted(() => {
	load();
});
</script>
