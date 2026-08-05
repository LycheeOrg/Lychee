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
	<UMain class="p-4">
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
				variant="solid"
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
			<LycheeLoadingIcon fast />
		</div>

		<!-- Table -->
		<div v-else>
			<UTable
				:data="albums"
				:columns="columns"
				sticky
				:ui="{ base: 'table-fixed', td: 'px-4 py-0', tr: 'hover:bg-primary/5 border-none' }"
				:virtualize="{ estimateSize: 28, overscan: 50 }"
				class="max-h-[65vh] text-sm"
			>
				<template #select-header>
					<UCheckbox
						:model-value="isPageAllSelected"
						@update:model-value="(v: boolean | 'indeterminate') => toggleSelectPage(v === true)"
					/>
				</template>
				<template #select-cell="{ row }">
					<UCheckbox :model-value="selectedIds.includes(row.original.id)" @update:model-value="() => toggleRow(row.original.id)" />
				</template>
				<template #title-cell="{ row }">
					<span :style="`padding-left: ${(albumDepths[row.index] - 1) * 1.25}rem`" class="inline-flex items-center gap-1">
						<span v-if="albumDepths[row.index] > 0" class="text-muted mr-1">└─</span>
						<UInput
							v-if="editingTitleId === row.original.id"
							v-model="editingTitleValue"
							size="sm"
							class="w-64"
							@blur="saveTitle(row.original)"
							@keyup.enter="saveTitle(row.original)"
							@keyup.escape="cancelEditTitle"
						/>
						<span v-else class="cursor-text hover:text-primary" @click="startEditTitle(row.original)">{{ row.original.title }}</span>
					</span>
				</template>
				<template #is_nsfw-cell="{ row }">
					<USwitch
						size="xs"
						:model-value="row.original.is_nsfw"
						color="error"
						@update:model-value="(val: boolean) => onInlineToggle(row.original.id, 'is_nsfw', val)"
					/>
				</template>
				<template #is_public-cell="{ row }">
					<USwitch
						size="xs"
						:model-value="row.original.is_public"
						@update:model-value="(val: boolean) => onInlineToggle(row.original.id, 'is_public', val)"
					/>
				</template>
				<template #is_link_required-cell="{ row }">
					<USwitch
						size="xs"
						:model-value="row.original.is_link_required"
						:disabled="!row.original.is_public"
						@update:model-value="(val: boolean) => onInlineToggle(row.original.id, 'is_link_required', val)"
					/>
				</template>
				<template #grants_download-cell="{ row }">
					<USwitch
						size="xs"
						:model-value="row.original.grants_download"
						:disabled="!row.original.is_public"
						@update:model-value="(val: boolean) => onInlineToggle(row.original.id, 'grants_download', val)"
					/>
				</template>
				<template #grants_full_photo_access-cell="{ row }">
					<USwitch
						size="xs"
						:model-value="row.original.grants_full_photo_access"
						:disabled="!row.original.is_public"
						@update:model-value="(val: boolean) => onInlineToggle(row.original.id, 'grants_full_photo_access', val)"
					/>
				</template>
				<template #grants_upload-cell="{ row }">
					<USwitch
						size="xs"
						:model-value="row.original.grants_upload"
						:disabled="!row.original.is_public || !is_se_enabled"
						color="error"
						@update:model-value="(val: boolean) => onInlineToggle(row.original.id, 'grants_upload', val)"
					/>
				</template>
				<template #photo_sorting-cell="{ row }">
					<div class="flex items-center justify-center gap-1">
						<USelectMenu
							v-if="editingSortingId === row.original.id + '_photo'"
							:model-value="findOption(photoSortingColumnsOptions, row.original.photo_sorting_col)"
							:items="photoSortingColumnsOptions"
							label-key="label"
							size="sm"
							class="text-xs w-32"
							@update:model-value="
								(v: SelectOption<App.Enum.ColumnSortingPhotoType> | undefined) =>
									savePhotoSortingCol(row.original.id, v?.value ?? null)
							"
							@update:open="(o: boolean) => !o && closeEditSorting()"
						>
							<template #default="{ modelValue }">{{ selectedLabel(modelValue) }}</template>
							<template #item-label="{ item }">{{ $t(item.label) }}</template>
						</USelectMenu>
						<span
							v-else
							class="cursor-text text-xs hover:text-primary w-32 text-center"
							@click="startEditPhotoSorting(row.original.id)"
							>{{
								photoSortingColumnsOptions.find((o) => o.value === row.original.photo_sorting_col)?.label !== undefined
									? $t(photoSortingColumnsOptions.find((o) => o.value === row.original.photo_sorting_col)!.label)
									: "—"
							}}</span
						>
						<UButton
							size="sm"
							variant="ghost"
							color="neutral"
							:icon="row.original.photo_sorting_order === 'DESC' ? 'lucide:arrow-down-wide-narrow' : 'lucide:arrow-up-wide-narrow'"
							:disabled="row.original.photo_sorting_col === null"
							@click="
								onInlineSortingChange(
									row.original.id,
									'photo_sorting_order',
									row.original.photo_sorting_order === 'DESC' ? 'ASC' : 'DESC',
								)
							"
						/>
					</div>
				</template>
				<template #album_sorting-cell="{ row }">
					<div class="flex items-center justify-center gap-1">
						<USelectMenu
							v-if="editingSortingId === row.original.id + '_album'"
							:model-value="findOption(albumSortingColumnsOptions, row.original.album_sorting_col)"
							:items="albumSortingColumnsOptions"
							label-key="label"
							size="sm"
							class="text-xs w-32"
							@update:model-value="
								(v: SelectOption<App.Enum.ColumnSortingAlbumType> | undefined) =>
									saveAlbumSortingCol(row.original.id, v?.value ?? null)
							"
							@update:open="(o: boolean) => !o && closeEditSorting()"
						>
							<template #default="{ modelValue }">{{ selectedLabel(modelValue) }}</template>
							<template #item-label="{ item }">{{ $t(item.label) }}</template>
						</USelectMenu>
						<span
							v-else
							class="cursor-text text-xs hover:text-primary w-32 text-center"
							@click="startEditAlbumSorting(row.original.id)"
							>{{
								albumSortingColumnsOptions.find((o) => o.value === row.original.album_sorting_col)?.label !== undefined
									? $t(albumSortingColumnsOptions.find((o) => o.value === row.original.album_sorting_col)!.label)
									: "—"
							}}</span
						>
						<UButton
							size="sm"
							variant="ghost"
							color="neutral"
							:icon="row.original.album_sorting_order === 'DESC' ? 'lucide:arrow-down-wide-narrow' : 'lucide:arrow-up-wide-narrow'"
							:disabled="row.original.album_sorting_col === null"
							@click="
								onInlineSortingChange(
									row.original.id,
									'album_sorting_order',
									row.original.album_sorting_order === 'DESC' ? 'ASC' : 'DESC',
								)
							"
						/>
					</div>
				</template>
				<template #actions-cell="{ row }">
					<UButton size="sm" variant="ghost" color="neutral" icon="lucide:pencil" @click="quickEditAlbum(row.original.id)" />
				</template>
				<template v-if="paginationMode === 'infinite'" #body-bottom>
					<tr>
						<td colspan="100">
							<div ref="sentinel" class="h-4" />
						</td>
					</tr>
				</template>
			</UTable>

			<!-- Pagination (numbered mode) -->
			<div v-if="paginationMode === 'numbered'" class="flex justify-center mt-2">
				<UPagination v-model:page="currentPage" :total="total" :items-per-page="perPage" @update:page="() => load()" />
			</div>
		</div>
	</UMain>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { trans, trans_choice } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import DeleteDialog from "@/v8/components/forms/gallery-dialogs/DeleteDialog.vue";
import BulkSetOwnerDialog from "@/v8/components/forms/bulk-album-edit/BulkSetOwnerDialog.vue";
import BulkEditFieldsDialog from "@/v8/components/forms/bulk-album-edit/BulkEditFieldsDialog.vue";
import BulkAlbumEditService, { type BulkAlbumResource } from "@/services/bulk-album-edit-service";
import AlbumService from "@/services/album-service";
import UsersService from "@/services/users-service";
import { photoSortingColumnsOptions, albumSortingColumnsOptions, type SelectOption } from "@/config/constants";
import { useLycheeStateStore } from "@/stores/LycheeState";
import type { TableColumn } from "@nuxt/ui";

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

const centered = { class: { th: "text-center", td: "text-center" } };

const columns = computed<TableColumn<BulkAlbumResource>[]>(() => {
	const cols: TableColumn<BulkAlbumResource>[] = [
		{ id: "select", meta: centered },
		{ id: "title", header: trans("bulk_album_edit.col_title") },
		{ id: "owner", accessorKey: "owner_name", header: trans("bulk_album_edit.col_owner") },
		{ id: "license", header: trans("bulk_album_edit.col_license"), cell: ({ row }) => row.original.license ?? "—", meta: centered },
		{ id: "is_nsfw", header: trans("bulk_album_edit.col_is_nsfw"), meta: centered },
		{ id: "is_public", header: trans("bulk_album_edit.col_is_public"), meta: centered },
		{ id: "is_link_required", header: trans("bulk_album_edit.col_is_link_required"), meta: centered },
		{ id: "grants_download", header: trans("bulk_album_edit.col_grants_download"), meta: centered },
		{ id: "grants_full_photo_access", header: trans("bulk_album_edit.col_grants_full_photo_access"), meta: centered },
	];

	if (is_se_enabled.value || is_se_preview_enabled.value) {
		cols.push({ id: "grants_upload", header: trans("bulk_album_edit.col_grants_upload"), meta: centered });
	}

	cols.push(
		{ id: "photo_sorting", header: trans("bulk_album_edit.col_photo_sorting"), meta: centered },
		{ id: "album_sorting", header: trans("bulk_album_edit.col_album_sorting"), meta: centered },
		{
			id: "created_at",
			header: trans("bulk_album_edit.col_created_at"),
			cell: ({ row }) => formatDate(row.original.created_at),
		},
		{ id: "actions", meta: centered },
	);

	return cols;
});

function findOption<T extends string>(options: SelectOption<T>[], value: string | null): SelectOption<T> | undefined {
	return options.find((o) => o.value === value);
}

function selectedLabel<T>(option: SelectOption<T> | undefined): string {
	// A single space (matching USelectMenu's own placeholder fallback) keeps the
	// trigger's line box at its normal height; an empty string collapses it.
	return option ? trans(option.label) : " ";
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
