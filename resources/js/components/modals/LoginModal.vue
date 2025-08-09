<template>
	<Dialog v-model:visible="is_login_open" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container="{ closeCallback }">
			<LoginForm :close-callback="closeCallback" @logged-in="emits('logged-in')" />
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import Dialog from "primevue/dialog";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import LoginForm from "@/components/forms/auth/LoginForm.vue";

const emits = defineEmits<{
	"logged-in": [];
}>();

const togglableStore = useTogglablesStateStore();
const { is_login_open } = storeToRefs(togglableStore);
</script>
