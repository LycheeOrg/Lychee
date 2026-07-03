<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #header>
			<span class="font-bold">{{ $t("bulk_album_edit.set_owner_description") }}</span>
		</template>
		<template #body>
			<USelectMenu
				v-model="selectedOwner"
				:items="users"
				label-key="username"
				searchable
				:placeholder="$t('bulk_album_edit.set_owner_select_user')"
				class="w-full"
			/>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" :label="$t('bulk_album_edit.cancel')" color="neutral" variant="soft" @click="visible = false" />
				<UButton
					class="flex-1 justify-center"
					:label="$t('bulk_album_edit.transfer')"
					color="primary"
					:disabled="selectedOwner === undefined"
					@click="doSetOwner"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import BulkAlbumEditService from "@/services/bulk-album-edit-service";
import UsersService from "@/services/users-service";

const props = defineProps<{
	albumIds: string[];
}>();

const emits = defineEmits<{
	transferred: [];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useAppToast();
const users = ref<App.Http.Resources.Models.LightUserResource[]>([]);
const selectedOwner = ref<App.Http.Resources.Models.LightUserResource | undefined>(undefined);

watch(visible, (val) => {
	if (val) {
		selectedOwner.value = undefined;
		if (users.value.length === 0) {
			UsersService.get()
				.then((r) => {
					users.value = r.data;
				})
				.catch(() => {
					toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_load_users"), life: 3000 });
				});
		}
	}
});

function doSetOwner(): void {
	if (selectedOwner.value === undefined) {
		return;
	}
	BulkAlbumEditService.setOwner({ album_ids: props.albumIds, owner_id: selectedOwner.value.id })
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("bulk_album_edit.success_set_owner"), life: 3000 });
			visible.value = false;
			emits("transferred");
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_set_owner"), life: 3000 });
		});
}
</script>
