<template>
	<Dialog v-model:visible="visible" modal class="border-none max-w-lg w-full">
		<template #container="{ closeCallback }">
			<div class="p-9 w-full flex flex-col gap-2 justify-center">
				<FloatLabel class="w-full" variant="on">
					<InputText id="username" v-model="username" aria-label="Username" :autofocus="true" />
					<label for="username">{{ $t("users.username") }}</label>
				</FloatLabel>
				<FloatLabel class="w-full" variant="on">
					<InputPassword id="password" v-model="password" aria-label="Password" />
					<label for="password">{{ $t("users.password") }}</label>
				</FloatLabel>
				<div class="w-full items-center text-muted-color">
					<Checkbox inputId="mayUpload" v-model="may_upload" :binary="true" />
					<label for="mayUpload" class="ml-2 cursor-pointer3">{{ $t("users.create_edit.upload_rights") }}</label>
				</div>
				<div class="w-full items-center text-muted-color">
					<Checkbox inputId="mayEdit" v-model="may_edit_own_settings" :binary="true" />
					<label for="mayEdit" class="ml-2 cursor-pointer">{{ $t("users.create_edit.edit_rights") }}</label>
				</div>
				<div class="w-full items-center text-muted-color" v-if="is_se_enabled || is_se_preview_enabled">
					<Checkbox inputId="hasQuota" v-model="has_quota" :binary="true" />
					<label for="hasQuota" class="ml-2 cursor-pointer">{{ $t("users.create_edit.quota") }} <SETag /></label>
				</div>
				<div class="w-full flex items-center text-muted-color" v-if="has_quota === true">
					<InputText id="quotaKb" v-model="quota_kb" aria-label="quotaKb" class="!w-1/2" />
					<label class="pl-4 w-1/2" for="quotaKb">{{ $t("users.create_edit.quota_kb") }}</label>
				</div>
				<div class="w-full flex items-center text-muted-color pt-2" v-if="is_se_enabled">
					<FloatLabel variant="on">
						<Textarea id="note" class="w-full h-18" v-model="note" rows="2" cols="40" />
						<label for="note">{{ $t("users.create_edit.note") }}</label>
					</FloatLabel>
				</div>
			</div>
			<div class="flex">
				<Button @click="closeCallback" severity="secondary" class="w-full border-0 rounded-none rounded-bl-lg font-bold">
					{{ $t("dialogs.button.cancel") }}
				</Button>
				<Button
					v-if="!props.isEdit"
					@click="createUser"
					class="w-full border-0 bg-transparent text-create-600 hover:bg-create-600 hover:text-white rounded-none rounded-br-lg font-bold"
					:disabled="username === undefined || password === undefined || username === '' || password === ''"
				>
					<i class="pi pi-user-plus" /><span class="hidden md:inline">{{ $t("users.create_edit.create") }}</span>
				</Button>
				<Button
					v-else
					@click="editUser"
					severity="contrast"
					class="w-full border-0 rounded-none rounded-br-lg font-bold"
					:disabled="username === undefined || username === ''"
				>
					<i class="pi pi-user-edit" /><span class="hidden md:inline">{{ $t("users.create_edit.edit") }}</span>
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { Ref, ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import FloatLabel from "primevue/floatlabel";
import { useToast } from "primevue/usetoast";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import UserManagementService from "@/services/user-management-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import Textarea from "../basic/Textarea.vue";
import SETag from "@/components/icons/SETag.vue";
import UsersService from "@/services/users-service";
import { trans } from "laravel-vue-i18n";

const lycheeStore = useLycheeStateStore();
const { is_se_preview_enabled, is_se_enabled } = storeToRefs(lycheeStore);

const visible = defineModel("visible") as Ref<boolean>;
const props = defineProps<{
	user: App.Http.Resources.Models.UserManagementResource | undefined;
	isEdit: boolean;
}>();

const id = ref<number | undefined>(props.user?.id);
const username = ref<string | undefined>(props.user?.username);
const note = ref<string | undefined>(props.user?.note ?? undefined);
const password = ref<string | undefined>(undefined);
const may_edit_own_settings = ref(props.user?.may_edit_own_settings ?? false);
const may_upload = ref(props.user?.may_upload ?? false);
const has_quota = ref(props.user?.quota_kb !== undefined && props.user?.quota_kb !== null);
const quota_kb = ref(props.user?.quota_kb?.toString() ?? "0");

const toast = useToast();
const emits = defineEmits<{
	refresh: [];
}>();

function createUser() {
	if (username.value === undefined || password.value === undefined) {
		return;
	}

	UserManagementService.create({
		username: username.value,
		password: password.value,
		may_edit_own_settings: may_edit_own_settings.value,
		may_upload: may_upload.value,
		has_quota: is_se_enabled ? has_quota.value : undefined,
		quota_kb: is_se_enabled ? parseInt(quota_kb.value) : undefined,
		note: is_se_enabled ? note.value : undefined,
	})
		.then(() => {
			visible.value = false;
			password.value = undefined;
			may_upload.value = false;
			may_edit_own_settings.value = false;
			username.value = undefined;
			has_quota.value = false;
			quota_kb.value = "0";
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("users.user_created"), life: 3000 });
			emits("refresh");

			// Clear user count as it is cachable.
			UsersService.clearCount();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
		});
}

function editUser() {
	if (username.value === undefined || id.value === undefined) {
		return;
	}

	UserManagementService.edit({
		id: id.value,
		username: username.value,
		password: password.value,
		may_edit_own_settings: may_edit_own_settings.value,
		may_upload: may_upload.value,
		has_quota: is_se_enabled ? has_quota.value : undefined,
		quota_kb: is_se_enabled ? parseInt(quota_kb.value) : undefined,
		note: is_se_enabled ? note.value : undefined,
	})
		.then(() => {
			visible.value = false;
			password.value = undefined;
			may_upload.value = false;
			may_edit_own_settings.value = false;
			username.value = undefined;
			has_quota.value = false;
			quota_kb.value = "0";
			toast.add({ severity: "success", summary: trans("users.change_saved"), detail: trans("users.user_updated"), life: 3000 });
			emits("refresh");
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response.data.message, life: 3000 });
		});
}

watch(
	() => props.user,
	(newUser: App.Http.Resources.Models.UserManagementResource | undefined, _oldUser) => {
		id.value = newUser?.id;
		username.value = newUser?.username;
		may_edit_own_settings.value = newUser?.may_edit_own_settings ?? false;
		may_upload.value = newUser?.may_upload ?? false;
		has_quota.value = newUser?.quota_kb !== undefined && newUser?.quota_kb !== null;
		quota_kb.value = newUser?.quota_kb?.toString() ?? "0";
	},
);
</script>
