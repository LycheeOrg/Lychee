<template>
	<div class="w-full flex flex-wrap md:flex-nowrap gap-2 justify-center">
		<FloatLabel class="w-1/3">
			<InputText id="username" v-model="username" aria-label="Username" />
			<label class="" for="username">{{ $t("lychee.USERNAME") }}</label>
		</FloatLabel>
		<FloatLabel class="w-1/3">
			<InputPassword id="password" v-model="password" aria-label="Password" />
			<label class="" for="password">{{ $t("lychee.PASSWORD") }}</label>
		</FloatLabel>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="may_upload" :binary="true" />
		</div>
		<div class="w-1/12 flex justify-center items-center">
			<Checkbox v-model="may_edit_own_settings" :binary="true" />
		</div>
		<Button @click="createUser" class="border-0 bg-surface text-create-600 hover:bg-create-600 hover:text-white w-1/6"
			><i class="pi pi-user-plus" /><span class="hidden md:inline">{{ $t("lychee.CREATE") }}</span></Button
		>
	</div>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import FloatLabel from "primevue/floatlabel";
import { useToast } from "primevue/usetoast";
import InputText from "@/components/forms/basic/InputText.vue";
import InputPassword from "@/components/forms/basic/InputPassword.vue";
import UserManagementService from "@/services/user-management-service";

const username = ref(undefined as string | undefined);
const password = ref(undefined as string | undefined);
const may_edit_own_settings = ref(false);
const may_upload = ref(false);

const toast = useToast();
const emits = defineEmits<{
	createUser: [];
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
		password.value = undefined;
		may_upload.value = false;
		may_edit_own_settings.value = false;
		username.value = undefined;
		toast.add({ severity: "success", summary: "Success", detail: "User created", life: 3000 });
		emits("createUser");
	});
}
</script>
