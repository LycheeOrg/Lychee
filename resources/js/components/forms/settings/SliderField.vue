<template>
	<div class="items-center justify-between gap-4 hidden sm:flex">
		<div
			:class="{
				'text-primary-emphasis': props.config.require_se,
				'text-muted-color-emphasis': !props.config.require_se,
			}"
			v-html="props.label ?? props.config.documentation"
		/>
		<SelectButton class="border-none" v-model="val" :options="options" aria-labelledby="basic" @update:modelValue="update" />
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import SelectButton from "primevue/selectbutton";

const props = defineProps<{
	label?: string;
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref<string>(props.config.value);
const options = ref(props.config.type.split("|"));

const changed = computed(() => val.value !== props.config.value);

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
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
