<template>
	<Dialog v-model:visible="visible" class="border-none">
		<template #container="{ closeCallback }">
			<div class="p-9 w-full flex flex-col gap-2 justify-center">
				<FloatLabel class="w-full" variant="on">
					<InputText id="username" v-model="username" aria-label="Username" />
					<label class="" for="username">{{ $t("lychee.USERNAME") }}</label>
				</FloatLabel>
				<FloatLabel class="w-full" variant="on">
					<InputPassword id="password" v-model="password" aria-label="Password" />
					<label class="" for="password">{{ $t("lychee.PASSWORD") }}</label>
				</FloatLabel>
				<div class="w-full items-center text-muted-color">
					<Checkbox inputId="mayUpload" v-model="may_upload" :binary="true" />
					<label for="mayUpload" class="ml-2 cursor-pointer3">User can upload content.</label>
				</div>
				<div class="w-full items-center text-muted-color">
					<Checkbox inputId="mayEdit" v-model="may_edit_own_settings" :binary="true" />
					<label for="mayEdit" class="ml-2 cursor-pointer">User can modify their profile (username, password).</label>
				</div>
			</div>
			<div class="flex">
				<Button @click="closeCallback" severity="secondary" class="w-full border-0 rounded-none rounded-bl-lg font-bold">
					{{ $t("lychee.CANCEL") }}
				</Button>
				<Button
					v-if="!props.isEdit"
					@click="createUser"
					class="w-full border-0 bg-surface text-create-600 hover:bg-create-600 hover:text-white rounded-none rounded-br-lg font-bold"
					:disabled="username === undefined || password === undefined || username === '' || password === ''"
				>
					<i class="pi pi-user-plus" /><span class="hidden md:inline">{{ $t("lychee.CREATE") }}</span>
				</Button>
				<Button
					v-else
					@click="editUser"
					severity="contrast"
					class="w-full border-0 rounded-none rounded-br-lg font-bold"
					:disabled="username === undefined || username === ''"
				>
					<i class="pi pi-user-edit" /><span class="hidden md:inline">{{ $t("Edit") }}</span>
				</Button>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { Ref, ref, watch } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import FloatLabel from "primevue/floatlabel";
import { useToast } from "primevue/usetoast";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import UserManagementService from "@/services/user-management-service";
import Dialog from "primevue/dialog";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const lycheeStore = useLycheeStateStore();
lycheeStore.init();
const { is_se_preview_enabled, is_se_enabled } = storeToRefs(lycheeStore);

const visible = defineModel("visible") as Ref<boolean>;
const props = defineProps<{
	user: App.Http.Resources.Models.UserManagementResource | undefined;
	isEdit: boolean;
}>();

const id = ref(props.user?.id);
const username = ref(props.user?.username);
const password = ref(undefined as string | undefined);
const may_edit_own_settings = ref(props.user?.may_edit_own_settings ?? false);
const may_upload = ref(props.user?.may_upload ?? false);

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
	}).then(() => {
		visible.value = false;
		password.value = undefined;
		may_upload.value = false;
		may_edit_own_settings.value = false;
		username.value = undefined;
		toast.add({ severity: "success", summary: "Success", detail: "User created", life: 3000 });
		emits("refresh");
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
	}).then(() => {
		visible.value = false;
		password.value = undefined;
		may_upload.value = false;
		may_edit_own_settings.value = false;
		username.value = undefined;
		toast.add({ severity: "success", summary: "Change saved!", detail: "User updated", life: 3000 });
		emits("refresh");
	});
}

watch(
	() => props.user,
	(newUser: App.Http.Resources.Models.UserManagementResource | undefined, _oldUser) => {
		id.value = newUser?.id;
		username.value = newUser?.username;
		may_edit_own_settings.value = newUser?.may_edit_own_settings ?? false;
		may_upload.value = newUser?.may_upload ?? false;
	},
);
</script>
