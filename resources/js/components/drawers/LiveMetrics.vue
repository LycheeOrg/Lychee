<template>
	<Drawer v-model:visible="is_metrics_open" :close-on-esc="false" position="right" :pt:root:class="' w-sm'">
		<template #header>
			<span class="text-xl font-bold">
				{{ $t("statistics.metrics.header") }}
			</span>
		</template>
		<div class="flex flex-col">
			<div
				v-if="lycheeStore.is_se_preview_enabled"
				class="text-sm text-muted-color-emphasis mb-8"
				v-html="$t('statistics.metrics.preview_text')"
			></div>
			<div v-for="item in prettifiedData" :key="item.action + item.ago" class="flex pt-2 pb-1">
				<div class="flex flex-col w-full text-sm text-muted-color">
					<router-link v-if="item.count === 1" v-slot="{ href }" :to="item.link">
						<a :href="href" v-html="printSingular(item)"></a>
					</router-link>
					<router-link v-if="item.count > 1" v-slot="{ href }" :to="item.link">
						<a :href="href" v-html="printPlural(item)"></a>
					</router-link>
					<span class="text-xs -mt-1">{{ item.ago }}</span>
				</div>
				<div class="w-12 h-12 relative shrink-0">
					<div
						class="absolute top-0 right-0 translate-x-2.5 -translate-y-2 w-6 h-6 rounded-full bg-primary-emphasis flex justify-center items-center border-3 border-solid border-surface-0 text-surface-0 dark:border-surface-900 dark:text-surface-900"
					>
						<span
							class="pi text-2xs"
							:class="{
								'pi-bookmark': item.action === 'favourite',
								'pi-download': item.action === 'download',
								'pi-share-alt': item.action === 'shared',
								'pi-eye': item.action === 'visit',
							}"
						></span>
					</div>
					<img v-if="item.src" :src="item.src" class="rounded w-full h-full object-cover" />
				</div>
			</div>
		</div>
	</Drawer>
</template>
<script setup lang="ts">
import AlbumService from "@/services/album-service";
import MetricsService from "@/services/metrics-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useImageHelpers } from "@/utils/Helpers";
import { trans } from "laravel-vue-i18n";
import { storeToRefs } from "pinia";
import Drawer from "primevue/drawer";
import { sprintf } from "sprintf-js";
import { ref } from "vue";
import { onMounted } from "vue";
import { watch } from "vue";

const { getPlaceholderIcon } = useImageHelpers();

const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const { is_metrics_open } = storeToRefs(togglableStore);

type LiveMetrics = {
	ago: string;
	date: Date;
	id: string;
	link: { name: string; params: { albumId: string | null; photoId: string | null } };
	action: App.Enum.MetricsAction;
	title: string;
	count: number;
	src: string | null;
};
const data = ref<App.Http.Resources.Models.LiveMetricsResource[] | undefined>(undefined);
const prettifiedData = ref<LiveMetrics[] | undefined>(undefined);

function load() {
	if (lycheeStore.is_se_preview_enabled) {
		// Do not load, if the preview is enabled
		data.value = [];
		AlbumService.getAll()
			.then((response) => {
				prettifiedData.value = (response.data.albums as App.Http.Resources.Models.ThumbAlbumResource[]).map((album) => {
					const ago = dateToAgo(album.created_at);
					const ret: LiveMetrics = {
						ago: ago,
						date: new Date(album.created_at),
						action: ["visit", "favourite", "download", "shared"].at(Math.floor(Math.random() * 4)) as App.Enum.MetricsAction,
						title: album.title,
						id: album.id,
						count: Math.floor(Math.random() * 3) + 1,
						src: album.thumb?.thumb ?? getPlaceholderIcon(),
						link: {
							name: "album",
							params: {
								albumId: album.id,
								photoId: null,
							},
						},
					};
					return ret;
				});
			})
			.catch((error) => {
				console.error(error);
			});
		return;
	}

	MetricsService.get()
		.then((response) => {
			data.value = response.data;
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

	const dateMetrics: Record<string, LiveMetrics> = {};

	data.value.forEach((item) => {
		const k = genKey(item);

		if (k in dateMetrics) {
			dateMetrics[k].count++;
		} else {
			dateMetrics[k] = LiveMetricsToPretty(item, 1);
		}
	});

	prettifiedData.value = Object.values(dateMetrics).sort((a, b) => {
		return b.date.getTime() - a.date.getTime();
	});
}

function genKey(item: App.Http.Resources.Models.LiveMetricsResource) {
	return item.action + dateToAgo(item.created_at) + (item.photo_id ?? item.album_id ?? "undefined");
}

function dateToAgo(date: string) {
	const dateObj = new Date(date);
	const now = new Date();
	const diff = Math.abs(now.getTime() - dateObj.getTime());
	const seconds = Math.floor(diff / 1000);
	const minutes = Math.floor(seconds / 60);
	const hours = Math.floor(minutes / 60);
	const days = Math.floor(hours / 24);

	if (days > 1) {
		return sprintf(trans("statistics.metrics.ago.days"), days);
	} else if (days > 0) {
		return trans("statistics.metrics.ago.day");
	} else if (hours > 1) {
		return sprintf(trans("statistics.metrics.ago.hours"), hours);
	} else if (hours > 0) {
		return trans("statistics.metrics.ago.hour");
	} else if (minutes > 30) {
		return sprintf(trans("statistics.metrics.ago.minutes"), 30);
	} else if (minutes > 15) {
		return sprintf(trans("statistics.metrics.ago.minutes"), 15);
	} else if (minutes > 5) {
		return sprintf(trans("statistics.metrics.ago.minutes"), minutes);
	} else if (minutes > 0) {
		return trans("statistics.metrics.ago.few_minutes");
	} else {
		return trans("statistics.metrics.ago.seconds");
	}
}

function LiveMetricsToPretty(data: App.Http.Resources.Models.LiveMetricsResource, count: number): LiveMetrics {
	const ago = dateToAgo(data.created_at);
	return {
		ago: ago,
		date: new Date(data.created_at),
		action: data.action,
		title: data.title ?? "undefined",
		id: data.photo_id ?? data.album_id ?? "undefined",
		count: count,
		src: data.url,
		link: {
			name: "album",
			params: {
				albumId: data.album_id,
				photoId: data.photo_id,
			},
		},
	};
}

onMounted(() => {
	if (is_metrics_open.value) {
		load();
	}
});

watch(
	() => is_metrics_open.value,
	(newValue) => {
		if (newValue) {
			load();
		}
	},
);
</script>
