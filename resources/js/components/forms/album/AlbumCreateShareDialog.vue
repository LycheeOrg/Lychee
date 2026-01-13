<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true" @close="visible = false">
		<template #container="{ closeCallback }">
			<div class="flex flex-col relative w-full md:w-lg text-sm rounded-md pt-9">
				<div class="flex text-muted-color-emphasis w-full px-9 pb-2">
					<div class="w-1/2">
						<span class="w-full">{{ $t("sharing.username") }}</span>
					</div>
					<div class="w-1/2 flex justify-around items-center">
						<i v-tooltip.top="$t('sharing.grants.read')" class="pi pi-eye" />
						<i v-tooltip.top="$t('sharing.grants.original')" class="pi pi-window-maximize" />
						<i v-tooltip.top="$t('sharing.grants.download')" class="pi pi-cloud-download" />
						<i v-tooltip.top="$t('sharing.grants.upload')" class="pi pi-upload" />
						<i v-tooltip.top="$t('sharing.grants.edit')" class="pi pi-file-edit" />
						<i v-tooltip.top="$t('sharing.grants.delete')" class="pi pi-trash" />
					</div>
				</div>
				<div class="flex text-muted-color-emphasis w-full px-9">
					<div v-if="newShareUser" class="w-1/2 flex items-center">
						<span class="w-full">
							<i v-if="newShareUser.type === 'group'" class="pi pi-users ltr:mr-1 rtl:ml-1" />
							{{ newShareUser.name }}
						</span>
						<span @click="newShareUser = undefined"><i class="pi pi-times" /></span>
					</div>
					<div v-if="!newShareUser" class="w-1/2">
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
				</div>
				<div class="flex items-center mt-9 w-full">
					<Button severity="secondary" class="w-full font-bold border-none rounded-bl-xl" @click="closeCallback">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button
						:disabled="!newShareUser"
						class="font-bold w-full border-none rounded-none bg-transparent text-create-600 hover:bg-create-600 hover:text-white rounded-br-xl"
						@click="create"
					>
						<i class="pi pi-user-plus" /><span class="hidden md:inline">{{ $t("sharing.share") }}</span>
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import SharingService from "@/services/sharing-service";
import { trans } from "laravel-vue-i18n";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import SearchTargetUser from "./SearchTargetUser.vue";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";
import { type UserOrGroup, type UserOrGroupId } from "@/stores/UsersAndGroupsState";

const props = defineProps<{
	album: App.Http.Resources.Models.HeadAlbumResource | App.Http.Resources.Models.HeadTagAlbumResource;
	filteredUsersIds?: UserOrGroupId[];
}>();

const visible = defineModel("visible", { type: Boolean, required: true });

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
		visible.value = false;
		reset();
		emits("createdPermission");
	});
}
</script>
