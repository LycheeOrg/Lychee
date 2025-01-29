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
						<i class="pi pi-eye" v-tooltip.top="$t('sharing.grants.read')" />
						<i class="pi pi-window-maximize" v-tooltip.top="$t('sharing.grants.original')" />
						<i class="pi pi-cloud-download" v-tooltip.top="$t('sharing.grants.download')" />
						<i class="pi pi-upload" v-tooltip.top="$t('sharing.grants.upload')" />
						<i class="pi pi-file-edit" v-tooltip.top="$t('sharing.grants.edit')" />
						<i class="pi pi-trash" v-tooltip.top="$t('sharing.grants.delete')" />
					</div>
					<div class="w-1/6"></div>
				</div>
				<ShareLine v-for="perm in perms" :perm="perm" @delete="deletePermission" :with-album="false" />
				<div v-if="perms.length === 0">
					<p class="text-muted-color text-center py-3">{{ $t("sharing.no_data") }}</p>
				</div>
				<div class="flex gap-4">
					<Button
						@click="dialogVisible = true"
						icon="pi pi-plus"
						severity="primary"
						class="p-3 w-full mt-4 font-bold border-none rounded-xl"
						:label="$t('sharing.add_new_access_permission')"
					></Button>
					<Button
						@click="dialogPropagateVisible = true"
						icon="pi pi-forward"
						severity="danger"
						:disabled="perms.length === 0"
						class="p-3 w-full mt-4 font-bold border-none rounded-xl disabled:opacity-50"
						:label="$t('sharing.propagate')"
					></Button>
				</div>
			</template>
		</template>
	</Card>
	<ConfirmSharingDialog v-model:visible="dialogPropagateVisible" :album="props.album" />
	<AlbumCreateShareDialog v-model:visible="dialogVisible" :album="props.album" @createdPermission="load" :filtered-users-ids="sharedUserIds" />
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

const props = defineProps<{
	album: App.Http.Resources.Models.AlbumResource | App.Http.Resources.Models.TagAlbumResource;
}>();

const toast = useToast();

const perms = ref<App.Http.Resources.Models.AccessPermissionResource[] | undefined>(undefined);

const dialogVisible = ref(false);
const dialogPropagateVisible = ref(false);

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
