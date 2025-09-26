<template>
	<InputNumber
		v-model="modelValue"
		placeholder="Price"
		inputId="currency-us"
		mode="currency"
		:currency="props.currency"
		locale="en-US"
		class="border-0 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400 p-inputcurrency"
		@update:model-value="updateValue"
		@blur="updateValue"
	/>
</template>
<script setup lang="ts">
import { onMounted, ref, watch } from "vue";
import InputNumber from "primevue/inputnumber";

const emits = defineEmits(["update:modelValue"]);

const props = defineProps<{
	value: number | null;
	currency: string;
}>();

// We devide by 100 to convert cents to currency units.
const modelValue = ref(0);

function updateValue() {
	// We multiply by 100 to convert currency units to cents.
	const newValue = modelValue.value !== null ? Math.round(modelValue.value * 100) : null;
	emits("update:modelValue", newValue);
}

onMounted(() => {
	modelValue.value = (props.value ?? 0) / 100;
});

watch(
	() => props.value,
	(newValue: number | null) => {
		modelValue.value = (newValue ?? 0) / 100;
	},
);
</script>
<style lang="css">
.p-inputcurrency input {
	border: none;
}
</style>
