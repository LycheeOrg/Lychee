<template>
	<div>
		<div class="flex gap-4 justify-between flex-wrap sm:flex-nowrap">
			<!-- </IconField> -->
			<label
				:for="props.config.key"
				class="w-1/2 sm:w-full"
				:class="props.config.require_se ? 'text-primary-emphasis' : 'text-muted-color-emphasis'"
				v-html="props.label ?? props.config.documentation"
			/>
			<!-- <IconField> -->
			<span class="flex gap-4">
				<ResetField v-if="changed" @click="reset" />
				<ToggleSwitch v-model="val" @update:modelValue="update" :input-id="props.config.key" class="text-sm translate-y-1"></ToggleSwitch>
			</span>
		</div>
		<div v-if="props.config.details || details !== undefined" class="text-muted-color text-sm" v-html="props.details ?? props.config.details" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ToggleSwitch from "primevue/toggleswitch";
import ResetField from "@/components/forms/settings/ResetField.vue";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
	label?: string;
	details?: string;
}>();

const val = ref<boolean>(props.config.value === "1");

const changed = computed(() => val.value !== (props.config.value === "1"));

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
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
