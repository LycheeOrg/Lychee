<template>
	<!-- Set Owner Dialog -->
	<BulkSetOwnerDialog v-model:visible="isSetOwnerVisible" :album-ids="selectedIds" @transferred="onTransferred" />

	<!-- Delete Confirmation Dialog -->
	<DeleteDialog v-model:open="isDeleteVisible" :album-ids="selectedIds" @deleted="onDeleted" />

	<!-- Edit Fields Dialog -->
	<BulkEditFieldsDialog v-model:visible="isEditFieldsVisible" :album-ids="selectedIds" @patched="onPatched" />

	<!-- Toolbar -->
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("bulk_album_edit.title") }}
	</UHeader>

	<UCard class="max-w-7xl mx-auto mt-4">
		<p class="text-center text-muted text-sm mb-4">{{ $t("bulk_album_edit.description") }}</p>
		<!-- Warning -->
		<p class="text-center text-highlighted text-sm mb-4">{{ $t("bulk_album_edit.warning") }}</p>

		<!-- Filter + controls bar -->
		<div class="flex flex-wrap items-center gap-2 mb-3">
			<UInput
				v-model="search"
				size="sm"
				:placeholder="$t('bulk_album_edit.filter_placeholder')"
				class="flex-1 min-w-48"
				@update:model-value="onSearchInput"
			/>

			<USelectMenu v-model="perPage" :items="perPageOptions" class="w-24" size="sm" @update:model-value="load(1)" />

			<UButton
				size="sm"
				color="neutral"
				variant="soft"
				:icon="paginationMode === 'numbered' ? 'lucide:list' : 'lucide:align-justify'"
				:label="$t(paginationMode === 'numbered' ? 'bulk_album_edit.mode_infinite' : 'bulk_album_edit.mode_paginated')"
				@click="togglePaginationMode"
			/>
		</div>

		<!-- Selection + action bar -->
		<div class="flex flex-wrap items-center gap-2 mb-3">
			<span class="text-muted text-sm">
				{{ trans_choice("bulk_album_edit.total_selected", selectedIds.length, { n: String(selectedIds.length) }) }}
			</span>

			<UButton
				size="sm"
				color="neutral"
				variant="soft"
				:label="$t('bulk_album_edit.select_all_page')"
				@click="toggleSelectPage(!isPageAllSelected)"
			/>

			<UButton size="sm" color="neutral" variant="soft" :label="$t('bulk_album_edit.select_all_matching')" @click="selectAllMatching" />

			<UButton
				v-if="selectedIds.length > 0"
				size="sm"
				color="error"
				variant="soft"
				:label="$t('bulk_album_edit.action_delete')"
				icon="lucide:trash"
				@click="
					() => {
						isDeleteVisible = true;
					}
				"
			/>
			<UButton
				v-if="selectedIds.length > 0 && numUsers > 1"
				size="sm"
				color="neutral"
				variant="soft"
				:label="$t('bulk_album_edit.action_set_owner')"
				icon="lucide:user"
				@click="
					() => {
						isSetOwnerVisible = true;
					}
				"
			/>
			<UButton
				v-if="selectedIds.length > 0"
				size="sm"
				color="primary"
				:label="$t('bulk_album_edit.action_edit_fields')"
				icon="lucide:pencil"
				@click="
					() => {
						isEditFieldsVisible = true;
					}
				"
			/>
		</div>

		<!-- Loading state -->
		<div v-if="loading" class="flex justify-center py-12">
			<Spinner />
		</div>

		<!-- Table -->
		<div v-else class="overflow-x-auto">
			<table class="w-full text-sm border-collapse">
				<thead>
					<tr class="border-b border-default text-left">
						<th class="p-2 w-10 text-center">
							<UCheckbox
								:model-value="isPageAllSelected"
								@update:model-value="(v: boolean | 'indeterminate') => toggleSelectPage(v === true)"
							/>
						</th>
						<th class="p-2">{{ $t("bulk_album_edit.col_title") }}</th>
						<th class="p-2 w-32">{{ $t("bulk_album_edit.col_owner") }}</th>
						<th class="p-2 w-28 text-center">{{ $t("bulk_album_edit.col_license") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_is_nsfw") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_is_public") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_is_link_required") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_grants_download") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_grants_full_photo_access") }}</th>
						<th v-if="is_se_enabled || is_se_preview_enabled" class="p-2 w-14 text-center text-error">
							{{ $t("bulk_album_edit.col_grants_upload") }}
						</th>
						<th class="p-2 w-52 text-center">{{ $t("bulk_album_edit.col_photo_sorting") }}</th>
						<th class="p-2 w-52 text-center">{{ $t("bulk_album_edit.col_album_sorting") }}</th>
						<th class="p-2 w-32 text-left">{{ $t("bulk_album_edit.col_created_at") }}</th>
						<th class="p-2 w-10"></th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(album, idx) in albums" :key="album.id" class="hover:bg-elevated/50">
						<td class="p-2 text-center">
							<UCheckbox :model-value="selectedIds.includes(album.id)" @update:model-value="() => toggleRow(album.id)" />
						</td>
						<td class="p-2 whitespace-nowrap">
							<span :style="`padding-left: ${(albumDepths[idx] - 1) * 1.25}rem`" class="inline-flex items-center gap-1">
								<span v-if="albumDepths[idx] > 0" class="text-muted mr-1">└─</span>
								<UInput
									v-if="editingTitleId === album.id"
									v-model="editingTitleValue"
									size="sm"
									class="w-64"
									@blur="saveTitle(album)"
									@keyup.enter="saveTitle(album)"
									@keyup.escape="cancelEditTitle"
								/>
								<span v-else class="cursor-text hover:text-primary" @click="startEditTitle(album)">{{ album.title }}</span>
							</span>
						</td>
						<td class="p-2 text-muted text-xs w-32">{{ album.owner_name }}</td>
						<td class="p-2 text-muted text-xs text-center w-28">{{ album.license ?? "—" }}</td>
						<td class="p-2 text-center w-14">
							<USwitch
								:model-value="album.is_nsfw"
								color="error"
								@update:model-value="(val: boolean) => onInlineToggle(album.id, 'is_nsfw', val)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<USwitch
								:model-value="album.is_public"
								@update:model-value="(val: boolean) => onInlineToggle(album.id, 'is_public', val)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<USwitch
								:model-value="album.is_link_required"
								:disabled="!album.is_public"
								@update:model-value="(val: boolean) => onInlineToggle(album.id, 'is_link_required', val)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<USwitch
								:model-value="album.grants_download"
								:disabled="!album.is_public"
								@update:model-value="(val: boolean) => onInlineToggle(album.id, 'grants_download', val)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<USwitch
								:model-value="album.grants_full_photo_access"
								:disabled="!album.is_public"
								@update:model-value="(val: boolean) => onInlineToggle(album.id, 'grants_full_photo_access', val)"
							/>
						</td>
						<td v-if="is_se_enabled || is_se_preview_enabled" class="p-2 text-center w-14">
							<USwitch
								:model-value="album.grants_upload"
								:disabled="!album.is_public || !is_se_enabled"
								color="error"
								@update:model-value="(val: boolean) => onInlineToggle(album.id, 'grants_upload', val)"
							/>
						</td>
						<td class="py-2 text-center w-52">
							<div class="flex items-center justify-center gap-1">
								<USelectMenu
									v-if="editingSortingId === album.id + '_photo'"
									:model-value="findOption(photoSortingColumnsOptions, album.photo_sorting_col)"
									:items="photoSortingColumnsOptions"
									label-key="label"
									size="sm"
									class="text-xs w-32"
									@update:model-value="
										(v: SelectOption<App.Enum.ColumnSortingPhotoType> | undefined) =>
											savePhotoSortingCol(album.id, v?.value ?? null)
									"
									@update:open="(o: boolean) => !o && closeEditSorting()"
								>
									<template #default="{ modelValue }">{{ selectedLabel(modelValue) }}</template>
									<template #item-label="{ item }">{{ $t(item.label) }}</template>
								</USelectMenu>
								<span
									v-else
									class="cursor-text text-xs hover:text-primary w-32 text-center"
									@click="startEditPhotoSorting(album.id)"
									>{{
										photoSortingColumnsOptions.find((o) => o.value === album.photo_sorting_col)?.label !== undefined
											? $t(photoSortingColumnsOptions.find((o) => o.value === album.photo_sorting_col)!.label)
											: "—"
									}}</span
								>
								<UButton
									size="sm"
									variant="ghost"
									color="neutral"
									:icon="album.photo_sorting_order === 'DESC' ? 'lucide:arrow-down-wide-narrow' : 'lucide:arrow-up-wide-narrow'"
									:disabled="album.photo_sorting_col === null"
									@click="
										onInlineSortingChange(album.id, 'photo_sorting_order', album.photo_sorting_order === 'DESC' ? 'ASC' : 'DESC')
									"
								/>
							</div>
						</td>
						<td class="py-2 text-center w-52">
							<div class="flex items-center justify-center gap-1">
								<USelectMenu
									v-if="editingSortingId === album.id + '_album'"
									:model-value="findOption(albumSortingColumnsOptions, album.album_sorting_col)"
									:items="albumSortingColumnsOptions"
									label-key="label"
									size="sm"
									class="text-xs w-32"
									@update:model-value="
										(v: SelectOption<App.Enum.ColumnSortingAlbumType> | undefined) =>
											saveAlbumSortingCol(album.id, v?.value ?? null)
									"
									@update:open="(o: boolean) => !o && closeEditSorting()"
								>
									<template #default="{ modelValue }">{{ selectedLabel(modelValue) }}</template>
									<template #item-label="{ item }">{{ $t(item.label) }}</template>
								</USelectMenu>
								<span
									v-else
									class="cursor-text text-xs hover:text-primary w-32 text-center"
									@click="startEditAlbumSorting(album.id)"
									>{{
										albumSortingColumnsOptions.find((o) => o.value === album.album_sorting_col)?.label !== undefined
											? $t(albumSortingColumnsOptions.find((o) => o.value === album.album_sorting_col)!.label)
											: "—"
									}}</span
								>
								<UButton
									size="sm"
									variant="ghost"
									color="neutral"
									:icon="album.album_sorting_order === 'DESC' ? 'lucide:arrow-down-wide-narrow' : 'lucide:arrow-up-wide-narrow'"
									:disabled="album.album_sorting_col === null"
									@click="
										onInlineSortingChange(album.id, 'album_sorting_order', album.album_sorting_order === 'DESC' ? 'ASC' : 'DESC')
									"
								/>
							</div>
						</td>
						<td class="p-2 text-muted text-xs w-32">{{ formatDate(album.created_at) }}</td>
						<td class="p-2 w-10 text-center">
							<UButton size="sm" variant="ghost" color="neutral" icon="lucide:pencil" @click="quickEditAlbum(album.id)" />
						</td>
					</tr>
				</tbody>
			</table>

			<!-- Pagination (numbered mode) -->
			<div v-if="paginationMode === 'numbered'" class="flex justify-center mt-2">
				<UPagination v-model:page="currentPage" :total="total" :items-per-page="perPage" @update:page="() => load()" />
			</div>

			<!-- Infinite scroll sentinel -->
			<div v-if="paginationMode === 'infinite'" ref="sentinel" class="h-4" />
		</div>
	</UCard>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { trans, trans_choice } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import Spinner from "@/v8/components/Spinner.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import DeleteDialog from "@/v8/components/forms/gallery-dialogs/DeleteDialog.vue";
import BulkSetOwnerDialog from "@/v8/components/forms/bulk-album-edit/BulkSetOwnerDialog.vue";
import BulkEditFieldsDialog from "@/v8/components/forms/bulk-album-edit/BulkEditFieldsDialog.vue";
import BulkAlbumEditService, { type BulkAlbumResource } from "@/services/bulk-album-edit-service";
import AlbumService from "@/services/album-service";
import UsersService from "@/services/users-service";
import { photoSortingColumnsOptions, albumSortingColumnsOptions, type SelectOption } from "@/config/constants";
import { useLycheeStateStore } from "@/stores/LycheeState";

const toast = useAppToast();

const { is_se_enabled, is_se_preview_enabled } = storeToRefs(useLycheeStateStore());

const numUsers = ref(0);
UsersService.count().then((data) => {
	numUsers.value = data.data;
});

// ── State ─────────────────────────────────────────────────────────────────────

const albums = ref<BulkAlbumResource[]>([]);
const loading = ref(false);
const search = ref("");
const currentPage = ref(1);
const perPage = ref(100);
const total = ref(0);
const perPageOptions = [100, 200, 500];

const selectedIds = ref<string[]>([]);

const paginationMode = ref<"numbered" | "infinite">("numbered");
const sentinel = ref<HTMLElement | null>(null);
let intersectionObserver: IntersectionObserver | null = null;

// -- dialogs --
const isSetOwnerVisible = ref(false);
const isDeleteVisible = ref(false);
const isEditFieldsVisible = ref(false);

// -- inline title edit --
const editingTitleId = ref<string | null>(null);
const editingTitleValue = ref<string>("");
const editingSortingId = ref<string | null>(null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// ── Computed helpers ──────────────────────────────────────────────────────────

/** O(n) depth computation using a stack of ancestor _rgt values (Q-034-02 → B). */
const albumDepths = computed<number[]>(() => {
	const depths: number[] = [];
	const stack: number[] = [];
	for (const row of albums.value) {
		while (stack.length > 0 && row._lft > stack[stack.length - 1]) {
			stack.pop();
		}
		depths.push(stack.length);
		stack.push(row._rgt);
	}
	return depths;
});

const isPageAllSelected = computed<boolean>(() => {
	return albums.value.length > 0 && albums.value.every((a) => selectedIds.value.includes(a.id));
});

function findOption<T extends string>(options: SelectOption<T>[], value: string | null): SelectOption<T> | undefined {
	return options.find((o) => o.value === value);
}

function selectedLabel<T>(option: SelectOption<T> | undefined): string {
	return option ? trans(option.label) : "";
}

function formatDate(iso: string): string {
	return new Date(iso).toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" });
}

// ── Data Loading ──────────────────────────────────────────────────────────────

function load(page?: number): void {
	if (page !== undefined) {
		currentPage.value = page;
	}
	loading.value = true;
	BulkAlbumEditService.getAlbums({
		search: search.value || undefined,
		page: currentPage.value,
		per_page: perPage.value,
	})
		.then((response) => {
			if (paginationMode.value === "infinite" && page !== undefined && page > 1) {
				albums.value = [...albums.value, ...response.data.data];
			} else {
				albums.value = response.data.data;
			}
			total.value = response.data.total;
			currentPage.value = response.data.current_page;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_load"), life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

function onSearchInput(): void {
	if (searchTimeout !== null) {
		clearTimeout(searchTimeout);
	}
	searchTimeout = setTimeout(() => {
		selectedIds.value = [];
		albums.value = [];
		load(1);
	}, 350);
}

// ── Selection ─────────────────────────────────────────────────────────────────

function toggleRow(id: string): void {
	if (selectedIds.value.includes(id)) {
		selectedIds.value = selectedIds.value.filter((i) => i !== id);
	} else {
		selectedIds.value = [...selectedIds.value, id];
	}
}

function toggleSelectPage(selectAll: boolean): void {
	if (selectAll) {
		const newIds = new Set(selectedIds.value);
		albums.value.forEach((a) => newIds.add(a.id));
		selectedIds.value = Array.from(newIds);
	} else {
		const pageIds = new Set(albums.value.map((a) => a.id));
		selectedIds.value = selectedIds.value.filter((id) => !pageIds.has(id));
	}
}

function selectAllMatching(): void {
	BulkAlbumEditService.getIds(search.value || null)
		.then((response) => {
			const newIds = new Set(selectedIds.value);
			response.data.ids.forEach((id) => newIds.add(id));
			selectedIds.value = Array.from(newIds);
			if (response.data.capped) {
				toast.add({ severity: "warn", summary: trans("toasts.warning"), detail: trans("bulk_album_edit.cap_warning"), life: 5000 });
			}
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_load"), life: 3000 });
		});
}

// ── Inline title editing ─────────────────────────────────────────────────────

function startEditTitle(album: BulkAlbumResource): void {
	editingTitleId.value = album.id;
	editingTitleValue.value = album.title;
}

function saveTitle(album: BulkAlbumResource): void {
	if (editingTitleId.value !== album.id) {
		return;
	}
	const newTitle = editingTitleValue.value.trim();
	editingTitleId.value = null;
	if (newTitle === album.title || newTitle === "") {
		return;
	}
	const originalTitle = album.title;
	album.title = newTitle;
	AlbumService.rename(album.id, newTitle).catch(() => {
		album.title = originalTitle;
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_patch"), life: 3000 });
	});
}

function cancelEditTitle(): void {
	editingTitleId.value = null;
}

// ── Sorting editing ────────────────────────────────────────────────────────────
// USelectMenu has no imperative `.show()` equivalent to PrimeVue's Select; the
// dropdown simply opens on click like a normal select once rendered in edit mode.

function startEditPhotoSorting(albumId: string): void {
	editingSortingId.value = albumId + "_photo";
}

function startEditAlbumSorting(albumId: string): void {
	editingSortingId.value = albumId + "_album";
}

function closeEditSorting(): void {
	editingSortingId.value = null;
}

function savePhotoSortingCol(albumId: string, val: string | null): void {
	onInlineSortingChange(albumId, "photo_sorting_col", val);
	editingSortingId.value = null;
}

function saveAlbumSortingCol(albumId: string, val: string | null): void {
	onInlineSortingChange(albumId, "album_sorting_col", val);
	editingSortingId.value = null;
}

// ── Quick edit ────────────────────────────────────────────────────────────────

function quickEditAlbum(id: string): void {
	selectedIds.value = [id];
	isEditFieldsVisible.value = true;
}

// ── Inline editing ────────────────────────────────────────────────────────────

function onInlineToggle(
	albumId: string,
	field: "is_public" | "is_nsfw" | "is_link_required" | "grants_full_photo_access" | "grants_download" | "grants_upload",
	value: boolean,
): void {
	const album = albums.value.find((a) => a.id === albumId);
	if (album === undefined) {
		return;
	}
	const originalValue = album[field];
	album[field] = value;
	BulkAlbumEditService.patchAlbums({ album_ids: [albumId], [field]: value }).catch(() => {
		album[field] = originalValue;
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_patch"), life: 3000 });
	});
}

function onInlineSortingChange(
	albumId: string,
	field: "photo_sorting_col" | "photo_sorting_order" | "album_sorting_col" | "album_sorting_order",
	value: string | null,
): void {
	const album = albums.value.find((a) => a.id === albumId);
	if (album === undefined) {
		return;
	}
	const originalValue = album[field];
	album[field] = value;
	BulkAlbumEditService.patchAlbums({ album_ids: [albumId], [field]: value }).catch(() => {
		album[field] = originalValue;
		toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_patch"), life: 3000 });
	});
}

// ── Pagination ────────────────────────────────────────────────────────────────

function togglePaginationMode(): void {
	if (paginationMode.value === "numbered") {
		paginationMode.value = "infinite";
	} else {
		paginationMode.value = "numbered";
		if (intersectionObserver !== null) {
			intersectionObserver.disconnect();
			intersectionObserver = null;
		}
		albums.value = [];
		load(1);
	}
}

function setupInfiniteScroll(): void {
	if (sentinel.value === null) {
		return;
	}
	intersectionObserver = new IntersectionObserver((entries) => {
		if (entries[0].isIntersecting && !loading.value && currentPage.value * perPage.value < total.value) {
			load(currentPage.value + 1);
		}
	});
	intersectionObserver.observe(sentinel.value);
}

watch([paginationMode, sentinel], ([mode, el]) => {
	if (intersectionObserver !== null) {
		intersectionObserver.disconnect();
		intersectionObserver = null;
	}
	if (mode === "infinite" && el !== null) {
		setupInfiniteScroll();
	}
});

// ── Set Owner ─────────────────────────────────────────────────────────────────

function onTransferred(): void {
	selectedIds.value = [];
	load();
}

// ── Delete ────────────────────────────────────────────────────────────────────

function onDeleted(): void {
	selectedIds.value = [];
	load(1);
}

// ── Edit Fields ───────────────────────────────────────────────────────────────

function onPatched(): void {
	load();
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────

onMounted(() => {
	load(1);
});

onUnmounted(() => {
	if (intersectionObserver !== null) {
		intersectionObserver.disconnect();
	}
});
</script>
