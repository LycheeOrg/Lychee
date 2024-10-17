<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text />
			</router-link>
		</template>
		<template #center>
			{{ $t("lychee.SHARING") }}
		</template>
		<template #end> </template>
	</Toolbar>
	<Panel class="border-none p-9 mx-auto max-w-3xl" v-if="perms !== undefined" pt:header:class="hidden">
		<div class="w-full mb-9 text-center">This page gives an overview and edit the sharing rights associated with albums.</div>
		<div class="flex flex-col">
			<div class="flex text-muted-color items-center">
				<div class="w-5/12 flex items-center">
					<span class="w-full">{{ $t("lychee.ALBUM_TITLE") }}</span>
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
			</div>
			<template v-if="perms?.length > 0">
				<ShareLine v-for="perm in perms" :perm="perm" @delete="deletePermission" :with-album="true" />
			</template>
			<p v-else class="text-center">Sharing list is empty</p>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import ShareLine from "@/components/forms/sharing/ShareLine.vue";
import SharingService from "@/services/sharing-service";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";

const perms = ref(undefined as undefined | App.Http.Resources.Models.AccessPermissionResource[]);
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
		toast.add({ severity: "success", summary: "Success", detail: "Permission deleted", life: 3000 });
		perms.value = permissions.filter((perm) => perm.id !== id);
	});
}
</script>
