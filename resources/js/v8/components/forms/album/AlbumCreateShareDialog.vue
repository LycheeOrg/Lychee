<template>
	<UModal v-model:open="open">
		<template #body>
			<div class="flex text-highlighted w-full pb-2">
				<div class="w-1/2">
					<span class="w-full">{{ $t("sharing.username") }}</span>
				</div>
				<div class="w-1/2 flex justify-around items-center">
					<UTooltip :text="$t('sharing.grants.read')"><UIcon name="prime:eye" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.original')"><UIcon name="prime:window-maximize" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.download')"><UIcon name="prime:cloud-download" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.upload')"><UIcon name="prime:upload" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.edit')"><UIcon name="prime:file-edit" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.delete')"><UIcon name="prime:trash" /></UTooltip>
				</div>
			</div>
			<div class="flex text-highlighted w-full">
				<div v-if="newShareUser" class="w-1/2 flex items-center">
					<span class="w-full">
						<UIcon v-if="newShareUser.type === 'group'" name="prime:users" class="ltr:mr-1 rtl:ml-1" />
						{{ newShareUser.name }}
					</span>
					<span @click="newShareUser = undefined"><UIcon name="prime:times" /></span>
				</div>
				<div v-if="!newShareUser" class="w-1/2">
					<SearchTargetUser :filtered-users-ids="props.filteredUsersIds" :with-groups="true" @selected="selectUser" />
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
			<UButton
				color="neutral"
				variant="ghost"
				class="w-full font-bold justify-center"
				@click="
					() => {
						open = false;
					}
				"
			>
				{{ $t("dialogs.button.cancel") }}
			</UButton>
			<UButton color="success" variant="ghost" class="font-bold w-full justify-center" :disabled="!newShareUser" @click="create">
				<UIcon name="prime:user-plus" /><span class="hidden md:inline">{{ $t("sharing.share") }}</span>
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import SharingService from "@/services/sharing-service";
import { trans } from "laravel-vue-i18n";
import { useAppToast } from "@/v8/composables/useAppToast";
import { ref } from "vue";
import SearchTargetUser from "./SearchTargetUser.vue";
import { type UserOrGroup, type UserOrGroupId } from "@/stores/UsersAndGroupsState";

const props = defineProps<{
	album:
		| App.Http.Resources.Models.HeadAlbumResource
		| App.Http.Resources.Models.HeadTagAlbumResource
		| App.Http.Resources.Models.HeadPersonAlbumResource;
	filteredUsersIds?: UserOrGroupId[];
}>();

const open = defineModel<boolean>("open", { required: true });

const toast = useAppToast();
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
		open.value = false;
		reset();
		emits("createdPermission");
	});
}
</script>
