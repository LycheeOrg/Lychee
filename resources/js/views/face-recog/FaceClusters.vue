<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<OpenLeftMenu />
			</template>
			<template #center>
				{{ $t("people.clusters_title") }}
			</template>
		</Toolbar>

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<ProgressSpinner />
		</div>

		<template v-else>
			<!-- Page body controls -->
			<div class="px-6 pt-4 pb-2 flex flex-wrap gap-2 items-center">
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

			<div v-if="clusters.length === 0" class="text-muted-color text-center mt-20 p-4">
				{{ $t("people.no_clusters") }}
			</div>

			<div v-else class="p-6">
				<!-- Batch info bar -->
				<div v-if="isBatchMode" class="flex items-center gap-3 text-sm mb-4">
					<Checkbox
						:modelValue="selectedLabels.length === clusters.length && clusters.length > 0"
						:indeterminate="selectedLabels.length > 0 && selectedLabels.length < clusters.length"
						binary
						@change="toggleSelectAllClusters"
					/>
					<span>{{ $t("people.batch_selected", { count: String(selectedLabels.length) }) }}</span>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
					<div
						v-for="cluster in clusters"
						:key="cluster.cluster_label"
						class="relative border border-surface rounded-lg p-4 flex flex-col items-start gap-3 cursor-pointer hover:border-primary transition-colors"
						:class="{ 'ring-2 ring-primary': isBatchMode && selectedLabels.includes(cluster.cluster_label) }"
						@click="isBatchMode ? toggleClusterSelection(cluster.cluster_label) : openClusterDetail(cluster)"
					>
						<!-- Batch checkbox -->
						<div v-if="isBatchMode" class="absolute top-2 right-2">
							<Checkbox
								:modelValue="selectedLabels.includes(cluster.cluster_label)"
								binary
								@click.stop="toggleClusterSelection(cluster.cluster_label)"
							/>
						</div>

						<div class="flex gap-2 shrink-0 flex-wrap">
							<img
								v-for="(url, idx) in cluster.sample_crop_urls"
								:key="idx"
								:src="url"
								class="w-14 h-14 rounded-lg object-cover"
								loading="lazy"
							/>
							<div
								v-if="cluster.face_count > cluster.sample_crop_urls.length"
								class="w-14 h-14 rounded-lg bg-surface-100 dark:bg-surface-700 flex items-center justify-center text-sm text-muted-color"
							>
								+{{ cluster.face_count - cluster.sample_crop_urls.length }}
							</div>
						</div>

						<div class="flex flex-col gap-2 w-full" @click.stop>
							<span class="text-sm text-muted-color">{{ cluster.face_count }} {{ $t("people.faces") }}</span>
							<AutoComplete
								v-model="clusterPersonSelect[cluster.cluster_label]"
								:suggestions="personSuggestions"
								optionLabel="name"
								:placeholder="$t('people.enter_name')"
								class="w-full"
								dropdown
								forceSelection
								@complete="searchPeople"
								@keydown.enter.stop="assignClusterWithSelection(cluster.cluster_label)"
							/>
						</div>

						<div class="flex gap-2 shrink-0" @click.stop>
							<Button
								:label="$t('people.assign')"
								icon="pi pi-check"
								severity="success"
								size="small"
								:disabled="!getClusterAssignName(cluster.cluster_label)"
								:loading="assigningLabel === cluster.cluster_label"
								@click="assignClusterWithSelection(cluster.cluster_label)"
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
			</div>

			<PaginationInfiniteScroll :loading="loadingMore" :hasMore="hasMorePages" @loadMore="loadMore" />
		</template>

		<!-- Cluster detail dialog -->
		<Dialog
			v-model:visible="detailDialogVisible"
			modal
			:header="$t('people.cluster_detail_title', { count: String(detailCluster?.face_count ?? 0) })"
			class="w-full max-w-2xl"
		>
			<div v-if="detailFacesLoading" class="flex justify-center py-6">
				<ProgressSpinner />
			</div>
			<div v-else>
				<div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-4">
					<div v-for="face in detailFaces" :key="face.id" class="relative aspect-square group">
						<img v-if="face.crop_url" :src="face.crop_url" class="w-full h-full object-cover rounded-lg" loading="lazy" />
						<div v-else class="w-full h-full bg-surface-200 dark:bg-surface-700 rounded-lg flex items-center justify-center">
							<i class="pi pi-user text-xl text-muted-color" />
						</div>
						<button
							class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
							@click="dismissSingleFace(face)"
						>
							×
						</button>
					</div>
				</div>
				<div class="flex flex-col sm:flex-row gap-3 items-end">
					<AutoComplete
						v-model="detailPersonSelect"
						:suggestions="personSuggestions"
						optionLabel="name"
						:placeholder="$t('people.enter_name')"
						class="flex-1"
						dropdown
						forceSelection
						@complete="searchPeople"
						@keydown.enter.stop="assignDetailCluster"
					/>
					<div class="flex gap-2">
						<Button
							:label="$t('people.assign')"
							icon="pi pi-check"
							severity="success"
							:disabled="!getDetailAssignName()"
							:loading="detailAssigning"
							@click="assignDetailCluster"
						/>
						<Button
							:label="$t('people.dismiss')"
							icon="pi pi-times"
							severity="danger"
							outlined
							:loading="detailDismissing"
							@click="dismissDetailCluster"
						/>
					</div>
				</div>
			</div>
		</Dialog>
	</div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from "vue";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import AutoComplete from "primevue/autocomplete";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import PaginationInfiniteScroll from "@/components/pagination/PaginationInfiniteScroll.vue";
import FaceClusterService from "@/services/face-cluster-service";
import PeopleService from "@/services/people-service";
import FaceDetectionService from "@/services/face-detection-service";

const toast = useToast();

const clusters = ref<App.Http.Resources.Models.ClusterPreviewResource[]>([]);
// Per-cluster selected person (string name or PersonResource)
const clusterPersonSelect = reactive<Record<number, App.Http.Resources.Models.PersonResource | string | null>>({});
const loading = ref(false);
const loadingMore = ref(false);
const runningClustering = ref(false);
const assigningLabel = ref<number | null>(null);
const dismissingLabel = ref<number | null>(null);
const currentPage = ref(1);
const hasMorePages = ref(false);

// All known persons for autocomplete
const allPeople = ref<App.Http.Resources.Models.PersonResource[]>([]);
const personSuggestions = ref<App.Http.Resources.Models.PersonResource[]>([]);

// Batch selection state
const isBatchMode = ref(false);
const selectedLabels = ref<number[]>([]);
const batchDismissing = ref(false);

// Cluster detail dialog state
const detailDialogVisible = ref(false);
const detailCluster = ref<App.Http.Resources.Models.ClusterPreviewResource | null>(null);
const detailFaces = ref<App.Http.Resources.Models.FaceResource[]>([]);
const detailFacesLoading = ref(false);
const detailPersonSelect = ref<App.Http.Resources.Models.PersonResource | string | null>(null);
const detailAssigning = ref(false);
const detailDismissing = ref(false);

function searchPeople(event: { query: string }) {
	const q = event.query.toLowerCase();
	personSuggestions.value = allPeople.value.filter((p) => p.name.toLowerCase().includes(q));
	// If no match, allow free-text entry by treating the query as a new name
	if (personSuggestions.value.length === 0 && q.trim().length > 0) {
		// Add a pseudo-option so forceSelection doesn't block
		personSuggestions.value = [
			{
				id: "",
				name: q.trim(),
				photo_count: 0,
				face_count: 0,
				representative_crop_url: null,
				representative_face_id: null,
				is_searchable: true,
				user_id: null,
			},
		];
	}
}

function getClusterAssignName(label: number): string | null {
	const v = clusterPersonSelect[label];
	if (!v) return null;
	if (typeof v === "string") return v.trim() || null;
	return v.name ?? null;
}

function getDetailAssignName(): string | null {
	const v = detailPersonSelect.value;
	if (!v) return null;
	if (typeof v === "string") return v.trim() || null;
	return v.name ?? null;
}

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

function loadPeople() {
	PeopleService.getPeople(1)
		.then((response) => {
			allPeople.value = response.data.persons;
		})
		.catch(() => {
			/* non-critical */
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

function resolveAssignPayload(v: App.Http.Resources.Models.PersonResource | string | null): { person_id?: string; new_person_name?: string } {
	if (!v) return {};
	if (typeof v === "string") return { new_person_name: v.trim() };
	if (v.id) return { person_id: v.id };
	return { new_person_name: v.name };
}

function assignClusterWithSelection(label: number) {
	const v = clusterPersonSelect[label];
	if (!v) return;
	const payload = resolveAssignPayload(v);
	if (!payload.person_id && !payload.new_person_name) return;

	assigningLabel.value = label;
	FaceClusterService.assignCluster(label, payload)
		.then((response) => {
			const name = typeof v === "string" ? v : v.name;
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("people.assigned_faces_to", { count: String(response.data.assigned_count), name }),
				life: 3000,
			});
			clusters.value = clusters.value.filter((c) => c.cluster_label !== label);
			delete clusterPersonSelect[label];
			// Refresh people list after potential new person
			loadPeople();
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
				detail: trans("people.dismissed_faces", { count: String(response.data.dismissed_count) }),
				life: 3000,
			});
			clusters.value = clusters.value.filter((c) => c.cluster_label !== label);
			delete clusterPersonSelect[label];
			if (detailCluster.value?.cluster_label === label) {
				detailDialogVisible.value = false;
			}
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
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.clustering_started"), life: 5000 });
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

// ── Cluster detail dialog ────────────────────────────────────────

function openClusterDetail(cluster: App.Http.Resources.Models.ClusterPreviewResource) {
	detailCluster.value = cluster;
	detailFaces.value = [];
	detailPersonSelect.value = null;
	detailDialogVisible.value = true;
	detailFacesLoading.value = true;
	FaceClusterService.getClusterFaces(cluster.cluster_label)
		.then((response) => {
			detailFaces.value = response.data.data as unknown as App.Http.Resources.Models.FaceResource[];
		})
		.catch(() => {
			/* already handled elsewhere */
		})
		.finally(() => {
			detailFacesLoading.value = false;
		});
}

function dismissSingleFace(face: App.Http.Resources.Models.FaceResource) {
	FaceDetectionService.toggleDismissed(face.id)
		.then(() => {
			detailFaces.value = detailFaces.value.filter((f) => f.id !== face.id);
			const cluster = clusters.value.find((c) => c.cluster_label === detailCluster.value?.cluster_label);
			if (cluster) {
				cluster.face_count = Math.max(0, cluster.face_count - 1);
			}
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function assignDetailCluster() {
	if (!detailCluster.value) return;
	const label = detailCluster.value.cluster_label;
	const payload = resolveAssignPayload(detailPersonSelect.value);
	if (!payload.person_id && !payload.new_person_name) return;

	detailAssigning.value = true;
	FaceClusterService.assignCluster(label, payload)
		.then((response) => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("people.assigned_faces", { count: String(response.data.assigned_count) }),
				life: 3000,
			});
			clusters.value = clusters.value.filter((c) => c.cluster_label !== label);
			detailDialogVisible.value = false;
			loadPeople();
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			detailAssigning.value = false;
		});
}

function dismissDetailCluster() {
	if (!detailCluster.value) return;
	const label = detailCluster.value.cluster_label;
	detailDismissing.value = true;
	FaceClusterService.dismissCluster(label)
		.then((response) => {
			toast.add({
				severity: "info",
				summary: trans("toasts.success"),
				detail: trans("people.dismissed_faces", { count: String(response.data.dismissed_count) }),
				life: 3000,
			});
			clusters.value = clusters.value.filter((c) => c.cluster_label !== label);
			detailDialogVisible.value = false;
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			detailDismissing.value = false;
		});
}

onMounted(() => {
	load();
	loadPeople();
});
</script>
