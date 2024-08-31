<template>
	<div class="flex gap-4">
		<!-- <IconField> -->
		<ToggleSwitch v-model="val" @update:modelValue="update" :input-id="props.config.key" class="text-sm translate-y-1"></ToggleSwitch>
		<!-- </IconField> -->
		<label :for="props.config.key">{{ props.config.documentation }}</label>
		<ResetField v-if="changed" @click="reset" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ToggleSwitch from "primevue/toggleswitch";
import ResetField from "@/components/forms/settings/ResetField.vue";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref((props.config.value === "1") as boolean);

const changed = computed(() => val.value !== (props.config.value === "1"));

const emits = defineEmits<{
	(e: "filled", key: string, value: string): void;
	(e: "reset", key: string): void;
}>();

function update() {
	if (changed.value) {
		emits("filled", props.config.key, val.value ? "1" : "0");
	} else {
		emits("reset", props.config.key);
	}
}

function reset() {
	emits("reset", props.config.key);
	val.value = props.config.value === "1";
}

// We watch props in case of updates.
watch(
	() => props.config,
	(newValue, _oldValue) => (val.value = newValue.value === "1"),
);
</script>
