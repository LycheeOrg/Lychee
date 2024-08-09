<template>
	<div class="py-1">
		<FloatLabel class="w-full flex-grow">
			<Select :id="props.config.key" class="w-96 border-none" v-model="val" :options="options" showClear @update:modelValue="update" />
			<label :for="props.config.key">{{ props.config.documentation }}</label>
		</FloatLabel>
		<ResetField v-if="changed" @click="reset" />
	</div>
</template>

<script setup lang="ts" generic="T extends string">
import { computed, ref, watch } from "vue";
import Select from "primevue/select";
import FloatLabel from "primevue/floatlabel";
import ResetField from "./ResetField.vue";
import SettingsService from "@/services/settings-service";

type Props = {
	config: App.Http.Resources.Models.ConfigResource;
};

const props = defineProps<Props>();

const val = ref(props.config.value);
const options = ref([] as string[]);

const changed = computed(() => val.value !== props.config.value);

const emits = defineEmits(["filled", "reset"]);

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
