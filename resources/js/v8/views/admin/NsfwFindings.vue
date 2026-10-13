<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("moderation.findings_title") }}
	</UHeader>

	<UCard class="max-w-6xl mx-auto mt-4">
		<p class="text-muted mb-6 text-center">{{ $t("moderation.findings_description") }}</p>

		<!-- Loading initial -->
		<div v-if="loading && rows.length === 0" class="flex justify-center py-12">
			<LycheeLoadingIcon fast class="text-3xl" />
		</div>

		<!-- Empty state -->
		<div v-else-if="!loading && rows.length === 0" class="text-center py-12">
			<div class="text-muted mb-4">
				<UIcon name="lucide:check-circle" class="text-4xl" />
			</div>
			<p class="text-muted">{{ $t("moderation.findings_empty") }}</p>
		</div>

		<!-- Bulk actions bar (always visible to prevent layout shift) -->
		<div class="flex items-center gap-2 mb-3 px-1 h-8">
			<span class="text-muted text-sm">{{ selectedIds.size }} {{ $t("moderation.selected") }}</span>
			<UButton
				v-if="selectedIds.size > 0"
				variant="solid"
				icon="lucide:x"
				color="error"
				size="sm"
				:label="$t('moderation.findings_dismiss_selected')"
				@click="dismissPhotos(Array.from(selectedIds))"
			/>
		</div>

		<UTable v-if="rows.length > 0" :data="rows" :columns="columns" sticky class="max-h-[65vh] text-sm">
			<template #select-header>
				<UCheckbox :model-value="allSelected" @update:model-value="toggleAll" />
			</template>
			<template #select-cell="{ row }">
				<UCheckbox :model-value="selectedIds.has(row.original.photo_id)" @update:model-value="() => toggleOne(row.original.photo_id)" />
			</template>

			<template #thumbnail-cell="{ row }">
				<img
					v-if="row.original.thumb_url"
					:src="row.original.thumb_url"
					:alt="row.original.title"
					class="w-16 min-w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80"
					@click="openPhoto(row.original.photo_id)"
				/>
				<UIcon v-else name="lucide:image" class="text-2xl text-muted" />
			</template>

			<template #title-cell="{ row }">
				<div class="flex flex-col">
					<span class="font-medium">{{ row.original.title }}</span>
					<span class="text-muted text-xs">{{ row.original.owner_username }}</span>
					<span v-if="row.original.scanned_at" class="text-muted text-xs whitespace-nowrap">
						{{ new Date(row.original.scanned_at).toLocaleDateString() }}
					</span>
				</div>
			</template>

			<template #status-cell="{ row }">
				<div class="flex flex-col gap-1 items-start">
					<UBadge v-if="!row.original.is_validated" color="neutral" class="text-xs">{{ $t("moderation.findings_hidden") }}</UBadge>
					<UBadge v-if="row.original.nsfw_status === 'review'" color="warning" class="text-xs">{{ $t("moderation.nsfw_review") }}</UBadge>
				</div>
			</template>

			<template #findings-cell="{ row }">
				<div class="flex flex-wrap gap-1 max-w-sm">
					<span
						v-for="detection in row.original.detections"
						:key="detection.id"
						:class="['inline-flex items-center gap-1 rounded px-2 py-0.5 text-xs whitespace-nowrap', chipClass(detection)]"
					>
						{{ detection.label }} {{ Math.round(detection.confidence * 100) }}%
						<button
							type="button"
							class="cursor-pointer hover:opacity-70"
							:aria-label="$t('moderation.findings_dismiss')"
							@click="dismissDetection(row.original.photo_id, detection.id)"
						>
							<UIcon name="lucide:x" />
						</button>
					</span>
				</div>
			</template>

			<template #albums-cell="{ row }">
				<div v-if="row.original.albums.length > 0" class="flex flex-col">
					<RouterLink
						v-for="album in row.original.albums"
						:key="album.id"
						:to="{ name: 'album', params: { albumId: album.id, photoId: row.original.photo_id } }"
						class="text-primary-400 hover:underline whitespace-nowrap"
					>
						{{ album.title }} <UIcon name="lucide:arrow-up-right" class="text-xs" />
					</RouterLink>
				</div>
				<span v-else class="text-muted">—</span>
			</template>

			<template #actions-cell="{ row }">
				<UButton
					icon="lucide:x"
					color="error"
					variant="ghost"
					size="sm"
					:aria-label="$t('moderation.findings_dismiss_all')"
					:title="$t('moderation.findings_dismiss_all')"
					@click="dismissPhotos([row.original.photo_id])"
				/>
			</template>

			<template #body-bottom>
				<tr>
					<td colspan="7">
						<div ref="sentinel" class="flex justify-center py-4">
							<LycheeLoadingIcon fast v-if="loading && rows.length > 0" class="text-2xl" />
						</div>
					</td>
				</tr>
			</template>
		</UTable>
	</UCard>

	<!-- Photo lightbox (full screen) -->
	<div v-if="photoVisible" class="fixed inset-0 z-50 bg-black flex items-center justify-center" @click="closePhoto">
		<PhotoBox @go-back="closePhoto" />
	</div>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { RouterLink } from "vue-router";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import PhotoBox from "@/v8/components/gallery/photoModule/PhotoBox.vue";
import ModerationService from "@/services/moderation-service";
import NsfwFindingsService from "@/services/nsfw-findings-service";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotoNsfwDetectionsStore } from "@/stores/PhotoNsfwDetectionsState";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useConfirmDialog } from "@/v8/composables/useConfirmDialog";
import { trans } from "laravel-vue-i18n";
import UButton from "@nuxt/ui/components/Button.vue";
import UCheckbox from "@nuxt/ui/components/Checkbox.vue";
import UBadge from "@nuxt/ui/components/Badge.vue";
import type { TableColumn } from "@nuxt/ui";

type Finding = App.Http.Resources.Models.NsfwFindingResource;
type Detection = App.Http.Resources.Models.NsfwDetectionResource;

const toast = useAppToast();
const { confirm } = useConfirmDialog();
const photoStore = usePhotoStore();
const nsfwDetectionsStore = usePhotoNsfwDetectionsStore();

const loading = ref(false);
const photoVisible = ref(false);
const rows = ref<Finding[]>([]);
const selectedIds = ref(new Set<string>());
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = 30;
const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const allSelected = computed(() => rows.value.length > 0 && rows.value.every((r) => selectedIds.value.has(r.photo_id)));

const columns: TableColumn<Finding>[] = [
	{ id: "select" },
	{ id: "thumbnail", header: trans("moderation.col_photo") },
	{ id: "title", header: trans("moderation.col_title") },
	{ id: "status", header: trans("moderation.findings_col_status") },
	{ id: "findings", header: trans("moderation.findings_col_findings") },
	{ id: "albums", header: trans("moderation.findings_col_albums") },
	{ id: "actions" },
];

/** Colour of the most severe tier of the finding: block, then review, then sensitive. */
function chipClass(detection: Detection): string {
	if (detection.is_block) {
		return "bg-red-500/15 text-red-600 dark:text-red-400";
	}
	if (detection.is_review) {
		return "bg-orange-500/15 text-orange-600 dark:text-orange-400";
	}
	return "bg-yellow-500/15 text-yellow-700 dark:text-yellow-400";
}

function toggleAll() {
	selectedIds.value = allSelected.value ? new Set() : new Set(rows.value.map((r) => r.photo_id));
}

function toggleOne(photoId: string) {
	const next = new Set(selectedIds.value);
	if (!next.delete(photoId)) {
		next.add(photoId);
	}
	selectedIds.value = next;
}

function openPhoto(photoId: string) {
	ModerationService.getPhoto(photoId).then((response) => {
		photoStore.photo = response.data;
		photoVisible.value = true;
	});
}

function closePhoto() {
	photoVisible.value = false;
	photoStore.reset();
}

function showError() {
	toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.findings_error"), life: 3000 });
}

function removeRows(photoIds: string[]) {
	const removed = new Set(photoIds);
	rows.value = rows.value.filter((r) => !removed.has(r.photo_id));
	const next = new Set(selectedIds.value);
	photoIds.forEach((id) => next.delete(id));
	selectedIds.value = next;
	nsfwDetectionsStore.forget(photoIds);
}

/** A chip dismiss acts at once; the row leaves the list with its last finding. */
function dismissDetection(photoId: string, detectionId: number) {
	NsfwFindingsService.dismiss([detectionId])
		.then(() => {
			const row = rows.value.find((r) => r.photo_id === photoId);
			const remaining = row?.detections.filter((d) => d.id !== detectionId) ?? [];
			if (row === undefined || remaining.length === 0) {
				removeRows([photoId]);
				return;
			}
			row.detections = remaining;
			nsfwDetectionsStore.forget([photoId]);
		})
		.catch(showError);
}

/** Whole-photo and bulk dismissals ask first: nothing can be restored. */
function dismissPhotos(photoIds: string[]) {
	confirm({
		title: trans("moderation.findings_confirm_header"),
		message: trans("moderation.findings_confirm", { count: String(photoIds.length) }),
		acceptLabel: trans("moderation.findings_dismiss"),
		rejectLabel: trans("dialogs.button.cancel"),
		severity: "danger",
	}).then((accepted) => {
		if (!accepted) {
			return;
		}
		NsfwFindingsService.dismissPhotos(photoIds)
			.then(() => {
				removeRows(photoIds);
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("moderation.findings_dismissed"), life: 3000 });
			})
			.catch(showError);
	});
}

function load(page: number) {
	if (loading.value || page > lastPage.value) {
		return;
	}
	loading.value = true;
	NsfwFindingsService.list(page, perPage)
		.then((response) => {
			rows.value = page === 1 ? response.data.photos : [...rows.value, ...response.data.photos];
			currentPage.value = response.data.current_page;
			lastPage.value = response.data.last_page;
		})
		.catch(showError)
		.finally(() => {
			loading.value = false;
		});
}

onMounted(() => {
	load(1);
	observer = new IntersectionObserver(
		(entries) => {
			if (entries[0].isIntersecting && !loading.value && currentPage.value < lastPage.value) {
				load(currentPage.value + 1);
			}
		},
		{ threshold: 0.1 },
	);
});

// The sentinel only mounts once the table (and its `body-bottom` slot) renders.
watch(sentinel, (el) => {
	if (el) {
		observer?.observe(el);
	}
});

onUnmounted(() => {
	observer?.disconnect();
});
</script>
