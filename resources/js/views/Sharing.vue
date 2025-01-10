<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("sharing.title") }}
		</template>
		<template #end> </template>
	</Toolbar>
	<Panel class="border-none p-9 mx-auto max-w-3xl" v-if="perms !== undefined" pt:header:class="hidden">
		<div class="w-full mb-9 text-center text-muted-color-emphasis">
			{{ $t("sharing.info") }}
		</div>
		<div class="flex flex-col text-muted-color-emphasis">
			<div class="flex items-center">
				<div class="w-5/12 flex items-center">
					<span class="w-full">{{ $t("sharing.album_title") }}</span>
					<span class="w-full">{{ $t("sharing.username") }}</span>
				</div>
				<div class="w-1/2 flex items-center justify-around">
					<i class="pi pi-eye" v-tooltip.top="$t('sharing.grants.read')" />
					<i class="pi pi-window-maximize" v-tooltip.top="$t('sharing.grants.original')" />
					<i class="pi pi-cloud-download" v-tooltip.top="$t('sharing.grants.download')" />
					<i class="pi pi-upload" v-tooltip.top="$t('sharing.grants.upload')" />
					<i class="pi pi-file-edit" v-tooltip.top="$t('sharing.grants.edit')" />
					<i class="pi pi-trash" v-tooltip.top="$t('sharing.grants.delete')" />
				</div>
				<div class="w-1/6"></div>
			</div>
			<template v-if="perms?.length > 0">
				<ShareLine v-for="perm in perms" :perm="perm" @delete="deletePermission" :with-album="true" />
			</template>
			<p v-else class="text-center">{{ $t("sharing.no_data") }}</p>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import ShareLine from "@/components/forms/sharing/ShareLine.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import SharingService from "@/services/sharing-service";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";

const perms = ref<App.Http.Resources.Models.AccessPermissionResource[] | undefined>(undefined);
const toast = useToast();

SharingService.list().then((response) => {
	perms.value = response.data;
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
</script>
