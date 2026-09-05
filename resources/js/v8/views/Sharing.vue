<template>
	<BulkSharingModal v-model:visible="bulkSharingVisible" @created-permission="load" />
	<AlbumCreateShareDialog
		v-if="createShareAlbum"
		v-model:open="createShareOpen"
		:album="createShareAlbum"
		:filtered-users-ids="createShareFilteredIds"
		@created-permission="load"
	/>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("sharing.title") }}
	</UHeader>
	<UMain class="p-4">
		<p class="text-center text-muted text-sm mb-4">{{ $t("sharing.info") }}</p>

		<p class="lg:hidden text-center text-highlighted mt-12">{{ $t("sharing.screen_too_small") }}</p>

		<div class="hidden lg:block max-w-5xl mx-auto">
			<div class="flex flex-wrap items-center gap-2 mb-3">
				<UInput v-model="search" size="sm" :placeholder="$t('sharing.filter_placeholder')" class="flex-1 min-w-48" />
				<USwitch v-model="hideEmpty" :label="$t('sharing.hide_empty')" :ui="{ label: 'text-sm' }" />
				<UButton
					color="primary"
					variant="solid"
					:label="$t('sharing.bluk_share')"
					icon="lucide:user-plus"
					@click="
						() => {
							bulkSharingVisible = true;
						}
					"
				/>
			</div>

			<div v-if="loading" class="flex justify-center py-12">
				<LycheeLoadingIcon fast />
			</div>

			<div v-else>
				<UTable
					v-if="albumRows.length > 0"
					:data="albumRows"
					:columns="columns"
					sticky
					:ui="{ base: 'table-fixed', td: 'px-2 py-2 align-top', th: 'px-2' }"
					class="max-h-[calc(100vh-var(--ui-header-height))] text-sm"
				>
					<template #title-cell="{ row }">
						<span :style="`padding-left: ${(albumDepths[row.index] - 1) * 1.25}rem`" class="inline-flex items-center gap-1">
							<span v-if="albumDepths[row.index] > 0" class="text-muted mr-1">└─</span>
							<UButton
								:to="{ name: 'album', params: { albumId: row.original.id } }"
								target="_blank"
								size="xs"
								variant="ghost"
								color="neutral"
								icon="lucide:external-link"
								class="shrink-0"
							/>
							<span>{{ row.original.title }}</span>
						</span>
					</template>
					<template #shares-header>
						<div class="flex items-center gap-4">
							<div class="flex-1"></div>
							<div class="flex items-center gap-2">
								<UTooltip :text="$t('sharing.grants.read')"><UIcon name="lucide:eye" /></UTooltip>
								<UTooltip :text="$t('sharing.grants.original')"><UIcon name="lucide:app-window" /></UTooltip>
								<UTooltip :text="$t('sharing.grants.download')"><UIcon name="lucide:cloud-download" /></UTooltip>
								<UTooltip :text="$t('sharing.grants.upload')"><UIcon name="lucide:upload" /></UTooltip>
								<UTooltip :text="$t('sharing.grants.edit')"><UIcon name="lucide:file-edit" /></UTooltip>
								<UTooltip :text="$t('sharing.grants.delete')"><UIcon name="lucide:trash" /></UTooltip>
								<UTooltip :text="$t('dialogs.button.delete')"><UIcon name="lucide:user-minus" /></UTooltip>
							</div>
						</div>
					</template>
					<template #shares-cell="{ row }">
						<div v-if="row.original.permissions.length > 0" class="flex flex-col gap-1">
							<ShareLine
								v-for="perm in row.original.permissions"
								:key="perm.id ?? undefined"
								:perm="perm"
								:with-album="false"
								@delete="deletePermission"
							/>
						</div>
						<span v-else class="text-muted text-sm">{{ $t("sharing.no_data") }}</span>
					</template>
					<template #actions-cell="{ row }">
						<UButton size="xs" variant="soft" color="success" icon="lucide:plus" @click="openCreateShareDialog(row.original)" />
					</template>
				</UTable>
				<p v-else class="text-center text-highlighted">{{ $t("sharing.no_data") }}</p>
			</div>
		</div>
	</UMain>
</template>
<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import ShareLine from "@/v8/components/forms/sharing/ShareLine.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import BulkSharingModal from "@/v8/components/forms/sharing/BulkSharingModal.vue";
import AlbumCreateShareDialog from "@/v8/components/forms/album/AlbumCreateShareDialog.vue";
import SharingService from "@/services/sharing-service";
import AlbumListV3Service from "@/services/album-list-v3-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import type { TableColumn } from "@nuxt/ui";
import { type UserOrGroupId } from "@/stores/UsersAndGroupsState";

type Permission = App.Http.Resources.Models.AccessPermissionResource;
type AccessPermissionResource = App.Http.Resources.V3.AlbumAccessPermissionResource;

// One row per album (tree-ordered), each folding in its own zero-or-more
// permissions — mirrors BulkAlbumEdit.vue's one-row-per-album table, with a
// stacked ShareLine list standing in for BulkAlbumEdit's per-field columns.
type AlbumSharesRow = {
	id: string;
	title: string;
	_lft: number;
	_rgt: number;
	permissions: Permission[];
};

const toast = useAppToast();
const bulkSharingVisible = ref(false);
const loading = ref(false);
const search = ref("");
const hideEmpty = ref(false);

const fullAlbumRows = ref<AlbumSharesRow[]>([]);

const albumRows = computed<AlbumSharesRow[]>(() => {
	const term = search.value.trim().toLowerCase();
	return fullAlbumRows.value.filter((row) => {
		if (hideEmpty.value && row.permissions.length === 0) {
			return false;
		}
		return term === "" || row.title.toLowerCase().includes(term);
	});
});

/** O(n) depth computation using a stack of ancestor _rgt values (mirrors BulkAlbumEdit.vue). */
const albumDepths = computed<number[]>(() => {
	const depths: number[] = [];
	const stack: number[] = [];
	for (const row of albumRows.value) {
		while (stack.length > 0 && row._lft > stack[stack.length - 1]) {
			stack.pop();
		}
		depths.push(stack.length);
		stack.push(row._rgt);
	}
	return depths;
});

const columns: TableColumn<AlbumSharesRow>[] = [
	{ id: "title", header: trans("sharing.album_title"), meta: { class: { th: "w-1/2", td: "w-1/2" } } },
	{ id: "shares" },
	{ id: "actions", meta: { class: { th: "w-12 min-w-12 max-w-12 text-center", td: "w-12 min-w-12 max-w-12 text-center" } } },
];

// Rebuilds the flat per-permission object ShareLine already knows how to
// render from the Struct-of-Arrays response at index `i`.
function adaptPermissionRow(data: AccessPermissionResource, i: number): Permission {
	return {
		id: data.permission_ids[i],
		user_id: data.user_ids[i],
		user_group_id: data.group_ids[i],
		username: data.user_names[i],
		user_group_name: data.group_names[i],
		album_title: data.album_titles[i],
		album_id: data.album_ids[i],
		grants_full_photo_access: data.grants_full_photo_accesses[i] ?? false,
		grants_download: data.grants_downloads[i] ?? false,
		grants_upload: data.grants_uploads[i] ?? false,
		grants_edit: data.grants_edits[i] ?? false,
		grants_delete: data.grants_deletes[i] ?? false,
	};
}

// Rebuilds one row per album (tree-ordered, matching BulkAlbumEdit's approach) from
// the flat (album, permission) Struct-of-Arrays response, folding each album's
// permissions (zero, one, or many) into that album's own row.
function groupIntoAlbumRows(data: AccessPermissionResource): AlbumSharesRow[] {
	const rows: AlbumSharesRow[] = [];
	const byAlbumId = new Map<string, AlbumSharesRow>();

	data.album_ids.forEach((album_id, i) => {
		let row = byAlbumId.get(album_id);
		if (row === undefined) {
			row = { id: album_id, title: data.album_titles[i], _lft: data._lft[i], _rgt: data._rgt[i], permissions: [] };
			byAlbumId.set(album_id, row);
			rows.push(row);
		}
		if (data.permission_ids[i] !== null) {
			row.permissions.push(adaptPermissionRow(data, i));
		}
	});

	return rows;
}

function deletePermission(id: number) {
	SharingService.delete(id).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("sharing.permission_deleted"), life: 3000 });
		for (const row of fullAlbumRows.value) {
			const idx = row.permissions.findIndex((perm) => perm.id === id);
			if (idx !== -1) {
				row.permissions.splice(idx, 1);
				return;
			}
		}
	});
}

// ── Per-row "add a share" dialog ─────────────────────────────────────────────

const createShareOpen = ref(false);
const createShareAlbum = ref<{ id: string } | undefined>(undefined);
const createShareFilteredIds = ref<UserOrGroupId[]>([]);

function sharedUserIds(permissions: Permission[]): UserOrGroupId[] {
	return permissions.map((perm) => {
		if (perm.user_group_id !== null) {
			return { id: perm.user_group_id, type: "group" };
		}
		return { id: perm.user_id, type: "user" };
	}) as UserOrGroupId[];
}

function openCreateShareDialog(row: AlbumSharesRow) {
	createShareAlbum.value = { id: row.id };
	createShareFilteredIds.value = sharedUserIds(row.permissions);
	createShareOpen.value = true;
}

function load() {
	loading.value = true;
	AlbumListV3Service.getAccessPermissions()
		.then((response) => {
			fullAlbumRows.value = groupIntoAlbumRows(response.data);
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
			fullAlbumRows.value = [];
		})
		.finally(() => {
			loading.value = false;
		});
}

onMounted(() => {
	load();
});
</script>
