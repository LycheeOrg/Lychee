<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full py-0" pt:body:class="p-0" pt:content:class="flex justify-center flex-col">
		<template #content>
			<ProgressSpinner v-if="perms === undefined" />
			<template v-else>
				<div class="flex text-muted-color-emphasis">
					<div class="w-5/12 flex">
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
					<div class="w-1/6"></div>
				</div>
				<ShareLine v-for="perm in perms" :perm="perm" :with-album="false" @delete="deletePermission" :key="`perm-${perm.id}`" />
				<div v-if="perms.length === 0">
					<p class="text-muted-color text-center py-3">{{ $t("sharing.no_data") }}</p>
				</div>
				<div class="flex gap-4">
					<Button
						icon="pi pi-plus"
						severity="primary"
						class="p-3 w-full mt-4 font-bold border-none rounded-xl"
						:label="$t('sharing.add_new_access_permission')"
						@click="dialogVisible = true"
					></Button>
					<Button
						icon="pi pi-forward"
						severity="danger"
						:disabled="perms.length === 0"
						class="p-3 w-full mt-4 font-bold border-none rounded-xl disabled:opacity-50"
						:label="$t('sharing.propagate')"
						@click="dialogPropagateVisible = true"
					></Button>
				</div>
			</template>
		</template>
	</Card>
	<ConfirmSharingDialog v-if="albumStore.tagOrModelAlbum" v-model:visible="dialogPropagateVisible" :album="albumStore.tagOrModelAlbum" />
	<AlbumCreateShareDialog
		v-if="albumStore.tagOrModelAlbum"
		v-model:visible="dialogVisible"
		:album="albumStore.tagOrModelAlbum"
		:filtered-users-ids="sharedUserIds"
		@created-permission="load"
	/>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import SharingService from "@/services/sharing-service";
import ShareLine from "@/components/forms/sharing/ShareLine.vue";
import { trans } from "laravel-vue-i18n";
import AlbumCreateShareDialog from "./AlbumCreateShareDialog.vue";
import Button from "primevue/button";
import ProgressSpinner from "primevue/progressspinner";
import ConfirmSharingDialog from "./ConfirmSharingDialog.vue";
import { type UserOrGroupId } from "@/stores/UsersAndGroupsState";
import { useAlbumStore } from "@/stores/AlbumState";

const toast = useToast();

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
