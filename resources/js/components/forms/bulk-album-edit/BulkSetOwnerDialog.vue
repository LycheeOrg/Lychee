<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div>
				<div class="p-9 max-w-md">
					<p class="text-center text-muted-color mb-4">{{ $t("bulk_album_edit.set_owner_description") }}</p>
					<Select
						v-model="selectedOwner"
						:options="users"
						filter
						option-label="username"
						:placeholder="$t('bulk_album_edit.set_owner_select_user')"
						class="w-full border-none"
					/>
				</div>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("bulk_album_edit.cancel") }}
					</Button>
					<Button :disabled="selectedOwner === undefined" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="doSetOwner">
						{{ $t("bulk_album_edit.transfer") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script lang="ts">
export default { name: "BulkSetOwnerDialog" };
</script>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Select from "primevue/select";
import BulkAlbumEditService from "@/services/bulk-album-edit-service";
import UsersService from "@/services/users-service";

const props = defineProps<{
	albumIds: string[];
}>();

const emits = defineEmits<{
	transferred: [];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useToast();
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
					toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_load_users", life: 3000 });
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
			toast.add({ severity: "success", summary: "OK", detail: "bulk_album_edit.success_set_owner", life: 3000 });
			visible.value = false;
			emits("transferred");
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_set_owner", life: 3000 });
		});
}
</script>
