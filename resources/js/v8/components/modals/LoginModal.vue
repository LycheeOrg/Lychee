<template>
	<UModal v-model:open="is_login_open">
		<template #body>
			<LoginForm @logged-in="emits('logged-in')" :close-callback="() => (is_login_open = false)" />
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import LoginForm from "@/v8/components/forms/auth/LoginForm.vue";

const emits = defineEmits<{
	"logged-in": [];
}>();

const togglableStore = useTogglablesStateStore();
const { is_login_open } = storeToRefs(togglableStore);
</script>
