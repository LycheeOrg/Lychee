<template>
	<div class="w-full border-0 h-14 flex items-center justify-between px-2">
		<OpenLeftMenu />
		<span class="absolute left-1/2 -translate-x-1/2 pointer-events-none">{{ $t("moderation.title") }}</span>
		<div></div>
	</div>

	<UCard class="max-w-5xl mx-auto mt-4">
		<p class="text-muted mb-6 text-center">{{ $t("moderation.description") }}</p>

		<!-- Loading initial -->
		<div v-if="loading && photos.length === 0" class="flex justify-center py-12">
			<Spinner class="text-3xl" />
		</div>

		<!-- Empty state -->
		<div v-else-if="!loading && photos.length === 0" class="text-center py-12">
			<div class="text-muted mb-4">
				<UIcon name="prime:check-circle" class="text-4xl" />
			</div>
			<p class="text-muted">{{ $t("moderation.no_pending") }}</p>
		</div>

		<!-- Bulk actions bar (always visible to prevent layout shift) -->
		<div class="flex items-center gap-2 mb-3 px-1 h-8">
			<span class="text-muted text-sm">{{ selectedIds.size }} {{ $t("moderation.selected") }}</span>
			<template v-if="selectedIds.size > 0">
				<UButton icon="prime:check" color="success" size="sm" :label="$t('moderation.approve_selected')" @click="approveSelected" />
				<UButton icon="prime:trash" color="error" size="sm" :label="$t('moderation.delete_selected')" @click="deleteSelected" />
			</template>
		</div>

		<!-- Photos table -->
		<table v-if="photos.length > 0" class="w-full text-sm">
			<thead>
				<tr class="text-left text-muted border-b border-neutral-200 dark:border-neutral-700">
					<th class="py-2 pr-3 w-10"><input type="checkbox" :checked="allSelected" @change="toggleAll" /></th>
					<th class="py-2 pr-3 w-20">{{ $t("moderation.col_thumbnail") }}</th>
					<th class="py-2 pr-3">{{ $t("moderation.col_title") }}</th>
					<th class="py-2 pr-3">{{ $t("moderation.col_owner") }}</th>
					<th class="py-2 pr-3">{{ $t("moderation.col_album") }}</th>
					<th class="py-2 pr-3">{{ $t("moderation.col_uploaded") }}</th>
					<th class="py-2 pr-3">{{ $t("moderation.col_nsfw") }}</th>
					<th class="py-2"></th>
				</tr>
			</thead>
			<tbody>
				<tr
					v-for="photo in photos"
					:key="photo.photo_id"
					class="border-b border-neutral-100 dark:border-neutral-800 hover:bg-elevated/50 cursor-pointer"
					@click="toggleOne(photo.photo_id)"
				>
					<td class="py-2 pr-3">
						<input type="checkbox" :checked="selectedIds.has(photo.photo_id)" @click.stop @change="toggleOne(photo.photo_id)" />
					</td>
					<td class="py-2 pr-3">
						<img
							v-if="photo.thumb_url"
							:src="photo.thumb_url"
							class="w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80"
							:alt="photo.title"
							@click.stop="openPhoto(photo.photo_id)"
						/>
						<UIcon v-else name="prime:image" class="text-2xl text-muted" />
					</td>
					<td class="py-2 pr-3">{{ photo.title }}</td>
					<td class="py-2 pr-3">{{ photo.owner_username }}</td>
					<td class="py-2 pr-3">
						<RouterLink
							v-if="photo.album_title"
							:to="{ name: 'album', params: { albumId: photo.album_id } }"
							class="text-primary-400 hover:underline"
							@click.stop
						>
							{{ photo.album_title }}
						</RouterLink>
						<span v-else class="text-muted">—</span>
					</td>
					<td class="py-2 pr-3 whitespace-nowrap">{{ new Date(photo.created_at).toLocaleDateString() }}</td>
					<td class="py-2 pr-3">
						<UBadge v-if="photo.nsfw_status === 'review'" color="warning" class="text-xs">{{ $t("moderation.nsfw_review") }}</UBadge>
					</td>
					<td class="py-2">
						<div class="flex gap-1" @click.stop>
							<UButton icon="prime:check" color="success" variant="ghost" size="sm" @click="approveSingle(photo.photo_id)" />
							<UButton
								icon="prime:trash"
								color="error"
								variant="ghost"
								size="sm"
								@click="deleteSingle(photo.photo_id, photo.album_id)"
							/>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<!-- Infinite scroll sentinel -->
		<div ref="sentinel" class="flex justify-center py-4">
			<Spinner v-if="loading && photos.length > 0" class="text-2xl" />
		</div>
	</UCard>

	<!-- Photo lightbox (full screen) -->
	<div v-if="photoVisible" class="fixed inset-0 z-50 bg-black flex items-center justify-center" @click="closePhoto">
		<PhotoBox @go-back="closePhoto" />
	</div>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from "vue";
import { RouterLink } from "vue-router";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Spinner from "@/v8/components/Spinner.vue";
import PhotoBox from "@/v8/components/gallery/photoModule/PhotoBox.vue";
import ModerationService from "@/services/moderation-service";
import PhotoService from "@/services/photo-service";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";

const toast = useAppToast();
const photoStore = usePhotoStore();

const loading = ref(false);
const photoVisible = ref(false);
const photos = ref<App.Http.Resources.Models.ModerationResource[]>([]);
const selectedIds = ref(new Set<string>());
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(30);
const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const allSelected = computed(() => photos.value.length > 0 && photos.value.every((p) => selectedIds.value.has(p.photo_id)));

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
	if (sentinel.value) {
		observer.observe(sentinel.value);
	}
});

onUnmounted(() => {
	observer?.disconnect();
});
</script>
