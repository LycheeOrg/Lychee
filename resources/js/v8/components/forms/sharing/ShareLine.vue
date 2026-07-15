<template>
	<div class="flex">
		<div class="w-5/12 flex items-center text-muted">
			<span v-if="props.withAlbum" class="w-full">
				<RouterLink :to="{ name: 'album', params: { albumId: props.perm.album_id } }" class="hover:text-highlighted underline">{{
					props.perm.album_title
				}}</RouterLink>
			</span>
			<span class="w-full">
				<UIcon v-if="props.perm.user_group_id !== null" name="lucide:users" class="ltr:mr-1 rtl:ml-1" />
				{{ props.perm.username ?? props.perm.user_group_name }}
			</span>
		</div>
		<div class="w-1/2 flex items-center justify-around">
			<UCheckbox v-model="grantsReadAccess" disabled />
			<UCheckbox v-model="grantsFullPhotoAccess" @update:model-value="edit" />
			<UCheckbox v-model="grantsDownload" @update:model-value="edit" />
			<UCheckbox v-model="grantsUpload" @update:model-value="edit" />
			<UCheckbox v-model="grantsEdit" @update:model-value="edit" />
			<UCheckbox v-model="grantsDelete" @update:model-value="edit" />
		</div>
		<UButton color="error" variant="ghost" class="w-1/6" @click="deletePermission">
			<UIcon name="lucide:user-minus" /><span class="hidden md:inline">{{ $t("dialogs.button.delete") }}</span>
		</UButton>
	</div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import SharingService from "@/services/sharing-service";

const props = defineProps<{
	withAlbum: boolean;
	perm: App.Http.Resources.Models.AccessPermissionResource;
}>();

const toast = useAppToast();
const emits = defineEmits<{
	delete: [id: number];
}>();

const grantsFullPhotoAccess = ref(false);
const grantsDownload = ref(false);
const grantsUpload = ref(false);
const grantsEdit = ref(false);
const grantsDelete = ref(false);
const grantsReadAccess = ref(true);

function load(permisison: App.Http.Resources.Models.AccessPermissionResource) {
	grantsFullPhotoAccess.value = permisison.grants_full_photo_access;
	grantsDownload.value = permisison.grants_download;
	grantsUpload.value = permisison.grants_upload;
	grantsEdit.value = permisison.grants_edit;
	grantsDelete.value = permisison.grants_delete;
}

function edit() {
	const data = {
		perm_id: props.perm.id as number,
		grants_full_photo_access: grantsFullPhotoAccess.value,
		grants_download: grantsDownload.value,
		grants_upload: grantsUpload.value,
		grants_edit: grantsEdit.value,
		grants_delete: grantsDelete.value,
	};
	SharingService.edit(data).then((response) => {
		load(response.data);
		toast.add({ severity: "success", summary: "Success", detail: "Permission updated", life: 1000 });
	});
}

load(props.perm);

const deletePermission = () => {
	if (props.perm.id === null) {
		return;
	}
	emits("delete", props.perm.id);
};

watch(
	() => props.perm,
	(newVal) => {
		load(newVal);
	},
);
</script>
