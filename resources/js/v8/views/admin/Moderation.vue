<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("moderation.title") }}
	</UHeader>

	<UCard class="max-w-5xl mx-auto mt-4">
		<p class="text-muted mb-6 text-center">{{ $t("moderation.description") }}</p>

		<!-- Loading initial -->
		<div v-if="loading && photos.length === 0" class="flex justify-center py-12">
			<LycheeLoadingIcon fast class="text-3xl" />
		</div>

		<!-- Empty state -->
		<div v-else-if="!loading && photos.length === 0" class="text-center py-12">
			<div class="text-muted mb-4">
				<UIcon name="lucide:check-circle" class="text-4xl" />
			</div>
			<p class="text-muted">{{ $t("moderation.no_pending") }}</p>
		</div>

		<!-- Bulk actions bar (always visible to prevent layout shift) -->
		<div class="flex items-center gap-2 mb-3 px-1 h-8">
			<span class="text-muted text-sm">{{ selectedIds.size }} {{ $t("moderation.selected") }}</span>
			<template v-if="selectedIds.size > 0">
				<UButton
					variant="solid"
					icon="lucide:check"
					color="success"
					size="sm"
					:label="$t('moderation.approve_selected')"
					@click="approveSelected"
				/>
				<UButton
					variant="solid"
					icon="lucide:trash"
					color="error"
					size="sm"
					:label="$t('moderation.delete_selected')"
					@click="deleteSelected"
				/>
			</template>
		</div>

		<!-- Photos table -->
		<UTable v-if="photos.length > 0" :data="photos" :columns="columns" sticky class="max-h-[65vh] text-sm">
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
					class="w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80"
					@click="openPhoto(row.original.photo_id)"
				/>
				<UIcon v-else name="lucide:image" class="text-2xl text-muted" />
			</template>

			<template #album-cell="{ row }">
				<RouterLink
					v-if="row.original.album_title"
					:to="{ name: 'album', params: { albumId: row.original.album_id } }"
					class="text-primary-400 hover:underline"
				>
					{{ row.original.album_title }}
				</RouterLink>
				<span v-else class="text-muted">—</span>
			</template>

			<template #created_at-cell="{ row }">
				<span class="whitespace-nowrap">{{ new Date(row.original.created_at).toLocaleDateString() }}</span>
			</template>

			<template #nsfw_status-cell="{ row }">
				<UBadge v-if="row.original.nsfw_status === 'review'" color="warning" class="text-xs">
					{{ $t("moderation.nsfw_review") }}
				</UBadge>
			</template>

			<template #actions-cell="{ row }">
				<div class="flex gap-1">
					<UButton icon="lucide:check" color="success" variant="ghost" size="sm" @click="approveSingle(row.original.photo_id)" />
					<UButton
						icon="lucide:trash"
						color="error"
						variant="ghost"
						size="sm"
						@click="deleteSingle(row.original.photo_id, row.original.album_id)"
					/>
				</div>
			</template>

			<template #body-bottom>
				<tr>
					<td colspan="8">
						<div ref="sentinel" class="flex justify-center py-4">
							<LycheeLoadingIcon fast v-if="loading && photos.length > 0" class="text-2xl" />
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
import PhotoService from "@/services/photo-service";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import UButton from "@nuxt/ui/components/Button.vue";
import UCheckbox from "@nuxt/ui/components/Checkbox.vue";
import UBadge from "@nuxt/ui/components/Badge.vue";
import type { TableColumn } from "@nuxt/ui";

type Photo = App.Http.Resources.Models.ModerationResource;

const toast = useAppToast();
const photoStore = usePhotoStore();

const loading = ref(false);
const photoVisible = ref(false);
const photos = ref<Photo[]>([]);
const selectedIds = ref(new Set<string>());
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(30);
const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const allSelected = computed(() => photos.value.length > 0 && photos.value.every((p) => selectedIds.value.has(p.photo_id)));

const columns: TableColumn<Photo>[] = [
	{ id: "select" },
	{ id: "thumbnail", header: trans("moderation.col_thumbnail") },
	{ accessorKey: "title", header: trans("moderation.col_title") },
	{ accessorKey: "owner_username", header: trans("moderation.col_owner") },
	{ id: "album", header: trans("moderation.col_album") },
	{ id: "created_at", header: trans("moderation.col_uploaded") },
	{ id: "nsfw_status", header: trans("moderation.col_nsfw") },
	{ id: "actions" },
];

function toggleAll() {
	if (allSelected.value) {
		selectedIds.value = new Set();
	} else {
		selectedIds.value = new Set(photos.value.map((p) => p.photo_id));
	}
}

function toggleOne(photoId: string) {
	const next = new Set(selectedIds.value);
	if (next.has(photoId)) {
		next.delete(photoId);
	} else {
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

function removeFromList(photoIds: string[]) {
	const removed = new Set(photoIds);
	photos.value = photos.value.filter((p) => !removed.has(p.photo_id));
	const next = new Set(selectedIds.value);
	photoIds.forEach((id) => next.delete(id));
	selectedIds.value = next;
}

function approveSingle(photoId: string) {
	ModerationService.approve([photoId])
		.then(() => {
			removeFromList([photoId]);
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("moderation.approved"), life: 3000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_pending"), life: 3000 });
		});
}

function deleteSingle(photoId: string, albumId: string | null) {
	PhotoService.delete([photoId], albumId ?? "unsorted")
		.then(() => {
			removeFromList([photoId]);
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_pending"), life: 3000 });
		});
}

function approveSelected() {
	const ids = Array.from(selectedIds.value);
	ModerationService.approve(ids)
		.then(() => {
			removeFromList(ids);
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("moderation.approved"), life: 3000 });
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_pending"), life: 3000 });
		});
}

function deleteSelected() {
	const ids = Array.from(selectedIds.value);
	const photo = photos.value.find((p) => ids.includes(p.photo_id));
	const albumId = photo?.album_id ?? "unsorted";
	PhotoService.delete(ids, albumId)
		.then(() => {
			removeFromList(ids);
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_pending"), life: 3000 });
		});
}

function load(page: number = 1) {
	if (loading.value || page > lastPage.value) {
		return;
	}
	loading.value = true;
	ModerationService.list(page, perPage.value)
		.then((response) => {
			photos.value = page === 1 ? response.data.photos : [...photos.value, ...response.data.photos];
			currentPage.value = response.data.current_page;
			lastPage.value = response.data.last_page;
			perPage.value = response.data.per_page;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_pending"), life: 3000 });
		})
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

// The sentinel only mounts once the table (and its `body-bottom` slot) renders,
// which happens asynchronously after the first page loads.
watch(sentinel, (el) => {
	if (el) {
		observer?.observe(el);
	}
});

onUnmounted(() => {
	observer?.disconnect();
});
</script>
