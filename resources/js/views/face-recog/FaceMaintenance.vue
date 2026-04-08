<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 rounded-none sticky top-0 z-10">
			<template #start>
				<OpenLeftMenu />
			</template>
			<template #center>
				{{ $t("maintenance.face_quality.title") }}
			</template>
		</Toolbar>

		<div class="max-w-6xl mx-auto px-4 pt-4">
			<p class="text-muted-color mb-4 text-center text-sm">
				{{ $t("maintenance.face_quality.description") }}
			</p>

			<!-- Sort controls -->
			<div class="flex flex-wrap gap-3 mb-4 items-center">
				<span class="text-muted-color text-sm">{{ $t("maintenance.face_quality.sort_by") }}</span>
				<div class="flex gap-2">
					<Button
						:label="$t('maintenance.face_quality.sort_confidence')"
						:severity="sortBy === 'confidence' ? 'primary' : 'secondary'"
						size="small"
						class="border-none"
						:icon="sortBy === 'confidence' ? (sortDir === 'asc' ? 'pi pi-arrow-up' : 'pi pi-arrow-down') : undefined"
						icon-pos="right"
						@click="setSort('confidence')"
					/>
					<Button
						:label="$t('maintenance.face_quality.sort_blur')"
						:severity="sortBy === 'laplacian_variance' ? 'primary' : 'secondary'"
						size="small"
						class="border-none"
						:icon="sortBy === 'laplacian_variance' ? (sortDir === 'asc' ? 'pi pi-arrow-up' : 'pi pi-arrow-down') : undefined"
						icon-pos="right"
						@click="setSort('laplacian_variance')"
					/>
				</div>
				<Button
					v-if="faces.length > 0"
					:label="allSelected ? $t('maintenance.face_quality.deselect_all') : $t('maintenance.face_quality.select_all')"
					severity="secondary"
					text
					size="small"
					class="border-none"
					@click="toggleSelectAll"
				/>
				<template v-if="selectedIds.length > 0">
					<span class="text-sm text-muted-color">
						{{ $t("maintenance.face_quality.selected_count", { count: String(selectedIds.length) }) }}
					</span>
					<Button
						:label="$t('maintenance.face_quality.batch_dismiss')"
						icon="pi pi-times"
						severity="danger"
						size="small"
						class="border-none"
						:loading="batchDismissing"
						@click="batchDismiss"
					/>
				</template>
			</div>

			<!-- Initial loading -->
			<div v-if="loading && faces.length === 0" class="flex justify-center py-12">
				<ProgressSpinner />
			</div>

			<!-- Empty state -->
			<div v-else-if="!loading && faces.length === 0" class="text-center py-12 text-muted-color">
				<i class="pi pi-check-circle text-4xl mb-4 block"></i>
				{{ $t("maintenance.face_quality.no_faces") }}
			</div>

			<!-- Face rows -->
			<div v-else class="flex flex-col divide-y divide-surface">
				<!-- Header row -->
				<div
					class="hidden sm:grid grid-cols-[2.5rem_3rem_1fr_1fr_5rem_5rem_2.5rem] gap-3 items-center px-2 py-1 text-xs text-muted-color font-medium"
				>
					<div></div>
					<div>{{ $t("maintenance.face_quality.col_face") }}</div>
					<div>{{ $t("maintenance.face_quality.col_person") }}</div>
					<div>{{ $t("maintenance.face_quality.col_cluster") }}</div>
					<div class="text-right">{{ $t("maintenance.face_quality.col_confidence") }}</div>
					<div class="text-right">{{ $t("maintenance.face_quality.col_blur") }}</div>
					<div></div>
				</div>

				<div
					v-for="(face, idx) in faces"
					:key="face.id"
					class="grid grid-cols-[2.5rem_3rem_1fr_2.5rem] sm:grid-cols-[2.5rem_3rem_1fr_1fr_5rem_5rem_2.5rem] gap-3 items-center px-2 py-2 cursor-pointer select-none hover:bg-surface-100 dark:hover:bg-surface-800 transition-colors"
					:class="{ 'bg-primary-50 dark:bg-primary-900/20': selectedIds.includes(face.id) }"
					@click="toggleSelection(face.id, idx, $event)"
				>
					<!-- Checkbox -->
					<div class="flex justify-center" @click.stop>
						<Checkbox :modelValue="selectedIds.includes(face.id)" binary @click.stop="toggleSelection(face.id, idx, $event)" />
					</div>

					<!-- Face image -->
					<div class="w-10 h-10 rounded-md overflow-hidden bg-surface-200 dark:bg-surface-700 shrink-0">
						<img v-if="face.crop_url" :src="face.crop_url" alt="" class="w-full h-full object-cover" loading="lazy" />
						<div v-else class="w-full h-full flex items-center justify-center">
							<i class="pi pi-user text-muted-color"></i>
						</div>
					</div>

					<!-- Person -->
					<div class="min-w-0">
						<span v-if="face.person_name" class="font-medium truncate block">{{ face.person_name }}</span>
						<span v-else class="text-muted-color italic text-sm">{{ $t("maintenance.face_quality.unassigned") }}</span>
						<!-- Cluster: shown here on mobile -->
						<Tag
							v-if="face.cluster_label !== null"
							:value="`#${face.cluster_label}`"
							severity="secondary"
							class="sm:hidden mt-0.5 text-xs! py-0! px-1!"
						/>
					</div>

					<!-- Cluster (hidden on mobile, shown in own column on sm+) -->
					<div class="hidden sm:block">
						<Tag v-if="face.cluster_label !== null" :value="`#${face.cluster_label}`" severity="secondary" class="text-xs! py-0! px-1!" />
						<span v-else class="text-muted-color">—</span>
					</div>

					<!-- Confidence -->
					<div class="hidden sm:block text-right text-sm">
						<span :class="face.confidence < 0.5 ? 'text-red-500' : 'text-muted-color'">{{ face.confidence.toFixed(2) }}</span>
					</div>

					<!-- Blur -->
					<div class="hidden sm:block text-right text-sm">
						<span :class="face.laplacian_variance < 50 ? 'text-red-500' : 'text-muted-color'">{{
							face.laplacian_variance.toFixed(0)
						}}</span>
					</div>

					<!-- Dismiss -->
					<div class="flex justify-center" @click.stop>
						<Button
							icon="pi pi-times"
							severity="danger"
							text
							rounded
							size="small"
							class="border-none"
							:loading="dismissingId === face.id"
							v-tooltip.left="$t('maintenance.face_quality.dismiss')"
							@click="dismissFace(face.id)"
						/>
					</div>
				</div>
			</div>

			<!-- Infinite scroll sentinel -->
			<PaginationInfiniteScroll :loading="loadingMore" :hasMore="hasMorePages" @loadMore="loadMore" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import ProgressSpinner from "primevue/progressspinner";
import Tag from "primevue/tag";
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import PaginationInfiniteScroll from "@/components/pagination/PaginationInfiniteScroll.vue";
import FaceMaintenanceService from "@/services/face-maintenance-service";
import FaceDetectionService from "@/services/face-detection-service";

const faces = ref<App.Http.Resources.Models.FaceResource[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const hasMorePages = ref(false);
const currentPage = ref(1);
const dismissingId = ref<string | null>(null);
const batchDismissing = ref(false);
const sortBy = ref<"confidence" | "laplacian_variance">("confidence");
const sortDir = ref<"asc" | "desc">("asc");

// Selection state
const selectedIds = ref<string[]>([]);
const lastSelectedIndex = ref<number>(-1);

const allSelected = computed(() => faces.value.length > 0 && selectedIds.value.length === faces.value.length);

const toast = useToast();

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
		sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
	} else {
		sortBy.value = field;
		sortDir.value = "asc";
	}
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

onMounted(() => {
	load();
});
</script>
