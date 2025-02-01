<template>
	<div class="w-full flex flex-wrap md:flex-nowrap gap-2 justify-center items-center h-12">
		<span class="w-1/4 text-sm text-muted-color">{{ formattedCreatedAt }}</span>
		<InputText v-model="alias" class="!w-1/2 pb-1" aria-label="Alias" :invalid="isInvalid" />
		<span v-if="isInvalid" class="w-1/4 text-xs text-muted-color text-center">{{ $t("profile.u2f.5_chars") }}</span>
		<Button
			v-if="isModified && !isInvalid"
			@click="saveU2F"
			class="border-0 text-primary-500 bg-transparent hover:bg-primary-400 hover:text-white w-1/4"
		>
			<i class="pi pi-save" /><span class="hidden md:inline">{{ $t("dialogs.button.save") }}</span></Button
		>
		<Button v-if="!isModified" @click="deleteU2F" class="border-0 bg-transparent text-danger-600 hover:bg-danger-700 hover:text-white w-1/4">
			<i class="pi pi-trash" /><span class="hidden md:inline">{{ $t("dialogs.button.delete") }}</span></Button
		>
	</div>
</template>
<script setup lang="ts">
import WebAuthnService from "@/services/webauthn-service";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { watch } from "vue";
import { computed, ref } from "vue";
import InputText from "../basic/InputText.vue";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	u2f: App.Http.Resources.Models.WebAuthnResource;
}>();

const toast = useToast();

const u2f = ref(props.u2f);
const id = ref(props.u2f.id);
const alias = ref(props.u2f.alias ?? "");
const created_at = ref(props.u2f.created_at);
const isModified = computed(() => alias.value !== (u2f.value.alias ?? ""));
const isInvalid = computed(() => isModified.value && alias.value.length < 5);

const formattedCreatedAt = computed(() => {
	const date = created_at.value.slice(0, 10).split("-");
	const time = created_at.value.slice(11, 16).split(":");
	return `${date[2]}/${date[1]}/${date[0]} ${time[0]}h${time[1]}`;
});

const emits = defineEmits<{
	delete: [id: string];
}>();

function saveU2F() {
	WebAuthnService.edit(id.value, alias.value).then(() => {
		u2f.value.alias = alias.value;
		toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("profile.u2f.credential_updated"), life: 3000 });
	});
}

function deleteU2F() {
	emits("delete", id.value);
}

watch(
	() => props.u2f,
	(newU2f: App.Http.Resources.Models.WebAuthnResource, _oldU2F) => {
		id.value = newU2f.id;
		alias.value = newU2f.alias ?? "";
	},
);
</script>
