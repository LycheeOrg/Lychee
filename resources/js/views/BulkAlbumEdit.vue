<template>
	<!-- Set Owner Dialog -->
	<Dialog v-model:visible="isSetOwnerVisible" :header="$t('bulk_album_edit.set_owner_title')" modal class="w-full max-w-md">
		<p class="mb-4 text-muted-color text-sm">{{ $t("bulk_album_edit.set_owner_description") }}</p>
		<Select
			v-model="selectedOwner"
			:options="users"
			filter
			option-label="username"
			:placeholder="$t('bulk_album_edit.set_owner_select_user')"
			class="w-full"
		/>
		<template #footer>
			<Button :label="$t('bulk_album_edit.cancel')" severity="secondary" text @click="isSetOwnerVisible = false" />
			<Button :label="$t('bulk_album_edit.transfer')" :disabled="selectedOwner === undefined" @click="doSetOwner" />
		</template>
	</Dialog>

	<!-- Delete Confirmation Dialog -->
	<Dialog v-model:visible="isDeleteVisible" :header="$t('bulk_album_edit.delete_title')" modal class="w-full max-w-md">
		<p class="mb-2 text-muted-color text-sm">
			{{ trans_choice("bulk_album_edit.delete_confirm", selectedIds.length, { count: String(selectedIds.length) }) }}
		</p>
		<template #footer>
			<Button :label="$t('bulk_album_edit.cancel')" severity="secondary" text @click="isDeleteVisible = false" />
			<Button :label="$t('bulk_album_edit.confirm_delete')" severity="danger" @click="doDelete" />
		</template>
	</Dialog>

	<!-- Edit Fields Dialog -->
	<Dialog v-model:visible="isEditFieldsVisible" :header="$t('bulk_album_edit.edit_fields_title')" modal class="w-full max-w-2xl">
		<p class="mb-4 text-muted-color text-sm">{{ $t("bulk_album_edit.edit_fields_description") }}</p>

		<div class="grid grid-cols-1 gap-3">
			<p class="font-semibold text-sm">{{ $t("bulk_album_edit.section_metadata") }}</p>

			<div v-for="field in textFields" :key="field.key" class="flex items-start gap-3">
				<Checkbox v-model="editEnabled[field.key]" :binary="true" class="mt-1 flex-shrink-0" />
				<div class="flex-1">
					<label class="block text-sm mb-1">{{ $t("bulk_album_edit." + field.label) }}</label>
					<InputText v-model="editTextValues[field.key]" :disabled="!editEnabled[field.key]" class="w-full" size="small" />
				</div>
			</div>

			<div v-for="field in enumFields" :key="field.key" class="flex items-start gap-3">
				<Checkbox v-model="editEnabled[field.key]" :binary="true" class="mt-1 flex-shrink-0" />
				<div class="flex-1">
					<label class="block text-sm mb-1">{{ $t("bulk_album_edit." + field.label) }}</label>
					<Select
						v-model="editEnumValues[field.key]"
						:options="field.options"
						:disabled="!editEnabled[field.key]"
						option-label="label"
						option-value="value"
						show-clear
						class="w-full"
						size="small"
					/>
				</div>
			</div>

			<p class="font-semibold text-sm mt-2">{{ $t("bulk_album_edit.section_visibility") }}</p>

			<div v-for="field in boolFields" :key="field.key" class="flex items-center gap-3">
				<Checkbox v-model="editEnabled[field.key]" :binary="true" class="flex-shrink-0" />
				<label class="text-sm flex-1">{{ $t("bulk_album_edit." + field.label) }}</label>
				<ToggleSwitch v-model="editBoolValues[field.key]" :disabled="!editEnabled[field.key]" />
			</div>
		</div>

		<template #footer>
			<Button :label="$t('bulk_album_edit.cancel')" severity="secondary" text @click="isEditFieldsVisible = false" />
			<Button :label="$t('bulk_album_edit.apply')" :disabled="!hasAnyEnabled" @click="doEditFields" />
		</template>
	</Dialog>

	<!-- Toolbar -->
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("bulk_album_edit.title") }}
		</template>
	</Toolbar>

	<div class="max-w-7xl mx-auto mt-4 px-2">
		<p class="text-center text-muted-color text-sm mb-4">{{ $t("bulk_album_edit.description") }}</p>

		<!-- Warning -->
		<Message severity="warn" :closable="false" class="mb-4">{{ $t("bulk_album_edit.warning") }}</Message>

		<!-- Filter + controls bar -->
		<div class="flex flex-wrap items-center gap-2 mb-3">
			<InputText
				v-model="search"
				size="small"
				:placeholder="$t('bulk_album_edit.filter_placeholder')"
				class="flex-1 min-w-48"
				@input="onSearchInput"
			/>

			<Select v-model="perPage" :options="perPageOptions" class="w-24" size="small" @change="load(1)" />

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
				v-if="selectedIds.length > 0"
				size="small"
				severity="secondary"
				:label="$t('bulk_album_edit.action_set_owner')"
				icon="pi pi-user"
				class="border-none"
				@click="openSetOwner"
			/>
			<Button
				v-if="selectedIds.length > 0"
				size="small"
				:label="$t('bulk_album_edit.action_edit_fields')"
				icon="pi pi-pencil"
				class="border-none"
				@click="openEditFields"
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
					<tr class="border-b border-surface-200 text-left">
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
						<th class="p-2 w-14 text-center">{{ $t("bulk_album_edit.col_grants_upload") }}</th>
						<th class="p-2 w-32 text-left">{{ $t("bulk_album_edit.col_created_at") }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(album, idx) in albums" :key="album.id" class="border-b border-surface-100 hover:bg-surface-50">
						<td class="p-2 text-center">
							<Checkbox :model-value="selectedIds.includes(album.id)" :binary="true" @update:model-value="toggleRow(album.id)" />
						</td>
						<td class="p-2 whitespace-nowrap">
							<span :style="`padding-left: ${albumDepths[idx] * 1.25}rem`">
								<span v-if="albumDepths[idx] > 0" class="text-muted-color mr-1">└─</span>{{ album.title }}
							</span>
						</td>
						<td class="p-2 text-muted-color text-xs w-32">{{ album.owner_name }}</td>
						<td class="p-2 text-muted-color text-xs text-center w-28">{{ album.license ?? "—" }}</td>
						<td class="p-2 text-center w-14">
							<ToggleSwitch
								:model-value="album.is_nsfw"
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
						<td class="p-2 text-center w-14">
							<ToggleSwitch
								:model-value="album.grants_upload"
								:disabled="!album.is_public"
								@update:model-value="(val) => onInlineToggle(album.id, 'grants_upload', val as boolean)"
							/>
						</td>
						<td class="p-2 text-muted-color text-xs w-32">{{ formatDate(album.created_at) }}</td>
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
	</div>
</template>

<script lang="ts">
export default { name: "BulkAlbumEdit" };
</script>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import { trans_choice } from "laravel-vue-i18n";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import Message from "primevue/message";
import Paginator, { type PageState } from "primevue/paginator";
import ProgressSpinner from "primevue/progressspinner";
import Select from "primevue/select";
import ToggleSwitch from "primevue/toggleswitch";
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import BulkAlbumEditService, { type BulkAlbumResource } from "@/services/bulk-album-edit-service";
import UsersService from "@/services/users-service";

const toast = useToast();

// ── State ─────────────────────────────────────────────────────────────────────

const albums = ref<BulkAlbumResource[]>([]);
const loading = ref(false);
const search = ref("");
const currentPage = ref(1);
const perPage = ref(50);
const total = ref(0);
const perPageOptions = [25, 50, 100];

const selectedIds = ref<string[]>([]);

const paginationMode = ref<"numbered" | "infinite">("numbered");
const sentinel = ref<HTMLElement | null>(null);
let intersectionObserver: IntersectionObserver | null = null;

// -- dialogs --
const isSetOwnerVisible = ref(false);
const isDeleteVisible = ref(false);
const isEditFieldsVisible = ref(false);

const users = ref<App.Http.Resources.Models.LightUserResource[]>([]);
const selectedOwner = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);

// Edit Fields state
const editEnabled = ref<Record<string, boolean>>({});
const editTextValues = ref<Record<string, string | null>>({});
const editEnumValues = ref<Record<string, string | null>>({});
const editBoolValues = ref<Record<string, boolean>>({});

const textFields = [
	{ key: "description", label: "field_description" },
	{ key: "copyright", label: "field_copyright" },
];

const licenseOptions = [
	{ label: "None", value: "none" },
	{ label: "Reserved", value: "reserved" },
	{ label: "CC0", value: "CC0" },
	{ label: "CC-BY 4.0", value: "CC-BY-4.0" },
	{ label: "CC-BY-ND 4.0", value: "CC-BY-ND-4.0" },
	{ label: "CC-BY-SA 4.0", value: "CC-BY-SA-4.0" },
	{ label: "CC-BY-NC 4.0", value: "CC-BY-NC-4.0" },
	{ label: "CC-BY-NC-ND 4.0", value: "CC-BY-NC-ND-4.0" },
	{ label: "CC-BY-NC-SA 4.0", value: "CC-BY-NC-SA-4.0" },
];

const photoLayoutOptions = [
	{ label: "Square", value: "square" },
	{ label: "Justified", value: "justified" },
	{ label: "Masonry", value: "masonry" },
	{ label: "Grid", value: "grid" },
];

const photoSortColOptions = [
	{ label: "Created At", value: "created_at" },
	{ label: "Taken At", value: "taken_at" },
	{ label: "Title", value: "title" },
	{ label: "Rating", value: "rating_avg" },
	{ label: "Type", value: "type" },
];

const albumSortColOptions = [
	{ label: "Created At", value: "created_at" },
	{ label: "Min Taken At", value: "min_taken_at" },
	{ label: "Max Taken At", value: "max_taken_at" },
	{ label: "Title", value: "title" },
];

const sortOrderOptions = [
	{ label: "Ascending", value: "ASC" },
	{ label: "Descending", value: "DESC" },
];

const aspectRatioOptions = [
	{ label: "5:4", value: "5/4" },
	{ label: "3:2", value: "3/2" },
	{ label: "1:1", value: "1/1" },
	{ label: "2:3", value: "2/3" },
	{ label: "4:5", value: "4/5" },
	{ label: "16:9", value: "16/9" },
];

const timelineOptions = [
	{ label: "Default", value: "default" },
	{ label: "Disabled", value: "disabled" },
	{ label: "Year", value: "year" },
	{ label: "Month", value: "month" },
	{ label: "Day", value: "day" },
];

const photoTimelineOptions = [...timelineOptions, { label: "Hour", value: "hour" }];

const enumFields = [
	{ key: "license", label: "field_license", options: licenseOptions },
	{ key: "photo_layout", label: "field_photo_layout", options: photoLayoutOptions },
	{ key: "photo_sorting_col", label: "field_photo_sorting_col", options: photoSortColOptions },
	{ key: "photo_sorting_order", label: "field_photo_sorting_order", options: sortOrderOptions },
	{ key: "album_sorting_col", label: "field_album_sorting_col", options: albumSortColOptions },
	{ key: "album_sorting_order", label: "field_album_sorting_order", options: sortOrderOptions },
	{ key: "album_thumb_aspect_ratio", label: "field_album_thumb_aspect_ratio", options: aspectRatioOptions },
	{ key: "album_timeline", label: "field_album_timeline", options: timelineOptions },
	{ key: "photo_timeline", label: "field_photo_timeline", options: photoTimelineOptions },
];

const boolFields = [
	{ key: "is_nsfw", label: "field_is_nsfw" },
	{ key: "is_public", label: "field_is_public" },
	{ key: "is_link_required", label: "field_is_link_required" },
	{ key: "grants_full_photo_access", label: "field_grants_full_photo_access" },
	{ key: "grants_download", label: "field_grants_download" },
	{ key: "grants_upload", label: "field_grants_upload" },
];

const hasAnyEnabled = computed(() => Object.values(editEnabled.value).some((v) => v === true));

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

function openSetOwner(): void {
	selectedOwner.value = undefined;
	if (users.value.length === 0) {
		UsersService.get()
			.then((r) => {
				users.value = r.data;
			})
			.catch(() => {
				toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_load_users", life: 3000 });
			});
	}
	isSetOwnerVisible.value = true;
}

function doSetOwner(): void {
	if (selectedOwner.value === undefined) {
		return;
	}
	BulkAlbumEditService.setOwner({ album_ids: selectedIds.value, owner_id: selectedOwner.value.id })
		.then(() => {
			toast.add({ severity: "success", summary: "OK", detail: "bulk_album_edit.success_set_owner", life: 3000 });
			isSetOwnerVisible.value = false;
			selectedIds.value = [];
			load();
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_set_owner", life: 3000 });
		});
}

// ── Delete ────────────────────────────────────────────────────────────────────

function doDelete(): void {
	BulkAlbumEditService.deleteAlbums(selectedIds.value)
		.then(() => {
			toast.add({ severity: "success", summary: "OK", detail: "bulk_album_edit.success_delete", life: 3000 });
			isDeleteVisible.value = false;
			selectedIds.value = [];
			load(1);
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_delete", life: 3000 });
		});
}

// ── Edit Fields ───────────────────────────────────────────────────────────────

function openEditFields(): void {
	// Reset edit state
	const enabled: Record<string, boolean> = {};
	const textVals: Record<string, string | null> = {};
	const enumVals: Record<string, string | null> = {};
	const boolVals: Record<string, boolean> = {};
	textFields.forEach((f) => {
		enabled[f.key] = false;
		textVals[f.key] = null;
	});
	enumFields.forEach((f) => {
		enabled[f.key] = false;
		enumVals[f.key] = null;
	});
	boolFields.forEach((f) => {
		enabled[f.key] = false;
		boolVals[f.key] = false;
	});
	editEnabled.value = enabled;
	editTextValues.value = textVals;
	editEnumValues.value = enumVals;
	editBoolValues.value = boolVals;
	isEditFieldsVisible.value = true;
}

function doEditFields(): void {
	const payload: Record<string, unknown> = { album_ids: selectedIds.value };
	textFields.forEach((f) => {
		if (editEnabled.value[f.key] === true) {
			payload[f.key] = editTextValues.value[f.key];
		}
	});
	enumFields.forEach((f) => {
		if (editEnabled.value[f.key] === true) {
			payload[f.key] = editEnumValues.value[f.key];
		}
	});
	boolFields.forEach((f) => {
		if (editEnabled.value[f.key] === true) {
			payload[f.key] = editBoolValues.value[f.key];
		}
	});

	BulkAlbumEditService.patchAlbums(payload as Parameters<typeof BulkAlbumEditService.patchAlbums>[0])
		.then(() => {
			toast.add({ severity: "success", summary: "OK", detail: "bulk_album_edit.success_patch", life: 3000 });
			isEditFieldsVisible.value = false;
			load();
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_patch", life: 3000 });
		});
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
