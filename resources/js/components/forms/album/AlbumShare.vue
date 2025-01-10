<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full" v-if="perms !== undefined">
		<template #content>
			<div class="flex text-muted-color-emphasis">
				<div class="w-5/12 flex">
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
				<div class="w-1/6"></div>
			</div>
			<ShareLine v-for="perm in perms" :perm="perm" @delete="deletePermission" :with-album="props.withAlbum" />
			<CreateSharing :withAlbum="props.withAlbum" :album="props.album" @createdPermission="load" :filtered-users-ids="sharedUserIds" />
		</template>
	</Card>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import Card from "primevue/card";
import { useToast } from "primevue/usetoast";
import SharingService from "@/services/sharing-service";
import ShareLine from "@/components/forms/sharing/ShareLine.vue";
import CreateSharing from "../sharing/CreateSharing.vue";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	withAlbum: boolean;
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource;
}>();

const toast = useToast();

const perms = ref<App.Http.Resources.Models.AccessPermissionResource[] | undefined>(undefined);

function load() {
	SharingService.get(props.album.id).then((response) => {
		perms.value = response.data;
	});
}

const sharedUserIds = computed((): number[] => {
	if (perms.value === undefined) {
		return [];
	}
	return perms.value.map((perm) => perm.user_id) as number[];
});

load();

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
</script>
