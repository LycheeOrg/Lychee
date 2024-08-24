<template>
	<div class="flex">
		<div class="w-5/12 flex items-center" v-if="newShareUser">
			<span class="w-full">{{ newShareUser.username }}</span>
			<span @click="newShareUser = undefined"><i class="pi pi-times" /></span>
		</div>
		<div class="w-5/12 flex" v-if="!newShareUser">
			<SearchTargetUser @selected="selectUser" :filtered-users-ids="props.filteredUsersIds" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsFullPhotoAccess" :binary="true" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsDownload" :binary="true" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsUpload" :binary="true" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsEdit" :binary="true" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="grantsDelete" :binary="true" />
		</div>
		<Button @click="create" :disabled="!newShareUser" class="border-0 bg-surface text-create-600 hover:bg-create-600 hover:text-white w-1/6">
			<i class="pi pi-user-plus" /><span class="hidden md:inline">{{ $t("lychee.SHARE") }}</span>
		</Button>
	</div>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import { useToast } from "primevue/usetoast";
import SearchTargetUser from "../album/SearchTargetUser.vue";
import SharingService from "@/services/sharing-service";

const props = withDefaults(
	defineProps<{
		withAlbum?: boolean;
		album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource;
		filteredUsersIds?: number[];
	}>(),
	{
		withAlbum: false,
		filteredUsersIds: () => [],
	},
);

const toast = useToast();
const emits = defineEmits<{
	(e: "createdPermission"): void;
}>();

const newShareUser = ref(undefined as undefined | App.Http.Resources.Models.LightUserResource);
const grantsFullPhotoAccess = ref(false);
const grantsDownload = ref(false);
const grantsUpload = ref(false);
const grantsEdit = ref(false);
const grantsDelete = ref(false);

function selectUser(target: App.Http.Resources.Models.LightUserResource) {
	newShareUser.value = target;
}

function reset() {
	newShareUser.value = undefined;
	grantsFullPhotoAccess.value = false;
	grantsDownload.value = false;
	grantsUpload.value = false;
	grantsEdit.value = false;
	grantsDelete.value = false;
}

function create() {
	if (newShareUser.value === undefined) {
		return;
	}
	const data = {
		album_ids: [props.album.id],
		user_ids: [newShareUser.value.id],
		grants_download: grantsDownload.value,
		grants_full_photo_access: grantsFullPhotoAccess.value,
		grants_upload: grantsUpload.value,
		grants_edit: grantsEdit.value,
		grants_delete: grantsDelete.value,
	};

	SharingService.add(data).then(() => {
		toast.add({ severity: "success", summary: "Success", detail: "Permission created", life: 3000 });
		reset();
		emits("createdPermission");
	});
}
</script>
