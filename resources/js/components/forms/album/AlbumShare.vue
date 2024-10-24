<template>
	<Card class="sm:p-4 xl:px-9 max-w-3xl w-full" v-if="perms !== undefined">
		<template #content>
			<div class="flex text-muted-color">
				<div class="w-5/12 flex">
					<span class="w-full">{{ $t("lychee.USERNAME") }}</span>
				</div>
				<div class="w-1/12 flex justify-center items-center">
					<i class="pi pi-window-maximize" v-tooltip.top="'Grants full photo access'" />
				</div>
				<div class="w-1/12 flex justify-center items-center">
					<i class="pi pi-download" v-tooltip.top="'Grants download'" />
				</div>
				<div class="w-1/12 flex justify-center items-center">
					<i class="pi pi-upload" v-tooltip.top="'Grants upload'" />
				</div>
				<div class="w-1/12 flex justify-center items-center">
					<i class="pi pi-file-edit" v-tooltip.top="'Grants edit'" />
				</div>
				<div class="w-1/12 flex justify-center items-center">
					<i class="pi pi-trash" v-tooltip.top="'Grants delete'" />
				</div>
				<div class="w-16"></div>
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

const props = defineProps<{
	withAlbum: boolean;
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource;
}>();

const toast = useToast();

const perms = ref(undefined as undefined | App.Http.Resources.Models.AccessPermissionResource[]);

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
		toast.add({ severity: "success", summary: "Success", detail: "Permission deleted", life: 3000 });
		perms.value = permissions.filter((perm) => perm.id !== id);
	});
}
</script>
