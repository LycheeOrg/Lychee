<template>
	<div class="h-svh overflow-y-auto">
		<UHeader :toggle="false">
			<template #left>
				<GoBack @go-back="$router.push({ name: 'people' })" />
			</template>
			{{ $t("people.clusters_title") }}
		</UHeader>

		<FaceRecognitionWarning />

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<LycheeLoadingIcon fast class="text-3xl" />
		</div>

		<template v-else>
			<!-- Page body controls -->
			<div class="px-6 pt-4 pb-2 flex flex-wrap gap-2 items-center">
				<UTooltip v-if="!isBatchMode" :text="$t('people.batch_select')">
					<UButton icon="lucide:check-square" color="neutral" variant="ghost" @click="startBatchMode" />
				</UTooltip>
				<template v-else>
					<UButton :label="$t('people.batch_cancel')" color="neutral" variant="ghost" @click="cancelBatchMode" />
					<UButton
						:label="$t('people.dismiss')"
						icon="lucide:x"
						color="error"
						variant="ghost"
						:disabled="selectedLabels.length === 0"
						@click="requestBatchDismiss"
					/>
				</template>
				<UButton
					:label="$t('people.run_clustering')"
					icon="lucide:refresh-cw"
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
						@click="isBatchMode ? toggleClusterSelection(cluster.cluster_label) : openQueue(cluster)"
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
								@update:model-value="
									(v: App.Http.Resources.Models.PersonResource | string | undefined) =>
										(clusterPersonSelect[cluster.cluster_label] = v ?? null)
								"
								@create="(name: string) => (clusterPersonSelect[cluster.cluster_label] = name)"
								@keydown.enter.stop="assignClusterWithSelection(cluster.cluster_label)"
							/>
						</div>

						<div class="flex gap-2 shrink-0" @click.stop>
							<UButton
								variant="solid"
								:label="$t('people.assign')"
								icon="lucide:check"
								color="success"
								size="sm"
								:disabled="!getClusterAssignName(cluster.cluster_label)"
								:loading="assigningLabel === cluster.cluster_label"
								@click="assignClusterWithSelection(cluster.cluster_label)"
							/>
							<UButton
								:label="$t('people.dismiss')"
								icon="lucide:x"
								color="error"
								variant="outline"
								size="sm"
								@click="requestDismissCluster(cluster)"
							/>
						</div>
					</div>
				</div>
			</div>

			<PaginationInfiniteScroll :loading="loadingMore" :hasMore="hasMorePages" @loadMore="loadMore" />
		</template>

		<!-- Cluster review queue: one cluster at a time, contact-sheet strip, auto-advances on decision -->
		<UModal v-model:open="queueVisible" :dismissible="true">
			<template #header>
				<div class="flex items-center justify-between w-full gap-2">
					<span class="font-bold">
						{{ $t("people.review_queue_position", { current: String((queueIndex ?? 0) + 1), total: String(totalClusters) }) }}
					</span>
					<UTooltip :text="$t('people.skip')">
						<UButton icon="lucide:skip-forward" color="neutral" variant="ghost" size="sm" @click="skipQueueCluster" />
					</UTooltip>
				</div>
			</template>
			<template #body>
				<div v-if="detailFacesLoading" class="flex justify-center py-6">
					<LycheeLoadingIcon fast class="text-2xl" />
				</div>
				<div v-else class="flex flex-col gap-4">
					<div ref="detailScrollContainer" class="overflow-x-auto">
						<div class="flex gap-2 pb-2">
							<div v-for="face in detailFaces" :key="face.id" class="relative shrink-0 w-24 h-24 group">
								<img v-if="face.crop_url" :src="face.crop_url" class="w-full h-full object-cover rounded-lg" loading="lazy" />
								<div v-else class="w-full h-full bg-elevated rounded-lg flex items-center justify-center">
									<UIcon name="lucide:user" class="text-xl text-muted" />
								</div>
								<button
									class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-inverted text-xs flex items-center justify-center opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity hover:bg-red-600"
									:title="$t('people.remove_from_person')"
									@click="dismissSingleFace(face)"
								>
									×
								</button>
							</div>
							<div v-if="detailHasMorePages || detailFacesLoadingMore" class="flex items-center justify-center w-24 h-24 shrink-0">
								<LycheeLoadingIcon fast class="text-lg" />
							</div>
							<div ref="detailScrollSentinel" class="shrink-0 w-1" />
						</div>
					</div>
					<div class="flex flex-col sm:flex-row gap-3 items-end border-t border-default pt-2">
						<UInputMenu
							:model-value="detailPersonModelValue()"
							:items="allPeople"
							label-key="name"
							create-item
							:placeholder="$t('people.enter_name')"
							class="flex-1"
							@update:model-value="
								(v: App.Http.Resources.Models.PersonResource | string | undefined) => (detailPersonSelect = v ?? null)
							"
							@create="(name: string) => (detailPersonSelect = name)"
							@keydown.enter.stop="assignQueueCluster"
						/>
						<div class="flex gap-2">
							<UButton
								variant="solid"
								:label="$t('people.assign')"
								icon="lucide:check"
								color="success"
								:disabled="!getDetailAssignName()"
								:loading="detailAssigning"
								@click="assignQueueCluster"
							/>
							<UButton
								:label="$t('people.dismiss')"
								icon="lucide:x"
								color="error"
								variant="outline"
								@click="queueCluster && requestDismissCluster(queueCluster)"
							/>
						</div>
					</div>
				</div>
			</template>
		</UModal>

		<!-- Dismiss confirmation dialog (single cluster or batch) -->
		<UModal v-model:open="confirmDialogVisible" :dismissible="true">
			<template #body>
				<p class="text-center text-highlighted max-w-xl text-wrap">
					<template v-if="pendingDismiss?.kind === 'cluster'">
						{{ $t("people.dismiss_cluster_confirm", { count: String(pendingDismiss.cluster.face_count) }) }}
					</template>
					<template v-else-if="pendingDismiss?.kind === 'batch'">
						{{ $t("people.dismiss_batch_confirm", { count: String(pendingDismiss.labels.length) }) }}
					</template>
					<br /><br />
					<span class="text-muted flex items-center justify-center gap-1">
						<UIcon name="lucide:triangle-alert" class="text-warning" />{{ $t("people.dismiss_warning") }}
					</span>
				</p>
			</template>
			<template #footer>
				<div class="flex w-full gap-2">
					<UButton
						color="neutral"
						variant="soft"
						class="flex-1 justify-center font-bold"
						:disabled="confirmingDismiss"
						@click="cancelDismissConfirm"
					>
						{{ $t("dialogs.button.cancel") }}
					</UButton>
					<UButton
						variant="solid"
						color="error"
						class="flex-1 justify-center font-bold"
						:loading="confirmingDismiss"
						@click="confirmDismiss"
					>
						{{ $t("people.dismiss") }}
					</UButton>
				</div>
			</template>
		</UModal>
	</div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useToast } from "@nuxt/ui/composables/useToast";
import { trans } from "laravel-vue-i18n";
import PaginationInfiniteScroll from "@/v8/components/pagination/PaginationInfiniteScroll.vue";
import FaceRecognitionWarning from "@/v8/components/faceRecog/FaceRecognitionWarning.vue";
import FaceClusterService from "@/services/face-cluster-service";
import FaceDetectionService from "@/services/face-detection-service";
import GoBack from "@/v8/components/headers/GoBack.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import { usePeopleList } from "@/composables/usePeopleList";

const toast = useAppToast();
const rawToast = useToast();

const clusters = ref<App.Http.Resources.Models.ClusterPreviewResource[]>([]);
// Per-cluster selected person (string name or PersonResource)
const clusterPersonSelect = reactive<Record<number, App.Http.Resources.Models.PersonResource | string | null>>({});
const loading = ref(false);
const loadingMore = ref(false);
const runningClustering = ref(false);
const assigningLabel = ref<number | null>(null);
const currentPage = ref(1);
const hasMorePages = ref(false);
const totalClusters = ref(0);

// All known persons for autocomplete
const { people: allPeople, load: loadAllPeople } = usePeopleList();

// Batch selection state
const isBatchMode = ref(false);
const selectedLabels = ref<number[]>([]);

// Dismiss confirmation (single cluster or batch)
type PendingDismiss = { kind: "cluster"; cluster: App.Http.Resources.Models.ClusterPreviewResource } | { kind: "batch"; labels: number[] };
const confirmDialogVisible = ref(false);
const pendingDismiss = ref<PendingDismiss | null>(null);
const confirmingDismiss = ref(false);

// Cluster review queue: steps through `clusters.value` starting at the clicked cluster.
// Deciding (assign/dismiss) removes the cluster from `clusters.value`, which naturally shifts
// the next cluster into the same index - no manual index bump needed on that path.
const queueVisible = ref(false);
const queueIndex = ref<number | null>(null);
const queueCluster = computed<App.Http.Resources.Models.ClusterPreviewResource | null>(() =>
	queueIndex.value !== null ? (clusters.value[queueIndex.value] ?? null) : null,
);

const detailFaces = ref<App.Http.Resources.Models.FaceResource[]>([]);
const detailFacesLoading = ref(false);
const detailFacesLoadingMore = ref(false);
const detailCurrentPage = ref(1);
const detailHasMorePages = ref(false);
const detailPersonSelect = ref<App.Http.Resources.Models.PersonResource | string | null>(null);
const detailAssigning = ref(false);
const detailScrollContainer = ref<HTMLElement | null>(null);
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

function requestDismissCluster(cluster: App.Http.Resources.Models.ClusterPreviewResource) {
	pendingDismiss.value = { kind: "cluster", cluster };
	confirmDialogVisible.value = true;
}

function requestBatchDismiss() {
	if (selectedLabels.value.length === 0) return;
	pendingDismiss.value = { kind: "batch", labels: [...selectedLabels.value] };
	confirmDialogVisible.value = true;
}

function cancelDismissConfirm() {
	confirmDialogVisible.value = false;
	pendingDismiss.value = null;
}

function confirmDismiss() {
	if (!pendingDismiss.value) return;
	const pending = pendingDismiss.value;
	confirmingDismiss.value = true;
	const action = pending.kind === "cluster" ? performDismissCluster(pending.cluster) : performBatchDismiss(pending.labels);
	action.finally(() => {
		confirmingDismiss.value = false;
		confirmDialogVisible.value = false;
		pendingDismiss.value = null;
	});
}

// Fetch every face id currently in a cluster (before dismissing it) so a later "Undo" can un-dismiss exactly those faces.
// The faces() endpoint excludes already-dismissed faces, so this must run before the dismiss call, not after.
function fetchAllClusterFaceIds(label: number, expectedCount: number): Promise<string[]> {
	const MAX_PAGES = 200; // safety cap (10,000 faces @ 50/page); real clusters are far smaller

	function fetchPage(page: number, ids: string[]): Promise<string[]> {
		if (ids.length >= expectedCount || page > MAX_PAGES) {
			return Promise.resolve(ids);
		}
		return FaceClusterService.getClusterFaces(label, page).then((response) => {
			const items = response.data.data as unknown as App.Http.Resources.Models.FaceResource[];
			if (items.length === 0) {
				return ids;
			}
			const nextIds = [...ids, ...items.map((f) => f.id)];
			if (response.data.current_page >= response.data.last_page) {
				return nextIds;
			}
			return fetchPage(page + 1, nextIds);
		});
	}

	return fetchPage(1, []);
}

interface DismissedEntry {
	cluster: App.Http.Resources.Models.ClusterPreviewResource;
	faceIds: string[];
}

function showDismissedToast(entries: DismissedEntry[], dismissedCount: number) {
	rawToast.add({
		color: "info",
		title: trans("toasts.success"),
		description: trans("people.dismissed_faces", { count: String(dismissedCount) }),
		duration: 8000,
		actions: [
			{
				label: trans("people.undo"),
				color: "neutral",
				variant: "outline",
				onClick: () => undoDismiss(entries),
			},
		],
	});
}

function undoDismiss(entries: DismissedEntry[]) {
	const allFaceIds = entries.flatMap((e) => e.faceIds);
	const activeLabel = queueCluster.value?.cluster_label;
	Promise.all(allFaceIds.map((id) => FaceDetectionService.toggleDismissed(id)))
		.then(() => {
			let restoredCount = 0;
			entries.forEach(({ cluster }) => {
				if (!clusters.value.some((c) => c.cluster_label === cluster.cluster_label)) {
					clusters.value = [cluster, ...clusters.value];
					restoredCount += 1;
				}
			});
			totalClusters.value += restoredCount;
			// Prepending restored clusters shifts every existing index by `restoredCount`, so
			// re-anchor the queue to the same cluster label rather than leaving `queueIndex` pointing
			// at whatever cluster now happens to sit at that position.
			if (activeLabel !== undefined) {
				const newIndex = clusters.value.findIndex((c) => c.cluster_label === activeLabel);
				if (newIndex !== -1) {
					queueIndex.value = newIndex;
				}
			}
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("people.restored_faces", { count: String(allFaceIds.length) }),
				life: 3000,
			});
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function performDismissCluster(cluster: App.Http.Resources.Models.ClusterPreviewResource): Promise<void> {
	const label = cluster.cluster_label;
	return fetchAllClusterFaceIds(label, cluster.face_count)
		.then((faceIds) =>
			FaceClusterService.dismissCluster(label).then((response) => {
				const wasQueueCluster = queueCluster.value?.cluster_label === label;
				clusters.value = clusters.value.filter((c) => c.cluster_label !== label);
				delete clusterPersonSelect[label];
				totalClusters.value = Math.max(0, totalClusters.value - 1);
				if (wasQueueCluster) {
					advanceQueueAfterRemoval();
				}
				showDismissedToast([{ cluster, faceIds }], response.data.dismissed_count);
			}),
		)
		.catch((e: { response?: { data?: { message?: string } } }) => {
			console.error("Error dismissing face cluster:", e);
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function performBatchDismiss(labels: number[]): Promise<void> {
	const targets = clusters.value.filter((c) => labels.includes(c.cluster_label));
	return Promise.all(
		targets.map((cluster) => fetchAllClusterFaceIds(cluster.cluster_label, cluster.face_count).then((faceIds) => ({ cluster, faceIds }))),
	)
		.then((prepared) =>
			Promise.all(prepared.map((p) => FaceClusterService.dismissCluster(p.cluster.cluster_label))).then((responses) => {
				const totalDismissed = responses.reduce((sum, r) => sum + r.data.dismissed_count, 0);
				clusters.value = clusters.value.filter((c) => !labels.includes(c.cluster_label));
				labels.forEach((l) => delete clusterPersonSelect[l]);
				totalClusters.value = Math.max(0, totalClusters.value - labels.length);
				selectedLabels.value = [];
				isBatchMode.value = false;
				showDismissedToast(prepared, totalDismissed);
			}),
		)
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
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
			totalClusters.value = response.data.total;
		})
		.catch((e) => {
			console.error("Error loading face clusters:", e);
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

function loadMore(): Promise<void> {
	loadingMore.value = true;
	const nextPage = currentPage.value + 1;
	return FaceClusterService.getClusters(nextPage)
		.then((response) => {
			const items = response.data.data;
			const newItems = Array.isArray(items) ? items : (Object.values(items) as App.Http.Resources.Models.ClusterPreviewResource[]);
			clusters.value = [...clusters.value, ...newItems];
			currentPage.value = nextPage;
			hasMorePages.value = response.data.current_page < response.data.last_page;
			totalClusters.value = response.data.total;
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
			totalClusters.value = Math.max(0, totalClusters.value - 1);
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

// -- Cluster review queue --

function openQueue(cluster: App.Http.Resources.Models.ClusterPreviewResource) {
	const idx = clusters.value.findIndex((c) => c.cluster_label === cluster.cluster_label);
	if (idx === -1) return;
	queueIndex.value = idx;
	queueVisible.value = true;
	loadQueueClusterFaces();
}

function loadQueueClusterFaces() {
	const cluster = queueCluster.value;
	if (!cluster) return;
	detailFaces.value = [];
	detailPersonSelect.value = null;
	detailCurrentPage.value = 1;
	detailHasMorePages.value = false;
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

// Deciding a cluster removes it from `clusters.value`, which shifts the next cluster into the
// same `queueIndex` - so we reload at the same index rather than incrementing it. Only advance
// past the end (or fetch another page) when the queue has actually run out at that index.
function advanceQueueAfterRemoval() {
	if (queueIndex.value === null) return;
	if (queueIndex.value < clusters.value.length) {
		loadQueueClusterFaces();
		return;
	}
	if (hasMorePages.value) {
		loadMore().then(() => {
			if (queueIndex.value !== null && queueIndex.value < clusters.value.length) {
				loadQueueClusterFaces();
			} else {
				closeQueue();
			}
		});
	} else {
		closeQueue();
	}
}

// Skip moves the queue forward without deciding anything - unlike assign/dismiss, nothing is
// removed from `clusters.value`, so the index itself has to advance.
function skipQueueCluster() {
	if (queueIndex.value === null) return;
	const nextIndex = queueIndex.value + 1;
	if (nextIndex < clusters.value.length) {
		queueIndex.value = nextIndex;
		return;
	}
	if (hasMorePages.value) {
		loadMore().then(() => {
			if (nextIndex < clusters.value.length) {
				queueIndex.value = nextIndex;
			} else {
				closeQueue();
			}
		});
	} else {
		closeQueue();
	}
}

function closeQueue() {
	queueVisible.value = false;
}

function loadMoreDetailFaces() {
	if (!queueCluster.value || detailFacesLoadingMore.value || !detailHasMorePages.value) return;
	detailFacesLoadingMore.value = true;
	const nextPage = detailCurrentPage.value + 1;
	FaceClusterService.getClusterFaces(queueCluster.value.cluster_label, nextPage)
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
	// Root against the horizontal strip itself, not the viewport - the sentinel is scrolled
	// sideways within `detailScrollContainer`, so intersection has to be measured against that.
	detailScrollObserver = new IntersectionObserver(
		(entries) => {
			if (entries[0]?.isIntersecting) {
				loadMoreDetailFaces();
			}
		},
		{ root: detailScrollContainer.value, threshold: 0.1 },
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

watch(queueIndex, (idx) => {
	if (idx !== null && queueVisible.value) {
		loadQueueClusterFaces();
	}
});

watch(queueVisible, (visible) => {
	if (!visible) {
		queueIndex.value = null;
		detailScrollObserver?.disconnect();
		detailScrollObserver = null;
	}
});

// Keybindings, active only while the review queue is open and no confirm dialog is blocking it.
defineShortcuts({
	d: () => queueVisible.value && !confirmDialogVisible.value && !!queueCluster.value && requestDismissCluster(queueCluster.value),
	arrowright: () => queueVisible.value && !confirmDialogVisible.value && skipQueueCluster(),
	" ": () => queueVisible.value && !confirmDialogVisible.value && skipQueueCluster(),
});

function undoSingleFace(face: App.Http.Resources.Models.FaceResource, clusterLabel: number) {
	FaceDetectionService.toggleDismissed(face.id)
		.then(() => {
			if (queueCluster.value?.cluster_label === clusterLabel && !detailFaces.value.some((f) => f.id === face.id)) {
				detailFaces.value = [face, ...detailFaces.value];
			}
			const cluster = clusters.value.find((c) => c.cluster_label === clusterLabel);
			if (cluster) {
				cluster.face_count += 1;
			}
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function dismissSingleFace(face: App.Http.Resources.Models.FaceResource) {
	const clusterLabel = queueCluster.value?.cluster_label;
	FaceDetectionService.toggleDismissed(face.id)
		.then(() => {
			detailFaces.value = detailFaces.value.filter((f) => f.id !== face.id);
			const cluster = clusters.value.find((c) => c.cluster_label === clusterLabel);
			if (cluster) {
				cluster.face_count = Math.max(0, cluster.face_count - 1);
			}
			rawToast.add({
				color: "info",
				title: trans("toasts.success"),
				description: trans("people.assignment.dismissed"),
				duration: 6000,
				actions:
					clusterLabel !== undefined
						? [
								{
									label: trans("people.undismiss_face"),
									color: "neutral",
									variant: "outline",
									onClick: () => undoSingleFace(face, clusterLabel),
								},
							]
						: [],
			});
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function assignQueueCluster() {
	if (!queueCluster.value) return;
	const cluster = queueCluster.value;
	const label = cluster.cluster_label;
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
			totalClusters.value = Math.max(0, totalClusters.value - 1);
			loadAllPeople().catch(() => {});
			advanceQueueAfterRemoval();
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			detailAssigning.value = false;
		});
}

onMounted(() => {
	load();
	loadAllPeople().catch(() => {});
});
</script>
