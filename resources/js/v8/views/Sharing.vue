<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("sharing.title") }}
	</UHeader>
	<BulkSharingModal v-model:visible="bulkSharingVisible" @created-permission="load" />
	<UCard v-if="perms !== undefined" class="mx-auto max-w-3xl mt-4" :ui="{ header: 'hidden' }">
		<div class="w-full text-center text-highlighted">
			{{ $t("sharing.info") }}
		</div>
		<UButton
			class="w-full font-bold justify-center mt-4 mb-12"
			:label="$t('sharing.bluk_share')"
			icon="prime:user-plus"
			@click="
				() => {
					bulkSharingVisible = true;
				}
			"
		/>
		<div class="flex flex-col text-highlighted">
			<div class="flex items-center">
				<div class="w-5/12 flex items-center">
					<span class="w-full">{{ $t("sharing.album_title") }}</span>
					<span class="w-full">{{ $t("sharing.username") }}</span>
				</div>
				<div class="w-1/2 flex items-center justify-around">
					<UTooltip :text="$t('sharing.grants.read')"><UIcon name="prime:eye" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.original')"><UIcon name="prime:window-maximize" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.download')"><UIcon name="prime:cloud-download" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.upload')"><UIcon name="prime:upload" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.edit')"><UIcon name="prime:file-edit" /></UTooltip>
					<UTooltip :text="$t('sharing.grants.delete')"><UIcon name="prime:trash" /></UTooltip>
				</div>
				<div class="w-1/6"></div>
			</div>
			<template v-if="perms?.length > 0">
				<ShareLine v-for="(perm, idx) in perms" :key="'p' + (perm.id ?? idx)" :perm="perm" :with-album="true" @delete="deletePermission" />
			</template>
			<p v-else class="text-center">{{ $t("sharing.no_data") }}</p>
		</div>
	</UCard>
</template>
<script setup lang="ts">
import { ref } from "vue";
import ShareLine from "@/v8/components/forms/sharing/ShareLine.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import BulkSharingModal from "@/v8/components/forms/sharing/BulkSharingModal.vue";
import SharingService from "@/services/sharing-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import { onMounted } from "vue";

const perms = ref<App.Http.Resources.Models.AccessPermissionResource[] | undefined>(undefined);
const toast = useAppToast();

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
