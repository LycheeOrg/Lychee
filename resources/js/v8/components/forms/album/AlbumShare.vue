<template>
	<Fieldset class="w-full">
		<template #legend>
			<span class="flex items-center gap-2"><UIcon :name="legendIcon" />{{ legendLabel }}</span>
		</template>
		<div class="flex justify-center flex-col">
			<LycheeLoadingIcon fast v-if="perms === undefined" />
			<template v-else>
				<div class="flex text-highlighted">
					<div class="w-5/12 flex">
						<span class="w-full">{{ $t("sharing.username") }}</span>
					</div>
					<div class="w-1/2 flex justify-around items-center">
						<UTooltip :text="$t('sharing.grants.read')"><UIcon name="lucide:eye" /></UTooltip>
						<UTooltip :text="$t('sharing.grants.original')"><UIcon name="lucide:app-window" /></UTooltip>
						<UTooltip :text="$t('sharing.grants.download')"><UIcon name="lucide:cloud-download" /></UTooltip>
						<UTooltip :text="$t('sharing.grants.upload')"><UIcon name="lucide:upload" /></UTooltip>
						<UTooltip :text="$t('sharing.grants.edit')"><UIcon name="lucide:file-edit" /></UTooltip>
						<UTooltip :text="$t('sharing.grants.delete')"><UIcon name="lucide:trash" /></UTooltip>
					</div>
					<div class="w-1/6"></div>
				</div>
				<ShareLine v-for="perm in perms" :perm="perm" :with-album="false" @delete="deletePermission" :key="`perm-${perm.id}`" />
				<div v-if="perms.length === 0">
					<p class="text-muted text-center py-3">{{ $t("sharing.no_data") }}</p>
				</div>
				<div class="flex gap-4">
					<UButton
						icon="lucide:plus"
						color="primary"
						variant="solid"
						class="p-3 w-full mt-4 font-bold justify-center"
						:label="$t('sharing.add_new_access_permission')"
						@click="
							() => {
								dialogVisible = true;
							}
						"
					/>
					<UButton
						icon="lucide:forward"
						color="error"
						variant="soft"
						:disabled="perms.length === 0"
						class="p-3 w-full mt-4 font-bold justify-center disabled:opacity-50"
						:label="$t('sharing.propagate')"
						@click="
							() => {
								dialogPropagateVisible = true;
							}
						"
					/>
				</div>
			</template>
		</div>
	</Fieldset>
	<ConfirmSharingDialog v-if="albumStore.tagOrModelAlbum" v-model:open="dialogPropagateVisible" :album="albumStore.tagOrModelAlbum" />
	<AlbumCreateShareDialog
		v-if="albumStore.tagOrModelAlbum"
		v-model:open="dialogVisible"
		:album="albumStore.tagOrModelAlbum"
		:filtered-users-ids="sharedUserIds"
		@created-permission="load"
	/>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import Fieldset from "@/v8/components/forms/basic/Fieldset.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import SharingService from "@/services/sharing-service";
import ShareLine from "@/v8/components/forms/sharing/ShareLine.vue";
import { trans } from "laravel-vue-i18n";
import AlbumCreateShareDialog from "./AlbumCreateShareDialog.vue";
import ConfirmSharingDialog from "./ConfirmSharingDialog.vue";
import { type UserOrGroupId } from "@/stores/UsersAndGroupsState";
import { useAlbumStore } from "@/stores/AlbumState";

defineProps<{
	legendIcon: string;
	legendLabel: string;
}>();

const toast = useAppToast();

const perms = ref<App.Http.Resources.Models.AccessPermissionResource[] | undefined>(undefined);

const dialogVisible = ref(false);
const dialogPropagateVisible = ref(false);
const albumStore = useAlbumStore();

function load() {
	if (albumStore.album === undefined) {
		return;
	}

	SharingService.get(albumStore.album.id).then((response) => {
		perms.value = response.data;
	});
}

const sharedUserIds = computed((): UserOrGroupId[] => {
	if (perms.value === undefined) {
		return [];
	}
	return perms.value.map((perm) => {
		if (perm.user_group_id !== null) {
			return {
				id: perm.user_group_id,
				type: "group",
			};
		}
		return {
			id: perm.user_id,
			type: "user",
		};
	}) as UserOrGroupId[];
});

function deletePermission(id: number) {
	const permissions = perms.value;
	if (permissions === undefined) {
		return;
	}

	SharingService.delete(id).then(() => {
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("sharing.permission_deleted"), life: 3000 });
		perms.value = permissions.filter((perm) => perm.id !== id);
	});
}

onMounted(() => {
	load();
});
</script>
