<template>
	<div class="flex">
		<div class="w-5/12 flex items-center text-muted-color">
			<span v-if="props.withAlbum" class="w-full">
				<router-link :to="{ name: 'album', params: { albumid: props.perm.album_id } }" class="hover:text-color-emphasis underline">{{
					props.perm.album_title
				}}</router-link>
			</span>
			<span class="w-full">{{ props.perm.username }}</span>
		</div>
		<div class="w-1/2 flex items-center justify-around">
			<Checkbox v-model="grantsReadAccess" :binary="true" disabled />
			<Checkbox v-model="grantsFullPhotoAccess" :binary="true" @update:model-value="edit" />
			<Checkbox v-model="grantsDownload" :binary="true" @update:model-value="edit" />
			<Checkbox v-model="grantsUpload" :binary="true" @update:model-value="edit" />
			<Checkbox v-model="grantsEdit" :binary="true" @update:model-value="edit" />
			<Checkbox v-model="grantsDelete" :binary="true" @update:model-value="edit" />
		</div>
		<Button @click="deletePermission" class="border-0 bg-transparent text-danger-600 hover:bg-danger-700 hover:text-white w-1/6">
			<i class="pi pi-user-minus" /><span class="hidden md:inline">{{ $t("dialogs.button.delete") }}</span>
		</Button>
	</div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";
import SharingService from "@/services/sharing-service";

const props = defineProps<{
	withAlbum: boolean;
	perm: App.Http.Resources.Models.AccessPermissionResource;
}>();

const toast = useToast();
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
