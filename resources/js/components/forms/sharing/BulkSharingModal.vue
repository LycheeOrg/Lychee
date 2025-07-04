<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div
				class="flex flex-col items-center gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative w-full md:w-2xl rounded-md text-muted-color pt-9"
			>
				<div class="text-muted-color-emphasis">
					{{ $t("sharing.bulk_share_instr") }}
				</div>
				<div class="w-full flex gap-2 px-9">
					<Listbox
						v-model="selectedAlbums"
						filter
						:options="targetAlbums"
						optionLabel="original"
						optionValue="id"
						dataKey="id"
						:multiple="true"
						:checkmark="true"
						:highlightOnSelect="false"
						class="w-full"
						:virtualScrollerOptions="{ itemSize: 28 }"
					>
						<template #header>
							<span class="text-muted-color-emphasis font-bold">{{ $t("sharing.albums") }}</span>
						</template>
						<template #empty>
							<span class="text-muted-color">{{ $t("sharing.no_albums") }}</span>
						</template>
						<template #option="slotProps">
							<div class="flex items-center">
								<div
									v-if="trim(slotProps.option.title) !== slotProps.option.original"
									v-tooltip.bottom="{ value: slotProps.option.title, pt: pt }"
								>
									{{ trim(slotProps.option.original) }}
								</div>
								<div v-else>{{ slotProps.option.original }}</div>
							</div>
						</template>
					</Listbox>
					<Listbox
						v-model="selectedUsersOrGroups"
						filter
						:options="usersGroupsList"
						optionLabel="name"
						dataKey="id"
						:multiple="true"
						:checkmark="true"
						:highlightOnSelect="false"
						class="w-full"
						:virtualScrollerOptions="{ itemSize: 28 }"
					>
						<template #header>
							<span class="text-muted-color-emphasis font-bold">{{ $t("sharing.users") }}</span>
						</template>
						<template #option="slotProps">
							<span class="w-full">
								<i class="pi pi-users ltr:mr-1 rtl:ml-1" v-if="slotProps.option.type === 'group'" />
								{{ slotProps.option.name }}
							</span>
						</template>
						<template #empty>
							<span class="text-muted-color">{{ $t("sharing.no_users") }}</span>
						</template>
					</Listbox>
				</div>

				<div class="w-1/2 flex justify-around items-center">
					<i class="pi pi-eye" v-tooltip.top="$t('sharing.grants.read')" />
					<i class="pi pi-window-maximize" v-tooltip.top="$t('sharing.grants.original')" />
					<i class="pi pi-cloud-download" v-tooltip.top="$t('sharing.grants.download')" />
					<i class="pi pi-upload" v-tooltip.top="$t('sharing.grants.upload')" />
					<i class="pi pi-file-edit" v-tooltip.top="$t('sharing.grants.edit')" />
					<i class="pi pi-trash" v-tooltip.top="$t('sharing.grants.delete')" />
				</div>
				<div class="w-1/2 flex items-center justify-around">
					<Checkbox v-model="grantsReadAccess" :binary="true" disabled />
					<Checkbox v-model="grantsFullPhotoAccess" :binary="true" />
					<Checkbox v-model="grantsDownload" :binary="true" />
					<Checkbox v-model="grantsUpload" :binary="true" />
					<Checkbox v-model="grantsEdit" :binary="true" />
					<Checkbox v-model="grantsDelete" :binary="true" />
				</div>

				<div class="flex justify-center w-full">
					<Button severity="secondary" class="w-full font-bold border-none rounded-none rounded-bl-xl" @click="closeCallback">{{
						$t("dialogs.button.cancel")
					}}</Button>
					<Button
						:disabled="!selectedAlbums.length || !selectedUsersOrGroups.length"
						@click="create"
						severity="success"
						class="border-0 bg-transparent text-create-600 hover:bg-create-600 hover:text-white w-full rounded-none rounded-br-xl"
					>
						<i class="pi pi-user-plus" /><span class="hidden md:inline">{{ $t("sharing.share") }}</span>
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Dialog from "primevue/dialog";
import { ref } from "vue";
import { onMounted } from "vue";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";
import Listbox from "primevue/listbox";
import SharingService from "@/services/sharing-service";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import { type UserOrGroup, useSearchUserGroupComputed } from "@/composables/search/searchUserGroupComputed";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const visible = defineModel("visible", { default: false });
const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { is_se_enabled } = storeToRefs(lycheeStore);

const toast = useToast();
const emits = defineEmits<{
	createdPermission: [];
}>();

const targetAlbums = ref<App.Http.Resources.Models.TargetAlbumResource[] | undefined>(undefined);
const selectedAlbums = ref<string[]>([]);
const selectedUsersOrGroups = ref<UserOrGroup[]>([]);

const grantsFullPhotoAccess = ref(false);
const grantsDownload = ref(false);
const grantsUpload = ref(false);
const grantsEdit = ref(false);
const grantsDelete = ref(false);
const grantsReadAccess = ref(true);

const pt = {
	root: {
		style: {
			transform: "translateX(40%)",
		},
	},
};

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

const { usersGroupsList, load } = useSearchUserGroupComputed(is_se_enabled, undefined);

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

onMounted(() => {
	loadAlbums();
	load();
});
</script>
