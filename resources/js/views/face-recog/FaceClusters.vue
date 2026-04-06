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
				<div class="flex gap-2">
					<Button
						v-if="!isBatchMode"
						icon="pi pi-check-square"
						severity="secondary"
						text
						v-tooltip.bottom="$t('people.batch_select')"
						@click="startBatchMode"
					/>
					<template v-else>
						<Button :label="$t('people.batch_cancel')" severity="secondary" text @click="cancelBatchMode" />
						<Button
							:label="$t('people.dismiss')"
							icon="pi pi-times"
							severity="danger"
							text
							:disabled="selectedLabels.length === 0"
							:loading="batchDismissing"
							@click="batchDismiss"
						/>
					</template>
					<Button
						:label="$t('people.run_clustering')"
						class="border-none"
						icon="pi pi-refresh"
						severity="secondary"
						outlined
						:loading="runningClustering"
						@click="runClustering"
					/>
				</div>
			</template>
		</Toolbar>

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<ProgressSpinner />
		</div>

		<div v-else-if="clusters.length === 0" class="text-muted-color text-center mt-20 p-4">
			{{ $t("people.no_clusters") }}
		</div>

		<div v-else class="p-6 flex flex-col gap-4">
			<!-- Batch info bar -->
			<div v-if="isBatchMode" class="flex items-center gap-3 text-sm -mb-2">
				<Checkbox
					:modelValue="selectedLabels.length === clusters.length && clusters.length > 0"
					:indeterminate="selectedLabels.length > 0 && selectedLabels.length < clusters.length"
					binary
					@change="toggleSelectAllClusters"
				/>
				<span>{{ $t("people.batch_selected", { count: String(selectedLabels.length) }) }}</span>
			</div>
			<div
				v-for="cluster in clusters"
				:key="cluster.cluster_label"
				class="relative border border-surface rounded-lg p-4 flex flex-col sm:flex-row items-start gap-4"
				:class="{ 'ring-2 ring-primary': isBatchMode && selectedLabels.includes(cluster.cluster_label), 'cursor-pointer': isBatchMode }"
				@click="isBatchMode ? toggleClusterSelection(cluster.cluster_label) : undefined"
			>
				<!-- Batch checkbox -->
				<div v-if="isBatchMode" class="absolute top-2 right-2">
					<Checkbox
						:modelValue="selectedLabels.includes(cluster.cluster_label)"
						binary
						@click.stop="toggleClusterSelection(cluster.cluster_label)"
					/>
				</div>
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
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import FaceClusterService from "@/services/face-cluster-service";

const toast = useToast();

const clusters = ref<App.Http.Resources.Models.ClusterPreviewResource[]>([]);
const clusterNames = reactive<Record<number, string>>({});
const loading = ref(false);
const loadingMore = ref(false);
const runningClustering = ref(false);
const assigningLabel = ref<number | null>(null);
const dismissingLabel = ref<number | null>(null);
const currentPage = ref(1);
const hasMorePages = ref(false);

// Batch selection state
const isBatchMode = ref(false);
const selectedLabels = ref<number[]>([]);
const batchDismissing = ref(false);

function startBatchMode() {
	isBatchMode.value = true;
	selectedLabels.value = [];
}

function cancelBatchMode() {
	isBatchMode.value = false;
	selectedLabels.value = [];
}

function toggleClusterSelection(label: number) {
	const idx = selectedLabels.value.indexOf(label);
	if (idx === -1) {
		selectedLabels.value.push(label);
	} else {
		selectedLabels.value.splice(idx, 1);
	}
}

function toggleSelectAllClusters() {
	if (selectedLabels.value.length === clusters.value.length) {
		selectedLabels.value = [];
	} else {
		selectedLabels.value = clusters.value.map((c) => c.cluster_label);
	}
}

function batchDismiss() {
	if (selectedLabels.value.length === 0) return;
	batchDismissing.value = true;
	const toDissmiss = [...selectedLabels.value];
	Promise.all(toDissmiss.map((label) => FaceClusterService.dismissCluster(label)))
		.then(() => {
			clusters.value = clusters.value.filter((c) => !toDissmiss.includes(c.cluster_label));
			selectedLabels.value = [];
			isBatchMode.value = false;
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			batchDismissing.value = false;
		});
}

function load() {
	loading.value = true;
	FaceClusterService.getClusters(1)
		.then((response) => {
			const items = response.data.data;
			clusters.value = Array.isArray(items) ? items : (Object.values(items) as App.Http.Resources.Models.ClusterPreviewResource[]);
			currentPage.value = 1;
			hasMorePages.value = response.data.current_page < response.data.last_page;
		})
		.catch((e) => {
			console.error("Error loading face clusters:", e);
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
			const items = response.data.data;
			const newItems = Array.isArray(items) ? items : (Object.values(items) as App.Http.Resources.Models.ClusterPreviewResource[]);
			clusters.value = [...clusters.value, ...newItems];
			currentPage.value = nextPage;
			hasMorePages.value = response.data.current_page < response.data.last_page;
		})
		.catch((e) => {
			console.error("Error loading more face clusters:", e);
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
			console.error("Error assigning face cluster:", e);
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
			console.error("Error dismissing face cluster:", e);
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
			console.error("Error running face clustering:", e);
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
