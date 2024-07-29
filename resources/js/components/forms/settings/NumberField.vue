<template>
	<div class="flex items-center gap-4">
		<IconField>
			<InputNumber
				v-model="val"
				:inputId="props.config.key"
				:min="props.min"
				showButtons
				mode="decimal"
				:useGrouping="false"
				fluid
				class="w-28"
				@updated="update"
			/>
			<InputIcon class="pi pi-times" @click="reset" v-if="changed" />
		</IconField>
		<div>{{ props.config.documentation }}</div>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputNumber from "primevue/inputnumber";

const props = defineProps<{
	min: number;
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref(Number(props.config.value));

const changed = computed(() => val.value !== Number(props.config.value));

const emits = defineEmits(["filled", "reset"]);

function update() {
	emits("filled", props.config.key, val.value);
}

function reset() {
	emits("reset", props.config.key);
	val.value = Number(props.config.value);
}

// We watch props in case of updates.
watch(
	() => props.config,
	(newValue, _oldValue) => (val.value = Number(newValue.value)),
);
</script>

<style>
.p-inputtext {
	border: none;
}
</style>
