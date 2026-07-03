<template>
	<UCard class="sm:p-4 xl:px-9 max-w-3xl w-full py-0" :ui="{ body: 'flex justify-center flex-col p-0' }">
		<Spinner v-if="perms === undefined" />
		<template v-else>
			<div class="flex text-highlighted">
				<div class="w-5/12 flex">
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
				<div class="w-1/6"></div>
			</div>
			<ShareLine v-for="perm in perms" :perm="perm" :with-album="false" @delete="deletePermission" :key="`perm-${perm.id}`" />
			<div v-if="perms.length === 0">
				<p class="text-muted text-center py-3">{{ $t("sharing.no_data") }}</p>
			</div>
			<div class="flex gap-4">
				<UButton
					icon="prime:plus"
					color="primary"
					class="p-3 w-full mt-4 font-bold justify-center rounded-xl"
					:label="$t('sharing.add_new_access_permission')"
					@click="dialogVisible = true"
				/>
				<UButton
					icon="prime:forward"
					color="error"
					:disabled="perms.length === 0"
					class="p-3 w-full mt-4 font-bold justify-center rounded-xl disabled:opacity-50"
					:label="$t('sharing.propagate')"
					@click="dialogPropagateVisible = true"
				/>
			</div>
		</template>
	</UCard>
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
import Spinner from "@/v8/components/Spinner.vue";
import SharingService from "@/services/sharing-service";
import ShareLine from "@/v8/components/forms/sharing/ShareLine.vue";
import { trans } from "laravel-vue-i18n";
import AlbumCreateShareDialog from "./AlbumCreateShareDialog.vue";
import ConfirmSharingDialog from "./ConfirmSharingDialog.vue";
import { type UserOrGroupId } from "@/stores/UsersAndGroupsState";
import { useAlbumStore } from "@/stores/AlbumState";

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
