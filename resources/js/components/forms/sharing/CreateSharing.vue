<template>
	<div class="flex">
		<div v-if="newShareUser" class="w-5/12 flex items-center">
			<span class="w-full">
				<i v-if="newShareUser.type === 'group'" class="pi pi-users ltr:mr-1 rtl:ml-1" />
				{{ newShareUser.name }}
			</span>
			<span @click="newShareUser = undefined"><i class="pi pi-times" /></span>
		</div>
		<div v-if="!newShareUser" class="w-5/12 flex">
			<SearchTargetUser :filtered-users-ids="props.filteredUsersIds" :with-groups="true" @selected="selectUser" />
		</div>
		<div class="w-1/2 flex items-center justify-around">
			<Checkbox v-model="grantsReadAccess" :binary="true" disabled />
			<Checkbox v-model="grantsFullPhotoAccess" :binary="true" />
			<Checkbox v-model="grantsDownload" :binary="true" />
			<Checkbox v-model="grantsUpload" :binary="true" />
			<Checkbox v-model="grantsEdit" :binary="true" />
			<Checkbox v-model="grantsDelete" :binary="true" />
		</div>
		<Button :disabled="!newShareUser" class="border-0 bg-transparent text-create-600 hover:bg-create-600 hover:text-white w-1/6" @click="create">
			<i class="pi pi-user-plus" /><span class="hidden md:inline">{{ $t("sharing.share") }}</span>
		</Button>
	</div>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import { useToast } from "primevue/usetoast";
import SearchTargetUser from "@/components/forms/album/SearchTargetUser.vue";
import SharingService from "@/services/sharing-service";
import { trans } from "laravel-vue-i18n";
import { type UserOrGroup, type UserOrGroupId } from "@/stores/UsersAndGroupsState";

const props = defineProps<{
	withAlbum?: boolean;
	album: App.Http.Resources.Models.HeadAlbumResource | App.Http.Resources.Models.HeadTagAlbumResource;
	filteredUsersIds?: UserOrGroupId[];
}>();

const toast = useToast();
const emits = defineEmits<{
	createdPermission: [];
}>();

const newShareUser = ref<UserOrGroup | undefined>(undefined);
const grantsFullPhotoAccess = ref(false);
const grantsDownload = ref(false);
const grantsUpload = ref(false);
const grantsEdit = ref(false);
const grantsDelete = ref(false);
const grantsReadAccess = ref(true);

function selectUser(target: UserOrGroup) {
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
		user_ids: [] as number[],
		group_ids: [] as number[],
		grants_download: grantsDownload.value,
		grants_full_photo_access: grantsFullPhotoAccess.value,
		grants_upload: grantsUpload.value,
		grants_edit: grantsEdit.value,
		grants_delete: grantsDelete.value,
	};

	if (newShareUser.value.type === "group") {
		data.group_ids = [newShareUser.value.id];
	} else {
		data.user_ids = [newShareUser.value.id];
	}

	SharingService.add(data).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("sharing.permission_created"), life: 3000 });
		reset();
		emits("createdPermission");
	});
}
</script>
