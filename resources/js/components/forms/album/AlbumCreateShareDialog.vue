<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true" @close="visible = false">
		<template #container="{ closeCallback }">
			<div class="flex flex-col relative md:w-[500px] max-w-[500px] text-sm rounded-md pt-9">
				<div class="flex text-muted-color-emphasis w-full px-9 pb-2">
					<div class="w-1/2">
						<span class="w-full">{{ $t("sharing.username") }}</span>
					</div>
					<div class="w-1/2 flex justify-around items-center">
						<i class="pi pi-eye" v-tooltip.top="$t('sharing.grants.read')" />
						<i class="pi pi-window-maximize" v-tooltip.top="$t('sharing.grants.original')" />
						<i class="pi pi-cloud-download" v-tooltip.top="$t('sharing.grants.download')" />
						<i class="pi pi-upload" v-tooltip.top="$t('sharing.grants.upload')" />
						<i class="pi pi-file-edit" v-tooltip.top="$t('sharing.grants.edit')" />
						<i class="pi pi-trash" v-tooltip.top="$t('sharing.grants.delete')" />
					</div>
				</div>
				<div class="flex text-muted-color-emphasis w-full px-9">
					<div class="w-1/2 flex items-center" v-if="newShareUser">
						<span class="w-full">{{ newShareUser.username }}</span>
						<span @click="newShareUser = undefined"><i class="pi pi-times" /></span>
					</div>
					<div class="w-1/2" v-if="!newShareUser">
						<SearchTargetUser @selected="selectUser" :filtered-users-ids="props.filteredUsersIds" />
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
					<Button @click="closeCallback" severity="secondary" class="w-full font-bold border-none rounded-bl-xl">
						{{ $t("dialogs.button.cancel") }}
					</Button>
					<Button
						@click="create"
						:disabled="!newShareUser"
						class="font-bold w-full border-none rounded-none bg-transparent text-create-600 hover:bg-create-600 hover:text-white rounded-br-xl"
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

const props = withDefaults(
	defineProps<{
		album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource;
		filteredUsersIds?: number[];
	}>(),
	{
		filteredUsersIds: () => [],
	},
);

const visible = defineModel("visible", { type: Boolean, required: true });

const toast = useToast();
const emits = defineEmits<{
	createdPermission: [];
}>();

const newShareUser = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);
const grantsFullPhotoAccess = ref(false);
const grantsDownload = ref(false);
const grantsUpload = ref(false);
const grantsEdit = ref(false);
const grantsDelete = ref(false);
const grantsReadAccess = ref(true);

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
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("sharing.permission_created"), life: 3000 });
		visible.value = false;
		reset();
		emits("createdPermission");
	});
}
</script>
