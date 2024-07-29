<template>
	<div class="flex gap-4">
		<IconField>
			<ToggleSwitch v-model="val" @updated="update" class="text-sm translate-y-1"></ToggleSwitch>
			<InputIcon class="pi pi-times" @click="reset" v-if="changed" />
		</IconField>
		<p>{{ props.config.documentation }}</p>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import ToggleSwitch from "primevue/toggleswitch";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref((props.config.value === "1") as boolean);

const changed = computed(() => val.value !== (props.config.value === "1"));

const emits = defineEmits(["filled", "reset"]);

function update() {
	emits("filled", props.config.key, val.value);
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
