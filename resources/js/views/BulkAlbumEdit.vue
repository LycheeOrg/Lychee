<template>
	<!-- Set Owner Dialog -->
	<BulkSetOwnerDialog v-model:visible="isSetOwnerVisible" :album-ids="selectedIds" @transferred="onTransferred" />

	<!-- Delete Confirmation Dialog -->
	<DeleteDialog v-model:visible="isDeleteVisible" :album-ids="selectedIds" @deleted="onDeleted" />

	<!-- Edit Fields Dialog -->
	<BulkEditFieldsDialog v-model:visible="isEditFieldsVisible" :album-ids="selectedIds" @patched="onPatched" />

	<!-- Toolbar -->
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("bulk_album_edit.title") }}
		</template>
	</Toolbar>

	<Panel class="max-w-7xl mx-auto mt-4 border-none">
		<p class="text-center text-muted-color text-sm mb-4">{{ $t("bulk_album_edit.description") }}</p>
		<!-- Warning -->
		<p class="text-center text-muted-color-emphasis text-sm mb-4">{{ $t("bulk_album_edit.warning") }}</p>

		<!-- Filter + controls bar -->
		<div class="flex flex-wrap items-center gap-2 mb-3">
			<InputText
				v-model="search"
				size="small"
				:placeholder="$t('bulk_album_edit.filter_placeholder')"
				class="flex-1 min-w-48"
				@input="onSearchInput"
			/>

			<Select v-model="perPage" :options="perPageOptions" class="w-24 border-none" size="small" @change="load(1)" />

			<Button
				size="small"
				severity="secondary"
				:icon="paginationMode === 'numbered' ? 'pi pi-list' : 'pi pi-align-justify'"
				:label="$t(paginationMode === 'numbered' ? 'bulk_album_edit.mode_infinite' : 'bulk_album_edit.mode_paginated')"
				class="border-none"
				@click="togglePaginationMode"
			/>
		</div>

		<!-- Selection + action bar -->
		<div class="flex flex-wrap items-center gap-2 mb-3">
			<span class="text-muted-color text-sm">
				{{ trans_choice("bulk_album_edit.total_selected", selectedIds.length, { n: String(selectedIds.length) }) }}
			</span>

			<Button
				size="small"
				severity="secondary"
				:label="$t('bulk_album_edit.select_all_page')"
				class="border-none"
				@click="toggleSelectPage(!isPageAllSelected)"
			/>

			<Button
				size="small"
				severity="secondary"
				:label="$t('bulk_album_edit.select_all_matching')"
				class="border-none"
				@click="selectAllMatching"
			/>

			<Button
				v-if="selectedIds.length > 0"
				size="small"
				severity="danger"
				:label="$t('bulk_album_edit.action_delete')"
				icon="pi pi-trash"
				class="border-none"
				@click="isDeleteVisible = true"
			/>
			<Button
				v-if="selectedIds.length > 0 && numUsers > 1"
				size="small"
				severity="secondary"
				:label="$t('bulk_album_edit.action_set_owner')"
				icon="pi pi-user"
				class="border-none"
				@click="isSetOwnerVisible = true"
			/>
			<Button
				v-if="selectedIds.length > 0"
				size="small"
				:label="$t('bulk_album_edit.action_edit_fields')"
				icon="pi pi-pencil"
				class="border-none"
				@click="isEditFieldsVisible = true"
			/>
		</div>

		<!-- Loading state -->
		<div v-if="loading" class="flex justify-center py-12">
			<ProgressSpinner />
		</div>

		<!-- Table -->
		<div v-else class="overflow-x-auto">
			<table class="w-full text-sm border-collapse">
				<thead>
					<tr class="border-b border-surface-200 dark:border-surface-700 text-left">
						<th class="p-2 w-10 text-center">
							<Checkbox :model-value="isPageAllSelected" :binary="true" @update:model-value="toggleSelectPage" />
						</th>
						<th class="p-2">{{ $t("bulk_album_edit.col_title") }}</th>
						<th class="p-2 w-32">{{ $t("bulk_album_edit.col_owner") }}</th>
						<th class="p-2 w-28 text-center">{{ $t("bulk_album_edit.col_license") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_is_nsfw") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_is_public") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_is_link_required") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_grants_download") }}</th>
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_grants_full_photo_access") }}</th>
						<th v-if="is_se_enabled || is_se_preview_enabled" class="p-2 w-14 text-center text-red-500">{{ $t("bulk_album_edit.col_grants_upload") }}</th>
						<th class="p-2 w-52 text-center">{{ $t("bulk_album_edit.col_photo_sorting") }}</th>
						<th class="p-2 w-52 text-center">{{ $t("bulk_album_edit.col_album_sorting") }}</th>
						<th class="p-2 w-32 text-left">{{ $t("bulk_album_edit.col_created_at") }}</th>
						<th class="p-2 w-10"></th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(album, idx) in albums" :key="album.id" class="hover:bg-primary-emphasis/5">
						<td class="p-2 text-center">
							<Checkbox :model-value="selectedIds.includes(album.id)" :binary="true" @update:model-value="toggleRow(album.id)" />
						</td>
						<td class="p-2 whitespace-nowrap">
							<span :style="`padding-left: ${(albumDepths[idx]-1) * 1.25}rem`" class="inline-flex items-center gap-1">
								<span v-if="albumDepths[idx] > 0" class="text-muted-color mr-1">└─</span>
								<InputText
									v-if="editingTitleId === album.id"
									v-model="editingTitleValue"
									size="small"
									class="w-64"
									@blur="saveTitle(album)"
									@keyup.enter="saveTitle(album)"
									@keyup.escape="cancelEditTitle"
								/>
								<span v-else class="cursor-text hover:text-primary-500" @click="startEditTitle(album)">{{ album.title }}</span>
							</span>
						</td>
						<td class="p-2 text-muted-color text-xs w-32">{{ album.owner_name }}</td>
						<td class="p-2 text-muted-color text-xs text-center w-28">{{ album.license ?? "—" }}</td>
						<td class="p-2 text-center w-14">
							<ToggleSwitch
								:model-value="album.is_nsfw"
								style="--p-toggleswitch-checked-background: var(--p-red-800); --p-toggleswitch-checked-hover-background: var(--p-red-900); --p-toggleswitch-hover-background: var(--p-red-900);"
								@update:model-value="(val) => onInlineToggle(album.id, 'is_nsfw', val as boolean)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<ToggleSwitch
								:model-value="album.is_public"
								@update:model-value="(val) => onInlineToggle(album.id, 'is_public', val as boolean)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<ToggleSwitch
								:model-value="album.is_link_required"
								:disabled="!album.is_public"
								@update:model-value="(val) => onInlineToggle(album.id, 'is_link_required', val as boolean)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<ToggleSwitch
								:model-value="album.grants_download"
								:disabled="!album.is_public"
								@update:model-value="(val) => onInlineToggle(album.id, 'grants_download', val as boolean)"
							/>
						</td>
						<td class="p-2 text-center w-14">
							<ToggleSwitch
								:model-value="album.grants_full_photo_access"
								:disabled="!album.is_public"
								@update:model-value="(val) => onInlineToggle(album.id, 'grants_full_photo_access', val as boolean)"
							/>
						</td>
							<td v-if="is_se_enabled || is_se_preview_enabled" class="p-2 text-center w-14">
								<ToggleSwitch
									:model-value="album.grants_upload"
									:disabled="!album.is_public || !is_se_enabled"
									style="--p-toggleswitch-checked-background: var(--p-red-800); --p-toggleswitch-checked-hover-background: var(--p-red-900); --p-toggleswitch-hover-background: var(--p-red-900);"
								@update:model-value="(val) => onInlineToggle(album.id, 'grants_upload', val as boolean)"
							/>
						</td>
						<td class="py-2 text-center w-52">
							<div class="flex items-center justify-center gap-1">
								<Select
									v-if="editingSortingId === album.id + '_photo'"
									ref="activeSortingSelect"
									:model-value="album.photo_sorting_col"
									:options="photoSortingColumnsOptions"
									option-label="label"
									option-value="value"
									show-clear
									size="small"
									class="text-xs w-32 border-none"
									@update:model-value="(val) => savePhotoSortingCol(album.id, val)"
									@blur="closeEditSorting"
								>
									<template #option="slotProps">
										{{ $t(slotProps.option.label) }}
									</template>
								</Select>
								<span
									v-else
									class="cursor-text text-xs hover:text-primary-500 w-32 text-center"
									@click="startEditPhotoSorting(album.id)"
								>{{ photoSortingColumnsOptions.find((o) => o.value === album.photo_sorting_col)?.label !== undefined ? $t(photoSortingColumnsOptions.find((o) => o.value === album.photo_sorting_col)!.label) : '—' }}</span>
								<Button
									size="small"
									text
									:icon="album.photo_sorting_order === 'DESC' ? 'pi pi-sort-amount-down-alt' : 'pi pi-sort-amount-up-alt'"
									:disabled="album.photo_sorting_col === null"
									@click="onInlineSortingChange(album.id, 'photo_sorting_order', album.photo_sorting_order === 'DESC' ? 'ASC' : 'DESC')"
								/>
							</div>
						</td>
						<td class="py-2 text-center w-52">
							<div class="flex items-center justify-center gap-1">
								<Select
									v-if="editingSortingId === album.id + '_album'"
									ref="activeSortingSelect"
									:model-value="album.album_sorting_col"
									:options="albumSortingColumnsOptions"
									option-label="label"
									option-value="value"
									show-clear
									size="small"
									class="text-xs w-32 border-none"
									@update:model-value="(val) => saveAlbumSortingCol(album.id, val)"
									@blur="closeEditSorting"
								>
									<template #option="slotProps">
										{{ $t(slotProps.option.label) }}
									</template>
								</Select>
								<span
									v-else
									class="cursor-text text-xs hover:text-primary-500 w-32 text-center"
									@click="startEditAlbumSorting(album.id)"
								>{{ albumSortingColumnsOptions.find((o) => o.value === album.album_sorting_col)?.label !== undefined ? $t(albumSortingColumnsOptions.find((o) => o.value === album.album_sorting_col)!.label) : '—' }}</span>
								<Button
									size="small"
									text
									:icon="album.album_sorting_order === 'DESC' ? 'pi pi-sort-amount-down-alt' : 'pi pi-sort-amount-up-alt'"
									:disabled="album.album_sorting_col === null"
									@click="onInlineSortingChange(album.id, 'album_sorting_order', album.album_sorting_order === 'DESC' ? 'ASC' : 'DESC')"
								/>
							</div>
						</td>
						<td class="p-2 text-muted-color text-xs w-32">{{ formatDate(album.created_at) }}</td>
						<td class="p-2 w-10 text-center">
							<Button
								size="small"
								text
								icon="pi pi-pencil"
								@click="quickEditAlbum(album.id)"
							/>
						</td>
					</tr>
				</tbody>
			</table>

			<!-- Pagination (numbered mode) -->
			<Paginator
				v-if="paginationMode === 'numbered'"
				:rows="perPage"
				:total-records="total"
				:first="(currentPage - 1) * perPage"
				:rows-per-page-options="perPageOptions"
				class="mt-2"
				@page="onPage"
			/>

			<!-- Infinite scroll sentinel -->
			<div v-if="paginationMode === 'infinite'" ref="sentinel" class="h-4" />
		</div>
	</Panel>
</template>

<script lang="ts">
export default { name: "BulkAlbumEdit" };
</script>

<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { trans_choice } from "laravel-vue-i18n";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Panel from "primevue/panel";
import Paginator, { type PageState } from "primevue/paginator";
import ProgressSpinner from "primevue/progressspinner";
import Select from "primevue/select";
import ToggleSwitch from "primevue/toggleswitch";
import Toolbar from "primevue/toolbar";
import InputText from "@/components/forms/basic/InputText.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import DeleteDialog from "@/components/forms/gallery-dialogs/DeleteDialog.vue";
import BulkSetOwnerDialog from "@/components/forms/bulk-album-edit/BulkSetOwnerDialog.vue";
import BulkEditFieldsDialog from "@/components/forms/bulk-album-edit/BulkEditFieldsDialog.vue";
import BulkAlbumEditService, { type BulkAlbumResource } from "@/services/bulk-album-edit-service";
import AlbumService from "@/services/album-service";
import UsersService from "@/services/users-service";
import { photoSortingColumnsOptions, albumSortingColumnsOptions } from "@/config/constants";
import { useLycheeStateStore } from "@/stores/LycheeState";

const toast = useToast();

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
const activeSortingSelect = ref<Array<{ show: () => void }>>([]);

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
			if (paginationMode.value === "infinite" && page === undefined) {
				albums.value = [...albums.value, ...response.data.data];
			} else {
				albums.value = response.data.data;
			}
			total.value = response.data.total;
			currentPage.value = response.data.current_page;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_load", life: 3000 });
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
				toast.add({ severity: "warn", summary: "Warning", detail: "bulk_album_edit.cap_warning", life: 5000 });
			}
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_load", life: 3000 });
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
		toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_patch", life: 3000 });
	});
}

function cancelEditTitle(): void {
	editingTitleId.value = null;
}

// ── Sorting editing ────────────────────────────────────────────────────────────

function startEditPhotoSorting(albumId: string): void {
	editingSortingId.value = albumId + '_photo';
	nextTick(() => activeSortingSelect.value[0]?.show());
}

function startEditAlbumSorting(albumId: string): void {
	editingSortingId.value = albumId + '_album';
	nextTick(() => activeSortingSelect.value[0]?.show());
}

function closeEditSorting(): void {
	editingSortingId.value = null;
}

function savePhotoSortingCol(albumId: string, val: string | null): void {
	onInlineSortingChange(albumId, 'photo_sorting_col', val);
	editingSortingId.value = null;
}

function saveAlbumSortingCol(albumId: string, val: string | null): void {
	onInlineSortingChange(albumId, 'album_sorting_col', val);
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
		toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_patch", life: 3000 });
	});
}

function onInlineSortingChange(
	albumId: string,
	field: 'photo_sorting_col' | 'photo_sorting_order' | 'album_sorting_col' | 'album_sorting_order',
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
		toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_patch", life: 3000 });
	});
}

// ── Pagination ────────────────────────────────────────────────────────────────

function onPage(event: PageState): void {
	currentPage.value = event.page + 1;
	perPage.value = event.rows;
	load();
}

function togglePaginationMode(): void {
	if (paginationMode.value === "numbered") {
		paginationMode.value = "infinite";
	} else {
		paginationMode.value = "numbered";
		if (intersectionObserver !== null) {
			intersectionObserver.disconnect();
			intersectionObserver = null;
		}
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
