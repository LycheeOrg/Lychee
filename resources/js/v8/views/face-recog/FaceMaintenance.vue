<template>
	<div class="h-svh overflow-y-auto">
		<div class="w-full border-0 h-14 flex items-center justify-between px-2 sticky top-0 z-10 bg-default">
			<OpenLeftMenu />
			<span class="absolute left-1/2 -translate-x-1/2 pointer-events-none">{{ $t("maintenance.face_quality.title") }}</span>
			<div></div>
		</div>

		<FaceRecognitionWarning />

		<div v-if="loading && faces.length === 0" class="flex justify-center py-12">
			<Spinner class="text-3xl" />
		</div>

		<template v-else>
			<UCard class="max-w-6xl mx-auto px-4 pt-4">
				<p class="text-muted mb-4 text-center text-sm">
					{{ $t("maintenance.face_quality.description") }}
				</p>

				<!-- Controls -->
				<div class="flex flex-wrap gap-3 mb-4 items-center">
					<UButton
						:label="dismissedOnly ? $t('maintenance.face_quality.show_active') : $t('maintenance.face_quality.show_dismissed')"
						:color="dismissedOnly ? 'primary' : 'neutral'"
						size="sm"
						:trailing="true"
						:icon="dismissedOnly ? 'prime:eye' : 'prime:eye-slash'"
						@click="toggleDismissedOnly"
					/>
					<UButton
						:label="$t('maintenance.face_quality.show_unassigned')"
						:color="unassignedOnly ? 'primary' : 'neutral'"
						size="sm"
						:trailing="true"
						:icon="unassignedOnly ? 'prime:user-minus' : 'prime:users'"
						@click="toggleUnassignedOnly"
					/>
					<UButton
						v-if="faces.length > 0"
						:label="allSelected ? $t('maintenance.face_quality.deselect_all') : $t('maintenance.face_quality.select_all')"
						color="neutral"
						variant="ghost"
						size="sm"
						@click="toggleSelectAll"
					/>
					<template v-if="selectedIds.length > 0">
						<span class="text-sm text-muted">
							{{ $t("maintenance.face_quality.selected_count", { count: String(selectedIds.length) }) }}
						</span>
						<UButton
							:label="$t('people.batch_assign')"
							icon="prime:user-plus"
							color="success"
							size="sm"
							@click="showBatchAssignModal = true"
						/>
						<UButton
							v-if="!dismissedOnly"
							:label="$t('maintenance.face_quality.batch_dismiss')"
							icon="prime:times"
							color="error"
							size="sm"
							:loading="batchDismissing"
							@click="batchDismiss"
						/>
						<UButton
							v-else
							:label="$t('maintenance.face_quality.batch_reactivate')"
							icon="prime:undo"
							color="success"
							size="sm"
							:loading="batchReactivating"
							@click="batchReactivate"
						/>
					</template>
				</div>

				<!-- Empty state -->
				<div v-if="faces.length === 0" class="text-center py-12 text-muted">
					<UIcon name="prime:check-circle" class="text-4xl mb-4 block mx-auto" />
					{{ $t("maintenance.face_quality.no_faces") }}
				</div>

				<!-- Face rows -->
				<div v-else class="flex flex-col divide-y divide-default">
					<!-- Header row -->
					<div
						class="hidden sm:grid grid-cols-[2.5rem_3rem_1fr_1fr_5rem_5rem_5rem] gap-3 items-center px-2 py-1 text-xs text-muted font-medium"
					>
						<div></div>
						<div>{{ $t("maintenance.face_quality.col_face") }}</div>
						<div>{{ $t("maintenance.face_quality.col_person") }}</div>
						<div>{{ $t("maintenance.face_quality.col_cluster") }}</div>
						<div
							class="text-right cursor-pointer hover:text-primary-500 transition-colors flex items-center justify-end gap-1"
							@click="setSort('confidence')"
						>
							{{ $t("maintenance.face_quality.col_confidence") }}
							<UIcon v-if="sortBy === 'confidence'" :name="sortDir === 'ASC' ? 'prime:arrow-up' : 'prime:arrow-down'" class="text-xs" />
						</div>
						<div
							class="text-right cursor-pointer hover:text-primary-500 transition-colors flex items-center justify-end gap-1"
							@click="setSort('laplacian_variance')"
						>
							{{ $t("maintenance.face_quality.col_blur") }}
							<UIcon
								v-if="sortBy === 'laplacian_variance'"
								:name="sortDir === 'ASC' ? 'prime:arrow-up' : 'prime:arrow-down'"
								class="text-xs"
							/>
						</div>
						<div></div>
					</div>

					<div
						v-for="(face, idx) in faces"
						:key="face.id"
						class="grid grid-cols-[2.5rem_3rem_1fr_5rem] sm:grid-cols-[2.5rem_3rem_1fr_1fr_5rem_5rem_5rem] gap-3 items-center px-2 py-2 cursor-pointer select-none hover:bg-elevated/50 transition-colors"
						:class="{ 'bg-primary-50 dark:bg-primary-900/20': selectedIds.includes(face.id) }"
						@click="toggleSelection(face.id, idx, $event)"
					>
						<!-- Checkbox -->
						<div class="flex justify-center" @click.stop>
							<UCheckbox :model-value="selectedIds.includes(face.id)" @click.stop="toggleSelection(face.id, idx, $event)" />
						</div>

						<!-- Face image -->
						<div
							class="w-10 h-10 rounded-md overflow-hidden bg-neutral-200 dark:bg-neutral-700 shrink-0 cursor-pointer hover:ring-2 hover:ring-primary-400 transition-all"
							@click.stop="openPhotoViewer(face)"
						>
							<img v-if="face.crop_url" :src="face.crop_url" alt="" class="w-full h-full object-cover" loading="lazy" />
							<div v-else class="w-full h-full flex items-center justify-center">
								<UIcon name="prime:user" class="text-muted" />
							</div>
						</div>

						<!-- Person -->
						<div class="min-w-0">
							<span v-if="face.person_name" class="font-medium truncate block">{{ face.person_name }}</span>
							<span v-else class="text-muted italic text-sm">{{ $t("maintenance.face_quality.unassigned") }}</span>
							<!-- Cluster: shown here on mobile -->
							<UBadge v-if="face.cluster_label !== null" color="neutral" class="sm:hidden mt-0.5 text-xs py-0 px-1">
								#{{ face.cluster_label }}
							</UBadge>
						</div>

						<!-- Cluster (hidden on mobile, shown in own column on sm+) -->
						<div class="hidden sm:block">
							<UBadge v-if="face.cluster_label !== null" color="neutral" class="text-xs py-0 px-1"> #{{ face.cluster_label }} </UBadge>
							<span v-else class="text-muted">—</span>
						</div>

						<!-- Confidence -->
						<div class="hidden sm:block text-right text-sm">
							<span :class="face.confidence < 0.5 ? 'text-red-500' : 'text-muted'">{{ face.confidence.toFixed(2) }}</span>
						</div>

						<!-- Blur -->
						<div class="hidden sm:block text-right text-sm">
							<span :class="face.laplacian_variance < 50 ? 'text-red-500' : 'text-muted'">{{
								face.laplacian_variance.toFixed(0)
							}}</span>
						</div>

						<!-- Actions: Assign & Dismiss -->
						<div class="flex justify-center gap-1" @click.stop>
							<UTooltip :text="$t('people.assign_face')">
								<UButton icon="prime:user-plus" color="neutral" variant="ghost" size="sm" @click="openAssignmentModal(face)" />
							</UTooltip>
							<UTooltip :text="$t('maintenance.face_quality.dismiss')">
								<UButton
									v-if="!dismissedOnly"
									icon="prime:times"
									color="error"
									variant="ghost"
									size="sm"
									:loading="dismissingId === face.id"
									@click="dismissFace(face.id)"
								/>
							</UTooltip>
							<UTooltip :text="$t('maintenance.face_quality.readd')">
								<UButton
									v-if="dismissedOnly"
									icon="prime:undo"
									color="success"
									variant="ghost"
									size="sm"
									:loading="dismissingId === face.id"
									@click="readdFace(face.id)"
								/>
							</UTooltip>
						</div>
					</div>
				</div>

				<!-- Infinite scroll sentinel -->
				<PaginationInfiniteScroll :loading="loadingMore" :hasMore="hasMorePages" @loadMore="loadMore" />
			</UCard>
		</template>

		<!-- Face Assignment Modal -->
		<FaceAssignmentModal
			v-if="assigningFace"
			v-model:open="showAssignmentModal"
			:face="assigningFace"
			@assigned="onFaceAssigned"
			@dismissed="onFaceDismissed"
		/>

		<!-- Batch Face Assignment Modal -->
		<BatchFaceAssignmentModal v-model:visible="showBatchAssignModal" :face-ids="selectedIds" @assigned="onBatchAssigned" />

		<!-- Photo Viewer Dialog -->
		<UModal v-model:open="showPhotoViewer" :dismissible="true">
			<template #header>
				<span class="font-bold">{{ viewingPhoto?.title || "Photo" }}</span>
			</template>
			<template #body>
				<div v-if="loadingPhoto" class="flex justify-center py-12">
					<Spinner class="text-3xl" />
				</div>
				<div v-else-if="viewingPhoto" class="flex flex-col gap-4">
					<img
						v-if="viewingPhoto.size_variants.medium?.url || viewingPhoto.size_variants.small?.url"
						:src="viewingPhoto.size_variants.medium?.url || viewingPhoto.size_variants.small?.url || ''"
						:alt="viewingPhoto.title"
						class="w-full h-auto rounded-lg"
					/>
					<div class="grid grid-cols-2 gap-2 text-sm">
						<div class="text-muted">Taken:</div>
						<div>{{ viewingPhoto.taken_at || "Unknown" }}</div>
						<div class="text-muted">Dimensions:</div>
						<div>{{ viewingPhoto.size_variants.original?.width || "?" }} x {{ viewingPhoto.size_variants.original?.height || "?" }}</div>
						<div v-if="viewingPhoto.description" class="col-span-2 pt-2 border-t border-default">
							<div class="text-muted mb-1">Description:</div>
							<div>{{ viewingPhoto.description }}</div>
						</div>
					</div>
				</div>
			</template>
		</UModal>
	</div>
</template>
<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import PaginationInfiniteScroll from "@/v8/components/pagination/PaginationInfiniteScroll.vue";
import FaceRecognitionWarning from "@/v8/components/faceRecog/FaceRecognitionWarning.vue";
import FaceAssignmentModal from "@/v8/components/modals/faceRecog/FaceAssignmentModal.vue";
import BatchFaceAssignmentModal from "@/v8/components/modals/faceRecog/BatchFaceAssignmentModal.vue";
import FaceMaintenanceService from "@/services/face-maintenance-service";
import FaceDetectionService from "@/services/face-detection-service";
import ModerationService from "@/services/moderation-service";
import Spinner from "@/v8/components/Spinner.vue";

const faces = ref<App.Http.Resources.Models.FaceResource[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const hasMorePages = ref(false);
const currentPage = ref(1);
const dismissingId = ref<string | null>(null);
const batchDismissing = ref(false);
const batchReactivating = ref(false);
const sortBy = ref<"confidence" | "laplacian_variance">("confidence");
const sortDir = ref<"ASC" | "DESC">("ASC");
const dismissedOnly = ref(false);
const unassignedOnly = ref(false);

// Selection state
const selectedIds = ref<string[]>([]);
const lastSelectedIndex = ref<number>(-1);

// Assignment modal state
const showAssignmentModal = ref(false);
const assigningFace = ref<App.Http.Resources.Models.FaceResource | null>(null);

// Batch assignment modal state
const showBatchAssignModal = ref(false);

// Photo viewer state
const showPhotoViewer = ref(false);
const viewingPhoto = ref<App.Http.Resources.Models.PhotoResource | null>(null);
const loadingPhoto = ref(false);

const allSelected = computed(() => faces.value.length > 0 && selectedIds.value.length === faces.value.length);

const toast = useAppToast();

function load(page = 1): void {
	if (page === 1) {
		loading.value = true;
		faces.value = [];
		selectedIds.value = [];
		lastSelectedIndex.value = -1;
	} else {
		loadingMore.value = true;
	}

	FaceMaintenanceService.getFaces({
		sort_by: sortBy.value,
		sort_dir: sortDir.value,
		dismissed_only: dismissedOnly.value,
		unassigned_only: unassignedOnly.value,
		page,
		per_page: 50,
	})
		.then((response) => {
			const incoming = response.data.data;
			if (page === 1) {
				faces.value = incoming;
			} else {
				faces.value = [...faces.value, ...incoming];
			}
			currentPage.value = response.data.current_page;
			hasMorePages.value = response.data.current_page < response.data.last_page;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("maintenance.face_quality.load_error"), life: 3000 });
		})
		.finally(() => {
			loading.value = false;
			loadingMore.value = false;
		});
}

function loadMore(): void {
	load(currentPage.value + 1);
}

function setSort(field: "confidence" | "laplacian_variance"): void {
	if (sortBy.value === field) {
		sortDir.value = sortDir.value === "ASC" ? "DESC" : "ASC";
	} else {
		sortBy.value = field;
		sortDir.value = "ASC";
	}
	load(1);
}

function toggleDismissedOnly(): void {
	dismissedOnly.value = !dismissedOnly.value;
	load(1);
}

function toggleUnassignedOnly(): void {
	unassignedOnly.value = !unassignedOnly.value;
	load(1);
}

function toggleSelection(id: string, idx: number, event: MouseEvent): void {
	if (event.shiftKey && lastSelectedIndex.value >= 0) {
		const from = Math.min(lastSelectedIndex.value, idx);
		const to = Math.max(lastSelectedIndex.value, idx);
		const rangeIds = faces.value.slice(from, to + 1).map((f) => f.id);
		const allRangeSelected = rangeIds.every((rid) => selectedIds.value.includes(rid));
		if (allRangeSelected) {
			selectedIds.value = selectedIds.value.filter((sid) => !rangeIds.includes(sid));
		} else {
			const toAdd = rangeIds.filter((rid) => !selectedIds.value.includes(rid));
			selectedIds.value = [...selectedIds.value, ...toAdd];
		}
	} else {
		const existing = selectedIds.value.indexOf(id);
		if (existing === -1) {
			selectedIds.value.push(id);
		} else {
			selectedIds.value.splice(existing, 1);
		}
		lastSelectedIndex.value = idx;
	}
}

function toggleSelectAll(): void {
	if (allSelected.value) {
		selectedIds.value = [];
	} else {
		selectedIds.value = faces.value.map((f) => f.id);
	}
}

function handleSelectAllShortcut(event: KeyboardEvent): void {
	if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "a") {
		event.preventDefault();
		if (faces.value.length > 0) {
			toggleSelectAll();
		}
	}
}

function dismissFace(id: string): void {
	dismissingId.value = id;
	FaceDetectionService.toggleDismissed(id)
		.then(() => {
			faces.value = faces.value.filter((f) => f.id !== id);
			selectedIds.value = selectedIds.value.filter((sid) => sid !== id);
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("maintenance.face_quality.dismissed"), life: 2000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("maintenance.face_quality.dismiss_error"), life: 3000 });
		})
		.finally(() => {
			dismissingId.value = null;
		});
}

function readdFace(id: string): void {
	dismissingId.value = id;
	FaceDetectionService.toggleDismissed(id)
		.then(() => {
			faces.value = faces.value.filter((f) => f.id !== id);
			selectedIds.value = selectedIds.value.filter((sid) => sid !== id);
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("maintenance.face_quality.readded"), life: 2000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("maintenance.face_quality.readd_error"), life: 3000 });
		})
		.finally(() => {
			dismissingId.value = null;
		});
}

function batchDismiss(): void {
	if (selectedIds.value.length === 0) return;
	batchDismissing.value = true;
	const ids = [...selectedIds.value];
	FaceMaintenanceService.batchDismiss(ids)
		.then((response) => {
			faces.value = faces.value.filter((f) => !ids.includes(f.id));
			selectedIds.value = [];
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("maintenance.face_quality.batch_dismissed", { count: String(response.data.dismissed_count) }),
				life: 2500,
			});
		})
		.catch(() => {
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: trans("maintenance.face_quality.batch_dismiss_error"),
				life: 3000,
			});
		})
		.finally(() => {
			batchDismissing.value = false;
		});
}

function batchReactivate(): void {
	if (selectedIds.value.length === 0) return;
	batchReactivating.value = true;
	const ids = [...selectedIds.value];

	Promise.all(ids.map((id) => FaceDetectionService.toggleDismissed(id)))
		.then(() => {
			faces.value = faces.value.filter((f) => !ids.includes(f.id));
			selectedIds.value = [];
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("maintenance.face_quality.batch_reactivated", { count: String(ids.length) }),
				life: 2500,
			});
		})
		.catch(() => {
			toast.add({
				severity: "error",
				summary: trans("toasts.error"),
				detail: trans("maintenance.face_quality.batch_reactivate_error"),
				life: 3000,
			});
		})
		.finally(() => {
			batchReactivating.value = false;
		});
}

function openAssignmentModal(face: App.Http.Resources.Models.FaceResource): void {
	assigningFace.value = face;
	showAssignmentModal.value = true;
}

function onFaceAssigned(updatedFace: App.Http.Resources.Models.FaceResource): void {
	// Update the face in the list with the new person assignment
	const idx = faces.value.findIndex((f) => f.id === updatedFace.id);
	if (idx !== -1) {
		faces.value[idx] = updatedFace;
	}
}

function onFaceDismissed(): void {
	// Remove the dismissed face from the list
	if (assigningFace.value) {
		faces.value = faces.value.filter((f) => f.id !== assigningFace.value?.id);
		selectedIds.value = selectedIds.value.filter((sid) => sid !== assigningFace.value?.id);
	}
}

function onBatchAssigned(): void {
	// Person names and unassigned-only filtering may both be affected; reload to stay accurate.
	selectedIds.value = [];
	load(1);
}

function openPhotoViewer(face: App.Http.Resources.Models.FaceResource): void {
	loadingPhoto.value = true;
	showPhotoViewer.value = true;
	viewingPhoto.value = null;

	ModerationService.getPhoto(face.photo_id)
		.then((response) => {
			viewingPhoto.value = response.data;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: "Failed to load photo", life: 3000 });
			showPhotoViewer.value = false;
		})
		.finally(() => {
			loadingPhoto.value = false;
		});
}

onMounted(() => {
	load();
	window.addEventListener("keydown", handleSelectAllShortcut);
});

onBeforeUnmount(() => {
	window.removeEventListener("keydown", handleSelectAllShortcut);
});
</script>
