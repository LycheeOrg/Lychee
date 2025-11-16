<template>
	<div class="flex flex-wrap items-center w-full">
		<div class="w-1/2" :class="props.config.require_se ? 'text-primary-emphasis' : 'text-muted-color-emphasis'">
			{{ props.config.key }}
			<sub v-if="props.config.order !== null" class="text-muted-color text-2xs"> ({{ props.config.order }}) </sub>
		</div>
		<IconField class="w-1/2">
			<InputText :id="props.config.key" v-model="val" type="text" @update:model-value="update" />
			<InputIcon v-if="changed" v-tooltip="'Click me to reset!'" :class="`pi ${classes} cursor-pointer`" @click="reset" />
		</IconField>
		<Message v-if="changed && isVersion" class="w-full h-8 mt-0.5" severity="error">We strongly recommend you do not modify this value.</Message>
		<div v-if="!changed || !isVersion" class="w-full text-muted-color">
			{{ tDoc(props.config) }}
			<br v-if="props.config.details" />
			<span v-html="tDetails(props.config)"></span>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Message from "primevue/message";
import InputText from "@/components/forms/basic/InputText.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc, tDetails } = useTranslation();

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref<string>(props.config.value);

const changed = computed(() => val.value !== props.config.value);
const isVersion = computed(() => props.config.key === "version");
const classes = computed(() => (isVersion.value ? "pi-exclamation-triangle text-danger-700" : "pi-exclamation-circle text-warning-600"));

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
