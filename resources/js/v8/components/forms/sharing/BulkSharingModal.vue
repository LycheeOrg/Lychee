<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div class="flex flex-col items-center gap-4">
				<div class="text-highlighted">
					{{ $t("sharing.bulk_share_instr") }}
				</div>
				<div class="w-full flex gap-2">
					<USelectMenu
						v-model="selectedAlbums"
						:items="targetAlbums"
						label-key="original"
						value-key="id"
						multiple
						class="w-full"
						:placeholder="$t('sharing.albums')"
					>
						<template #item-label="{ item }">{{ trim(item.original) }}</template>
					</USelectMenu>
					<USelectMenu
						v-model="selectedUserMenuValues"
						:items="userMenuItems"
						label-key="name"
						multiple
						class="w-full"
						:loading="usersGroupsList === undefined"
						:placeholder="$t('sharing.users')"
					>
						<template #item-leading="{ item }">
							<UIcon v-if="(item as unknown as UserOrGroup).type === 'group'" name="prime:users" />
						</template>
					</USelectMenu>
				</div>

				<div class="w-1/2 flex justify-around items-center">
					<UTooltip :text="$t('sharing.grants.read')"><UIcon name="prime:eye" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.original')"><UIcon name="prime:window-maximize" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.download')"><UIcon name="prime:cloud-download" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.upload')"><UIcon name="prime:upload" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.edit')"><UIcon name="prime:file-edit" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.delete')"><UIcon name="prime:trash" /></UTooltip>
				</div>
				<div class="w-1/2 flex items-center justify-around">
					<UCheckbox v-model="grantsReadAccess" disabled />
					<UCheckbox v-model="grantsFullPhotoAccess" />
					<UCheckbox v-model="grantsDownload" />
					<UCheckbox v-model="grantsUpload" />
					<UCheckbox v-model="grantsEdit" />
					<UCheckbox v-model="grantsDelete" />
				</div>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="closeCallback">{{
					$t("dialogs.button.cancel")
				}}</UButton>
				<UButton
					:disabled="!selectedAlbums.length || !selectedUsersOrGroups.length"
					color="success"
					icon="prime:user-plus"
					class="flex-1 justify-center"
					@click="create"
				>
					{{ $t("sharing.share") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import { onMounted } from "vue";
import SharingService from "@/services/sharing-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { UserOrGroup, useUsersAndGroupStore } from "@/stores/UsersAndGroupsState";
import type { SelectMenuItem } from "@nuxt/ui";

const visible = defineModel("visible", { default: false });
const lycheeStore = useLycheeStateStore();
const usersAndGroupsStore = useUsersAndGroupStore();
const { usersGroupsList } = storeToRefs(usersAndGroupsStore);
const toast = useAppToast();
const emits = defineEmits<{
	createdPermission: [];
}>();

const targetAlbums = ref<App.Http.Resources.Models.TargetAlbumResource[] | undefined>(undefined);
const selectedAlbums = ref<string[]>([]);
const selectedUsersOrGroups = ref<UserOrGroup[]>([]);

// USelectMenu's generic item type reserves `type` for "label" | "item" | "separator";
// UserOrGroup's own `type` field ("user" | "group") collides structurally, so bind through
// an opaque cast rather than renaming the shared `UserOrGroup` type (used by v7 too).
const userMenuItems = computed(() => usersGroupsList.value as unknown as SelectMenuItem[] | undefined);
const selectedUserMenuValues = computed<SelectMenuItem[]>({
	get: () => selectedUsersOrGroups.value as unknown as SelectMenuItem[],
	set: (v) => {
		selectedUsersOrGroups.value = v as unknown as UserOrGroup[];
	},
});

const grantsFullPhotoAccess = ref(false);
const grantsDownload = ref(false);
const grantsUpload = ref(false);
const grantsEdit = ref(false);
const grantsDelete = ref(false);
const grantsReadAccess = ref(true);

function trim(str: string) {
	if (str.length > 20) {
		return str.replace(/^\s+|\s+$/g, "").substring(0, 20) + "...";
	}
	return str;
}

function reset() {
	selectedUsersOrGroups.value = [];
	selectedAlbums.value = [];
	grantsFullPhotoAccess.value = false;
	grantsDownload.value = false;
	grantsUpload.value = false;
	grantsEdit.value = false;
	grantsDelete.value = false;
}

function closeCallback() {
	reset();
	visible.value = false;
}

function loadAlbums() {
	SharingService.listAlbums().then((response) => {
		targetAlbums.value = response.data;
	});
}

function create() {
	if (selectedUsersOrGroups.value === undefined || selectedUsersOrGroups.value.length === 0) {
		return;
	}
	if (selectedAlbums.value === undefined || selectedAlbums.value.length === 0) {
		return;
	}

	const userIds = selectedUsersOrGroups.value.filter((user) => user.type === "user").map((user) => user.id);
	const groupIds = selectedUsersOrGroups.value.filter((group) => group.type === "group").map((group) => group.id);

	const data = {
		album_ids: selectedAlbums.value,
		user_ids: userIds,
		group_ids: groupIds,
		grants_download: grantsDownload.value,
		grants_full_photo_access: grantsFullPhotoAccess.value,
		grants_upload: grantsUpload.value,
		grants_edit: grantsEdit.value,
		grants_delete: grantsDelete.value,
	};

	SharingService.add(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("sharing.permission_created"), life: 3000 });
		emits("createdPermission");
		closeCallback();
	});
}

onMounted(async () => {
	await lycheeStore.load();
	await usersAndGroupsStore.load();
	loadAlbums();
});
</script>
