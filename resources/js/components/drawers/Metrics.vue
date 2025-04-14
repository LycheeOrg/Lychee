<template>
	<Drawer :closeOnEsc="false" v-model:visible="isMetricsOpen" position="right" :pt:root:class="' w-sm'">
		<div class="flex flex-col">
			<div v-for="item in prettifiedData" :key="item.action + item.ago" class="flex">
				<div class="flex flex-col w-full text-sm text-muted-color">
					<router-link :to="item.link" v-if="item.count === 1" v-html="printSingular(item)"></router-link>
					<router-link :to="item.link" v-if="item.count > 1" v-html="printPlural(item)"></router-link>
					<span class="text-xs -mt-1">{{ item.ago }}</span>
				</div>
				<div class="p-0.5 relative">
					<img :src="item.src" v-if="item.src" class="rounded w-8 h-8 object-cover" />
				</div>
			</div>
		</div>
	</Drawer>
</template>
<script setup lang="ts">
import MetricsService from "@/services/metrics-service";
import { trans } from "laravel-vue-i18n";
import Drawer from "primevue/drawer";
import { sprintf } from "sprintf-js";
import { ref } from "vue";
import { onMounted } from "vue";
import { watch } from "vue";
import { Ref } from "vue";

const isMetricsOpen = defineModel("isMetricsOpen", { default: false }) as Ref<boolean>;

type LiveMetrics = {
	ago: string;
	id: string;
	link: { name: string; params: {} };
	action: App.Enum.MetricsAction;
	title: string;
	count: number;
	src: string | null;
};
const data = ref<App.Http.Resources.Models.LiveMetricsResource[] | undefined>(undefined);
const prettifiedData = ref<LiveMetrics[] | undefined>(undefined);

function load() {
	MetricsService.get()
		.then((response) => {
			data.value = response.data;
			console.log(response.data);
			prettifyData();
		})
		.catch((error) => {
			console.error(error);
		});
}

function printSingular(data: LiveMetrics) {
	const visitor = `<span class="font-bold text-muted-color-emphasis">${trans("statistics.metrics.a_visitor")}</span>`;
	switch (data.action) {
		case "visit":
			return sprintf(trans("statistics.metrics.visit_singular"), visitor, titlize(data.title));
		case "favourite":
			return sprintf(trans("statistics.metrics.favourite_singular"), visitor, titlize(data.title));
		case "download":
			return sprintf(trans("statistics.metrics.download_singular"), visitor, titlize(data.title));
		case "shared":
			return sprintf(trans("statistics.metrics.shared_singular"), visitor, titlize(data.title));
	}
}

function printPlural(data: LiveMetrics) {
	const visitors = sprintf(`<span class="font-bold text-muted-color-emphasis">${trans("statistics.metrics.visitors")}</span>`, data.count);
	switch (data.action) {
		case "visit":
			return sprintf(trans("statistics.metrics.visit_plural"), visitors, titlize(data.title));
		case "favourite":
			return sprintf(trans("statistics.metrics.favourite_plural"), visitors, titlize(data.title));
		case "download":
			return sprintf(trans("statistics.metrics.download_plural"), visitors, titlize(data.title));
		case "shared":
			return sprintf(trans("statistics.metrics.shared_plural"), visitors, titlize(data.title));
	}
}

function titlize(title: string) {
	const t = title.length > 20 ? title.substring(0, 20) + "..." : title;
	return `<span class="font-bold text-primary-emphasis">${t}</span>`;
}

function prettifyData() {
	if (data.value === undefined || data.value.length === 0) {
		return;
	}

	prettifiedData.value = [];

	let metric = LiveMetricsToPretty(data.value[0], 0);
	for (let i = 0; i < data.value.length; i++) {
		const candidate = data.value[i];
		const ago = dateToAgo(candidate.created_at);
		if (metric.action === candidate.action && metric.ago === ago && metric.id === (candidate.album_id ?? candidate.photo_id)) {
			metric.count++;
			continue;
		} else {
			prettifiedData.value.push(metric);
			metric = LiveMetricsToPretty(candidate, 1);
		}
	}
	// Push the last metric
	prettifiedData.value.push(metric);
}

function dateToAgo(date: string) {
	const dateObj = new Date(date);
	const now = new Date();
	const diff = Math.abs(now.getTime() - dateObj.getTime());
	const seconds = Math.floor(diff / 1000);
	const minutes = Math.floor(seconds / 60);
	const hours = Math.floor(minutes / 60);
	const days = Math.floor(hours / 24);

	if (days > 0) {
		return `${days} days ago`;
	} else if (hours > 12) {
		return `12 hours ago`;
	} else if (hours > 6) {
		return `6 hours ago`;
	} else if (hours > 0) {
		return `${hours} hours ago`;
	} else if (minutes > 30) {
		return `30 minutes ago`;
	} else if (minutes > 15) {
		return `15 minutes ago`;
	} else if (minutes > 5) {
		return `${minutes} minutes ago`;
	} else if (minutes > 0) {
		return `a few minutes ago`;
	} else {
		return `a few seconds ago`;
	}
}

function LiveMetricsToPretty(data: App.Http.Resources.Models.LiveMetricsResource, count: number): LiveMetrics {
	const ago = dateToAgo(data.created_at);
	return {
		ago: ago,
		action: data.action,
		title: data.title ?? "undefined",
		id: data.photo_id ?? data.album_id ?? "undefined",
		count: count,
		src: data.url,
		link: {
			name: data.photo_id ? "photo" : "album",
			params: {
				albumid: data.album_id,
				photoid: data.photo_id,
			},
		},
	};
}

onMounted(() => {
	if (isMetricsOpen.value) {
		load();
	}
});

watch(
	() => isMetricsOpen.value,
	(newValue) => {
		if (newValue) {
			load();
		}
	},
);
</script>
