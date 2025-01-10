<template>
	<div class="flex gap-4 justify-between items-center">
		<label class="w-full text-muted-color-emphasis" :for="props.config.key" v-html="props.label ?? props.config.documentation" />
		<div class="flex gap-4 items-center">
			<ResetField v-if="changed" @click="reset" />
			<Select :id="props.config.key" class="border-none" v-model="val" :options="options" @update:modelValue="update" />
		</div>
	</div>
</template>

<script setup lang="ts" generic="T extends string">
import { computed, ref, watch } from "vue";
import Select from "primevue/select";
import ResetField from "@/components/forms/settings/ResetField.vue";
import SettingsService from "@/services/settings-service";

type Props = {
	config: App.Http.Resources.Models.ConfigResource;
	label?: string;
};

const props = defineProps<Props>();

const val = ref(props.config.value);
const options = ref<string[]>([]);

const changed = computed(() => val.value !== props.config.value);

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

SettingsService.getLanguages().then((response) => {
	options.value = response.data;
});

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
