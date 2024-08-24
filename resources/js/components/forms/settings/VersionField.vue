<template>
	<div class="flex">
		<span class="mr-4">{{ props.config.documentation }}:</span>
		<Inplace style="--p-inplace-padding: 0">
			<template #display>{{ computedVersion }}</template>
			<template #content>
				<InputOtp v-model="val" :length="6" style="gap: 0" integerOnly @update:modelValue="update">
					<template #default="{ attrs, events, index }">
						<input type="text" v-bind="attrs" v-on="events" class="h-4 w-3 text-center border-b border-primary-500" />
						<div v-if="index === 2 || index === 4" class="">.</div>
					</template>
				</InputOtp>
			</template>
		</Inplace>
		<i
			class="ml-4 pi pi-exclamation-triangle text-danger-700 mt-1 cursor-pointer"
			v-tooltip="'Click me to reset!'"
			v-if="changed"
			@click="reset"
		></i>
	</div>
	<Message v-if="changed" severity="error">We strongly recommend you do not modify this value.</Message>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import InputOtp from "primevue/inputotp";
import Inplace from "primevue/inplace";
import Message from "primevue/message";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref(props.config.value);

const changed = computed(() => val.value !== props.config.value);

const computedVersion = computed(() => {
	const value = val.value;
	let version = "";
	for (let i = 0; i < 6; i += 2) {
		if (version !== "") {
			version += ".";
		}
		if (value[i] !== "0") {
			version += value[i];
		}
		version += value[i + 1];
	}
	return version;
});

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
