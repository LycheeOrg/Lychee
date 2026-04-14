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

		<!-- Filter + selection bar -->
		<div class="flex flex-wrap items-center gap-2 mb-3">
			<InputText
				v-model="search"
				size="small"
				:placeholder="$t('bulk_album_edit.filter_placeholder')"
				class="flex-1 min-w-48"
				@input="onSearchInput"
			/>

			<Select v-model="perPage" :options="perPageOptions" class="w-24" size="small" @change="load(1)" />

			<span class="text-muted-color text-sm">
				{{ trans_choice("bulk_album_edit.total_selected", selectedIds.length, { n: String(selectedIds.length) }) }}
			</span>

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
		<div v-else>
			<DataTable
				:value="albums"
				class="w-full text-sm"
				scrollable
				scroll-height="calc(100vh - 260px)"
				data-key="id"
				:selection="selectedRows"
				selection-mode="multiple"
				@row-select="onRowSelect"
				@row-unselect="onRowUnselect"
				@row-select-all="onSelectAll"
				@row-unselect-all="onUnselectAll"
			>
				<Column selection-mode="multiple" header-class="w-12" />

				<Column field="title" :header="$t('bulk_album_edit.col_title')">
					<template #body="{ data }">
						<span :style="indentStyle(data)" class="whitespace-nowrap">{{ data.title }}</span>
					</template>
				</Column>

				<Column field="owner_name" :header="$t('bulk_album_edit.col_owner')" class="w-32" />

				<Column :header="$t('bulk_album_edit.col_is_public')" header-class="text-center w-20">
					<template #body="{ data }">
						<div class="flex justify-center">
							<i :class="data.is_public ? 'pi pi-lock-open text-green-500' : 'pi pi-lock text-muted-color'" />
						</div>
					</template>
				</Column>

				<Column :header="$t('bulk_album_edit.col_is_nsfw')" header-class="text-center w-16">
					<template #body="{ data }">
						<div class="flex justify-center">
							<i v-if="data.is_nsfw" class="pi pi-exclamation-triangle text-orange-500" />
						</div>
					</template>
				</Column>

				<Column field="license" :header="$t('bulk_album_edit.col_license')" class="w-28">
					<template #body="{ data }">
						<span class="text-muted-color text-xs">{{ data.license ?? "—" }}</span>
					</template>
				</Column>

				<Column :header="$t('bulk_album_edit.col_created_at')" class="w-32">
					<template #body="{ data }">
						<span class="text-muted-color text-xs">{{ formatDate(data.created_at) }}</span>
					</template>
				</Column>
			</DataTable>

			<!-- Pagination -->
			<Paginator
				:rows="perPage"
				:total-records="total"
				:first="(currentPage - 1) * perPage"
				:rows-per-page-options="perPageOptions"
				class="mt-2"
				@page="onPage"
			/>
		</div>
	</div>
</template>

<script lang="ts">
export default { name: "BulkAlbumEdit" };
</script>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { trans_choice } from "laravel-vue-i18n";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Column from "primevue/column";
import DataTable, {
	type DataTableRowSelectAllEvent,
	type DataTableRowSelectEvent,
	type DataTableRowUnselectAllEvent,
	type DataTableRowUnselectEvent,
} from "primevue/datatable";
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
const selectedRows = ref<BulkAlbumResource[]>([]);

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

function indentStyle(row: BulkAlbumResource): string {
	// Compute depth from _lft position (approximation via nesting depth in sorted list).
	// We don't have parent_id in this resource, so we use a simple approach:
	// Find how many ancestors exist by scanning albums with _lft < row._lft and _rgt > row._rgt.
	const depth = albums.value.filter((a) => a._lft < row._lft && a._rgt > row._rgt).length;
	return depth > 0 ? `padding-left: ${depth * 1.25}rem` : "";
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
			albums.value = response.data.data;
			total.value = response.data.total;
			currentPage.value = response.data.current_page;
			// Keep selectedRows in sync (remove rows no longer on this page)
			const pageIds = new Set(albums.value.map((a) => a.id));
			selectedRows.value = selectedRows.value.filter((r) => selectedIds.value.includes(r.id) && pageIds.has(r.id));
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
		load(1);
	}, 350);
}

// ── Selection ─────────────────────────────────────────────────────────────────

function onRowSelect(event: DataTableRowSelectEvent): void {
	const id = (event.data as BulkAlbumResource).id;
	if (!selectedIds.value.includes(id)) {
		selectedIds.value = [...selectedIds.value, id];
		selectedRows.value = [...selectedRows.value, event.data as BulkAlbumResource];
	}
}

function onRowUnselect(event: DataTableRowUnselectEvent): void {
	const id = (event.data as BulkAlbumResource).id;
	selectedIds.value = selectedIds.value.filter((i) => i !== id);
	selectedRows.value = selectedRows.value.filter((r) => r.id !== id);
}

function onSelectAll(event: DataTableRowSelectAllEvent): void {
	const newIds: string[] = [];
	const newRows: BulkAlbumResource[] = [];
	(event.data as BulkAlbumResource[]).forEach((row) => {
		if (!selectedIds.value.includes(row.id)) {
			newIds.push(row.id);
			newRows.push(row);
		}
	});
	selectedIds.value = [...selectedIds.value, ...newIds];
	selectedRows.value = [...selectedRows.value, ...newRows];
}

function onUnselectAll(_event: DataTableRowUnselectAllEvent): void {
	// When unselecting all on current page, remove page IDs from selection
	const pageIds = new Set(albums.value.map((r) => r.id));
	selectedIds.value = selectedIds.value.filter((id) => !pageIds.has(id));
	selectedRows.value = selectedRows.value.filter((r) => !pageIds.has(r.id));
}

// ── Pagination ────────────────────────────────────────────────────────────────

function onPage(event: PageState): void {
	currentPage.value = event.page + 1;
	perPage.value = event.rows;
	load();
}

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
			selectedRows.value = [];
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
			selectedRows.value = [];
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
</script>
