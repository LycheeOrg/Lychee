<template>
	<div class="h-svh overflow-y-auto">
		<div class="w-full border-0 h-14 flex items-center justify-between px-2">
			<GoBack @go-back="$router.push({ name: 'people' })" />
			<span class="absolute left-1/2 -translate-x-1/2 pointer-events-none">{{ $t("people.clusters_title") }}</span>
			<div></div>
		</div>

		<FaceRecognitionWarning />

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<Spinner class="text-3xl" />
		</div>

		<template v-else>
			<!-- Page body controls -->
			<div class="px-6 pt-4 pb-2 flex flex-wrap gap-2 items-center">
				<UTooltip v-if="!isBatchMode" :text="$t('people.batch_select')">
					<UButton icon="prime:check-square" color="neutral" variant="ghost" @click="startBatchMode" />
				</UTooltip>
				<template v-else>
					<UButton :label="$t('people.batch_cancel')" color="neutral" variant="ghost" @click="cancelBatchMode" />
					<UButton
						:label="$t('people.dismiss')"
						icon="prime:times"
						color="error"
						variant="ghost"
						:disabled="selectedLabels.length === 0"
						:loading="batchDismissing"
						@click="batchDismiss"
					/>
				</template>
				<UButton
					:label="$t('people.run_clustering')"
					icon="prime:refresh"
					color="neutral"
					variant="outline"
					:loading="runningClustering"
					@click="runClustering"
				/>
			</div>

			<div v-if="clusters.length === 0" class="text-muted text-center mt-20 p-4">
				{{ $t("people.no_clusters") }}
			</div>

			<div v-else class="p-6">
				<!-- Batch info bar -->
				<div v-if="isBatchMode" class="flex items-center gap-3 text-sm mb-4">
					<UCheckbox
						:model-value="selectedLabels.length === clusters.length && clusters.length > 0"
						:indeterminate="selectedLabels.length > 0 && selectedLabels.length < clusters.length"
						@update:model-value="toggleSelectAllClusters"
					/>
					<span>{{ $t("people.batch_selected", { count: String(selectedLabels.length) }) }}</span>
				</div>

				<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
					<div
						v-for="cluster in clusters"
						:key="cluster.cluster_label"
						class="relative border border-default rounded-lg p-4 flex flex-col items-start gap-3 cursor-pointer hover:border-primary transition-colors"
						:class="{ 'ring-2 ring-primary': isBatchMode && selectedLabels.includes(cluster.cluster_label) }"
						@click="isBatchMode ? toggleClusterSelection(cluster.cluster_label) : openClusterDetail(cluster)"
					>
						<!-- Batch checkbox -->
						<div v-if="isBatchMode" class="absolute top-2 right-2">
							<UCheckbox
								:model-value="selectedLabels.includes(cluster.cluster_label)"
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
								class="w-14 h-14 rounded-lg bg-elevated flex items-center justify-center text-sm text-muted"
							>
								+{{ cluster.face_count - cluster.sample_crop_urls.length }}
							</div>
						</div>

						<div class="flex flex-col gap-2 w-full" @click.stop>
							<span class="text-sm text-muted">{{ cluster.face_count }} {{ $t("people.faces") }}</span>
							<UInputMenu
								:model-value="clusterPersonModelValue(cluster)"
								:items="allPeople"
								label-key="name"
								create-item
								:placeholder="$t('people.enter_name')"
								class="w-full"
								@update:model-value="(v) => (clusterPersonSelect[cluster.cluster_label] = v ?? null)"
								@create="(name: string) => (clusterPersonSelect[cluster.cluster_label] = name)"
								@keydown.enter.stop="assignClusterWithSelection(cluster.cluster_label)"
							/>
						</div>

						<div class="flex gap-2 shrink-0" @click.stop>
							<UButton
								:label="$t('people.assign')"
								icon="prime:check"
								color="success"
								size="sm"
								:disabled="!getClusterAssignName(cluster.cluster_label)"
								:loading="assigningLabel === cluster.cluster_label"
								@click="assignClusterWithSelection(cluster.cluster_label)"
							/>
							<UButton
								:label="$t('people.dismiss')"
								icon="prime:times"
								color="error"
								variant="outline"
								size="sm"
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
		<UModal v-model:open="detailDialogVisible" :dismissible="true">
			<template #header>
				<span class="font-bold">{{ $t("people.cluster_detail_title", { count: String(detailCluster?.face_count ?? 0) }) }}</span>
			</template>
			<template #body>
				<div v-if="detailFacesLoading" class="flex justify-center py-6">
					<Spinner class="text-2xl" />
				</div>
				<div v-else class="flex flex-col gap-4">
					<div class="overflow-y-auto max-h-[60vh]">
						<div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
							<div v-for="face in detailFaces" :key="face.id" class="relative aspect-square group">
								<img v-if="face.crop_url" :src="face.crop_url" class="w-full h-full object-cover rounded-lg" loading="lazy" />
								<div v-else class="w-full h-full bg-neutral-200 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
									<UIcon name="prime:user" class="text-xl text-muted" />
								</div>
								<button
									class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
									@click="dismissSingleFace(face)"
								>
									×
								</button>
							</div>
						</div>
						<div v-if="detailHasMorePages || detailFacesLoadingMore" class="flex justify-center py-2">
							<Spinner class="text-lg" />
						</div>
						<div ref="detailScrollSentinel" />
					</div>
					<div class="flex flex-col sm:flex-row gap-3 items-end border-t border-default pt-2">
						<UInputMenu
							:model-value="detailPersonModelValue()"
							:items="allPeople"
							label-key="name"
							create-item
							:placeholder="$t('people.enter_name')"
							class="flex-1"
							@update:model-value="(v) => (detailPersonSelect = v ?? null)"
							@create="(name: string) => (detailPersonSelect = name)"
							@keydown.enter.stop="assignDetailCluster"
						/>
						<div class="flex gap-2">
							<UButton
								:label="$t('people.assign')"
								icon="prime:check"
								color="success"
								:disabled="!getDetailAssignName()"
								:loading="detailAssigning"
								@click="assignDetailCluster"
							/>
							<UButton
								:label="$t('people.dismiss')"
								icon="prime:times"
								color="error"
								variant="outline"
								:loading="detailDismissing"
								@click="dismissDetailCluster"
							/>
						</div>
					</div>
				</div>
			</template>
		</UModal>
	</div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import PaginationInfiniteScroll from "@/v8/components/pagination/PaginationInfiniteScroll.vue";
import FaceRecognitionWarning from "@/v8/components/faceRecog/FaceRecognitionWarning.vue";
import FaceClusterService from "@/services/face-cluster-service";
import FaceDetectionService from "@/services/face-detection-service";
import GoBack from "@/v8/components/headers/GoBack.vue";
import Spinner from "@/v8/components/Spinner.vue";
import { usePeopleList } from "@/composables/usePeopleList";

const toast = useAppToast();

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
const { people: allPeople, load: loadAllPeople } = usePeopleList();

// Batch selection state
const isBatchMode = ref(false);
const selectedLabels = ref<number[]>([]);
const batchDismissing = ref(false);

// Cluster detail dialog state
const detailDialogVisible = ref(false);
const detailCluster = ref<App.Http.Resources.Models.ClusterPreviewResource | null>(null);
const detailFaces = ref<App.Http.Resources.Models.FaceResource[]>([]);
const detailFacesLoading = ref(false);
const detailFacesLoadingMore = ref(false);
const detailCurrentPage = ref(1);
const detailHasMorePages = ref(false);
const detailPersonSelect = ref<App.Http.Resources.Models.PersonResource | string | null>(null);
const detailAssigning = ref(false);
const detailDismissing = ref(false);
const detailScrollSentinel = ref<HTMLElement | null>(null);
let detailScrollObserver: IntersectionObserver | null = null;

function clusterPersonModelValue(cluster: App.Http.Resources.Models.ClusterPreviewResource): App.Http.Resources.Models.PersonResource | undefined {
	return (clusterPersonSelect[cluster.cluster_label] ?? undefined) as App.Http.Resources.Models.PersonResource | undefined;
}

function detailPersonModelValue(): App.Http.Resources.Models.PersonResource | undefined {
	return (detailPersonSelect.value ?? undefined) as App.Http.Resources.Models.PersonResource | undefined;
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
			loadAllPeople().catch(() => {});
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

// -- Cluster detail dialog --

function openClusterDetail(cluster: App.Http.Resources.Models.ClusterPreviewResource) {
	detailCluster.value = cluster;
	detailFaces.value = [];
	detailPersonSelect.value = null;
	detailCurrentPage.value = 1;
	detailHasMorePages.value = false;
	detailDialogVisible.value = true;
	detailFacesLoading.value = true;
	FaceClusterService.getClusterFaces(cluster.cluster_label, 1)
		.then((response) => {
			detailFaces.value = response.data.data as unknown as App.Http.Resources.Models.FaceResource[];
			detailCurrentPage.value = response.data.current_page;
			detailHasMorePages.value = response.data.current_page < response.data.last_page;
		})
		.catch(() => {
			/* already handled elsewhere */
		})
		.finally(() => {
			detailFacesLoading.value = false;
		});
}

function loadMoreDetailFaces() {
	if (!detailCluster.value || detailFacesLoadingMore.value || !detailHasMorePages.value) return;
	detailFacesLoadingMore.value = true;
	const nextPage = detailCurrentPage.value + 1;
	FaceClusterService.getClusterFaces(detailCluster.value.cluster_label, nextPage)
		.then((response) => {
			detailFaces.value = [...detailFaces.value, ...(response.data.data as unknown as App.Http.Resources.Models.FaceResource[])];
			detailCurrentPage.value = response.data.current_page;
			detailHasMorePages.value = response.data.current_page < response.data.last_page;
		})
		.catch(() => {
			/* already handled elsewhere */
		})
		.finally(() => {
			detailFacesLoadingMore.value = false;
		});
}

function setupDetailScrollObserver() {
	detailScrollObserver?.disconnect();
	detailScrollObserver = new IntersectionObserver(
		(entries) => {
			if (entries[0]?.isIntersecting) {
				loadMoreDetailFaces();
			}
		},
		{ threshold: 0.1 },
	);
	if (detailScrollSentinel.value) {
		detailScrollObserver.observe(detailScrollSentinel.value);
	}
}

watch(detailScrollSentinel, (el) => {
	if (el) {
		setupDetailScrollObserver();
	}
});

watch(detailDialogVisible, (visible) => {
	if (!visible) {
		detailScrollObserver?.disconnect();
		detailScrollObserver = null;
	}
});

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
			loadAllPeople().catch(() => {});
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
	loadAllPeople().catch(() => {});
});
</script>
