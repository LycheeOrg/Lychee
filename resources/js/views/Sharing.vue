<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("sharing.title") }}
		</template>
		<template #end> </template>
	</Toolbar>
	<BulkSharingModal v-model:visible="bulkSharingVisible" @created-permission="load" />
	<Panel v-if="perms !== undefined" class="border-none p-9 mx-auto max-w-3xl" pt:header:class="hidden">
		<div class="w-full text-center text-muted-color-emphasis">
			{{ $t("sharing.info") }}
		</div>
		<Button
			class="w-full font-bold border-none mt-4 mb-12"
			:label="$t('sharing.bluk_share')"
			:icon="'pi pi-user-plus'"
			@click="bulkSharingVisible = true"
		/>
		<div class="flex flex-col text-muted-color-emphasis">
			<div class="flex items-center">
				<div class="w-5/12 flex items-center">
					<span class="w-full">{{ $t("sharing.album_title") }}</span>
					<span class="w-full">{{ $t("sharing.username") }}</span>
				</div>
				<div class="w-1/2 flex items-center justify-around">
					<i v-tooltip.top="$t('sharing.grants.read')" class="pi pi-eye" />
					<i v-tooltip.top="$t('sharing.grants.original')" class="pi pi-window-maximize" />
					<i v-tooltip.top="$t('sharing.grants.download')" class="pi pi-cloud-download" />
					<i v-tooltip.top="$t('sharing.grants.upload')" class="pi pi-upload" />
					<i v-tooltip.top="$t('sharing.grants.edit')" class="pi pi-file-edit" />
					<i v-tooltip.top="$t('sharing.grants.delete')" class="pi pi-trash" />
				</div>
				<div class="w-1/6"></div>
			</div>
			<template v-if="perms?.length > 0">
				<ShareLine v-for="(perm, idx) in perms" :key="'p' + (perm.id ?? idx)" :perm="perm" :with-album="true" @delete="deletePermission" />
			</template>
			<p v-else class="text-center">{{ $t("sharing.no_data") }}</p>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import ShareLine from "@/components/forms/sharing/ShareLine.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import BulkSharingModal from "@/components/forms/sharing/BulkSharingModal.vue";
import SharingService from "@/services/sharing-service";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import { onMounted } from "vue";

const perms = ref<App.Http.Resources.Models.AccessPermissionResource[] | undefined>(undefined);
const toast = useToast();

const bulkSharingVisible = ref(false);

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

function load() {
	perms.value = undefined;
	SharingService.list().then((response) => {
		perms.value = response.data;
	});
}

onMounted(() => {
	load();
});
</script>
