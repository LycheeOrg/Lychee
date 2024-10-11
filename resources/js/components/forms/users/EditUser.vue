<template>
	<div class="w-full flex flex-wrap md:flex-nowrap gap-2 justify-center">
		<InputText v-model="username" class="!w-1/3" aria-label="Username" />
		<InputPassword v-model="password" class="!w-1/3" aria-label="Password" />
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="may_upload" :binary="true" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="may_edit_own_settings" :binary="true" />
		</div>
		<Button v-if="isModified" @click="saveUser" severity="primary" class="border-0 w-1/6">
			<i class="pi pi-user-edit" /><span class="hidden md:inline">{{ $t("lychee.SAVE") }}</span></Button
		>
		<Button v-if="!isModified" @click="deleteUser" class="border-0 bg-surface text-danger-600 hover:bg-danger-700 hover:text-white w-1/6">
			<i class="pi pi-user-minus" /><span class="hidden md:inline">{{ $t("lychee.DELETE") }}</span></Button
		>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import { useToast } from "primevue/usetoast";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import UserManagementService from "@/services/user-management-service";

const props = defineProps<{
	user: App.Http.Resources.Models.UserManagementResource;
}>();

const user = props.user;
const toast = useToast();

const id = ref(props.user.id);
const username = ref(props.user.username);
const password = ref(undefined as string | undefined);
const may_edit_own_settings = ref(props.user.may_edit_own_settings);
const may_upload = ref(props.user.may_upload);

const isModified = computed(() => {
	return (
		username.value !== user.username ||
		password.value !== undefined ||
		may_edit_own_settings.value !== user.may_edit_own_settings ||
		may_upload.value !== user.may_upload
	);
});
const emits = defineEmits<{
	deleteUser: [id: number];
}>();

function saveUser() {
	UserManagementService.edit({
		id: id.value,
		username: username.value,
		password: password.value,
		may_edit_own_settings: may_edit_own_settings.value,
		may_upload: may_upload.value,
	}).then(() => {
		password.value = undefined;
		user.may_upload = may_upload.value;
		user.may_edit_own_settings = may_edit_own_settings.value;
		user.username = username.value;
		toast.add({ severity: "success", summary: "Change saved!", detail: "User updated", life: 3000 });
	});
}

function deleteUser() {
	emits("deleteUser", id.value);
}

watch(
	() => props.user,
	(newUser: App.Http.Resources.Models.UserManagementResource, _oldUser) => {
		id.value = newUser.id;
		username.value = newUser.username;
		may_edit_own_settings.value = newUser.may_edit_own_settings;
		may_upload.value = newUser.may_upload;
	},
);
</script>
