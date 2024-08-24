<template>
	<div class="flex items-center gap-4">
		<InputNumber
			v-model="val"
			:inputId="props.config.key"
			:min="props.min"
			:max="props?.max ?? undefined"
			showButtons
			mode="decimal"
			:useGrouping="false"
			fluid
			class="w-28"
			@update:modelValue="update"
		/>
		<div>{{ props.config.documentation }}</div>
		<ResetField v-if="changed" @click="reset" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import InputNumber from "primevue/inputnumber";
import ResetField from "@/components/forms/settings/ResetField.vue";

const props = defineProps<{
	min: number;
	max?: number;
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref(Number(props.config.value));

const changed = computed(() => val.value !== Number(props.config.value));

const emits = defineEmits<{
	(e: "filled", key: string, value: string): void;
	(e: "reset", key: string): void;
}>();

function update() {
	emits("filled", props.config.key, `${val.value}`);
}

function reset() {
	emits("reset", props.config.key);
	val.value = Number(props.config.value);
}

// We watch props in case of updates.
watch(
	() => props.config,
	(newValue, _oldValue) => (val.value = Number(newValue.value)),
);
</script>

<style>
.p-inputnumber-input {
	border: none;
}
</style>
