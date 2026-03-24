<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<OpenLeftMenu />
			</template>
			<template #center>
				{{ $t("people.clusters_title") }}
			</template>
			<template #end>
				<Button
					:label="$t('people.run_clustering')"
					icon="pi pi-refresh"
					severity="secondary"
					outlined
					:loading="runningClustering"
					@click="runClustering"
				/>
			</template>
		</Toolbar>

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<ProgressSpinner />
		</div>

		<div v-else-if="clusters.length === 0" class="text-muted-color text-center mt-20 p-4">
			{{ $t("people.no_clusters") }}
		</div>

		<div v-else class="p-6 flex flex-col gap-4">
			<div
				v-for="cluster in clusters"
				:key="cluster.cluster_label"
				class="border border-surface rounded-lg p-4 flex flex-col sm:flex-row items-start gap-4"
			>
				<div class="flex gap-2 shrink-0">
					<img
						v-for="(url, idx) in cluster.sample_crop_urls"
						:key="idx"
						:src="url"
						class="w-16 h-16 rounded-lg object-cover"
						loading="lazy"
					/>
					<div
						v-if="cluster.face_count > cluster.sample_crop_urls.length"
						class="w-16 h-16 rounded-lg bg-surface-100 dark:bg-surface-700 flex items-center justify-center text-sm text-muted-color"
					>
						+{{ cluster.face_count - cluster.sample_crop_urls.length }}
					</div>
				</div>

				<div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-2 w-full">
					<span class="text-sm text-muted-color">{{ cluster.face_count }} {{ $t("people.faces") }}</span>
					<InputText v-model="clusterNames[cluster.cluster_label]" :placeholder="$t('people.enter_name')" class="w-full sm:w-48" />
				</div>

				<div class="flex gap-2 shrink-0">
					<Button
						:label="$t('people.assign')"
						icon="pi pi-check"
						severity="success"
						size="small"
						:disabled="!clusterNames[cluster.cluster_label]?.trim()"
						:loading="assigningLabel === cluster.cluster_label"
						@click="assignCluster(cluster.cluster_label)"
					/>
					<Button
						:label="$t('people.dismiss')"
						icon="pi pi-times"
						severity="danger"
						size="small"
						outlined
						:loading="dismissingLabel === cluster.cluster_label"
						@click="dismissCluster(cluster.cluster_label)"
					/>
				</div>
			</div>
		</div>

		<div v-if="hasMorePages" class="flex justify-center pb-8 mt-4">
			<Button :label="$t('gallery.load_more') || 'Load more'" severity="secondary" outlined @click="loadMore" :loading="loadingMore" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import FaceClusterService, { type ClusterPreview } from "@/services/face-cluster-service";

const toast = useToast();

const clusters = ref<ClusterPreview[]>([]);
const clusterNames = reactive<Record<number, string>>({});
const loading = ref(false);
const loadingMore = ref(false);
const runningClustering = ref(false);
const assigningLabel = ref<number | null>(null);
const dismissingLabel = ref<number | null>(null);
const currentPage = ref(1);
const hasMorePages = ref(false);

function load() {
	loading.value = true;
	FaceClusterService.getClusters(1)
		.then((response) => {
			clusters.value = response.data.data;
			currentPage.value = 1;
			hasMorePages.value = response.data.meta.current_page < response.data.meta.last_page;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

function loadMore() {
	loadingMore.value = true;
	const nextPage = currentPage.value + 1;
	FaceClusterService.getClusters(nextPage)
		.then((response) => {
			clusters.value = [...clusters.value, ...response.data.data];
			currentPage.value = nextPage;
			hasMorePages.value = response.data.meta.current_page < response.data.meta.last_page;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			loadingMore.value = false;
		});
}

function assignCluster(label: number) {
	const name = clusterNames[label]?.trim();
	if (!name) return;

	assigningLabel.value = label;
	FaceClusterService.assignCluster(label, { new_person_name: name })
		.then((response) => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: `Assigned ${response.data.assigned_count} face(s) to "${name}"`,
				life: 3000,
			});
			clusters.value = clusters.value.filter((c) => c.cluster_label !== label);
			delete clusterNames[label];
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			assigningLabel.value = null;
		});
}

function dismissCluster(label: number) {
	dismissingLabel.value = label;
	FaceClusterService.dismissCluster(label)
		.then((response) => {
			toast.add({
				severity: "info",
				summary: trans("toasts.success"),
				detail: `Dismissed ${response.data.dismissed_count} face(s)`,
				life: 3000,
			});
			clusters.value = clusters.value.filter((c) => c.cluster_label !== label);
			delete clusterNames[label];
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			dismissingLabel.value = null;
		});
}

function runClustering() {
	runningClustering.value = true;
	FaceClusterService.runClustering()
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: "Clustering started. Reload when complete.", life: 5000 });
			setTimeout(() => load(), 3000);
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			runningClustering.value = false;
		});
}

onMounted(() => {
	load();
});
</script>
