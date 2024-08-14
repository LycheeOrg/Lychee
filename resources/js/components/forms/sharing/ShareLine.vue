<template>
	<div class="flex">
		<div class="w-5/12 flex">
			<span v-if="props.withAlbum" class="w-full">{{ props.perm.album_title }}</span>
			<span class="w-full">{{ props.perm.username }}</span>
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsFullPhotoAccess" :binary="true" @update:model-value="edit" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsDownload" :binary="true" @update:model-value="edit" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsUpload" :binary="true" @update:model-value="edit" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsEdit" :binary="true" @update:model-value="edit" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsDelete" :binary="true" @update:model-value="edit" />
		</div>
		<Button @click="deletePermission" class="border-0 bg-surface text-danger-600 hover:bg-danger-700 hover:text-white w-1/6">
			<i class="pi pi-user-minus" /><span class="hidden md:inline">{{ $t("lychee.DELETE") }}</span>
		</Button>
	</div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useToast } from "primevue/usetoast";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";
import SharingService from "@/services/sharing-service";

const props = defineProps<{
	withAlbum: boolean;
	perm: App.Http.Resources.Models.AccessPermissionResource;
}>();

const toast = useToast();
const emit = defineEmits(["delete"]);

const grantsFullPhotoAccess = ref(false);
const grantsDownload = ref(false);
const grantsUpload = ref(false);
const grantsEdit = ref(false);
const grantsDelete = ref(false);

function edit() {
	const data = {
		perm_id: props.perm.id as number,
		grants_full_photo_access: grantsFullPhotoAccess.value,
		grants_download: grantsDownload.value,
		grants_upload: grantsUpload.value,
		grants_edit: grantsEdit.value,
		grants_delete: grantsDelete.value,
	};
	console.log(data);
	SharingService.edit(data).then((response) => {
		grantsFullPhotoAccess.value = response.data.grants_full_photo_access;
		grantsDownload.value = response.data.grants_download;
		grantsUpload.value = response.data.grants_upload;
		grantsEdit.value = response.data.grants_edit;
		grantsDelete.value = response.data.grants_delete;

		toast.add({ severity: "success", summary: "Success", detail: "Permission updated", life: 1000 });
	});
}

const deletePermission = () => {
	emit("delete", props.perm.id);
};
</script>
