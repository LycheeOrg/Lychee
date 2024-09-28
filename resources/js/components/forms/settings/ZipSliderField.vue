<template>
	<div class="items-center flex-wrap my-4 gap-4 justify-between hidden sm:flex">
		<div>{{ props.config.documentation }}</div>
		<SelectButton
			id="albumSortingColumn"
			class="border-none"
			v-model="val"
			:options="options"
			aria-labelledby="basic"
			@update:modelValue="update"
		/>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import SelectButton from "primevue/selectbutton";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref(props.config.value as string);
const options = ref(props.config.type.split("|"));

const changed = computed(() => val.value !== props.config.value);

const emits = defineEmits<{
	(e: "filled", key: string, value: string): void;
	(e: "reset", key: string): void;
}>();

function update() {
	emits("filled", props.config.key, val.value);
}

function reset() {
	emits("reset", props.config.key);
	val.value = props.config.value;
}

// We watch props in case of updates.
watch(
	() => props.config,
	(newValue, _oldValue) => (val.value = newValue.value),
);
</script>
