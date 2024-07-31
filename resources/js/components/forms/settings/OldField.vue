<template>
	<div class="py-1 flex flex-wrap">
		<div class="w-1/2">{{ props.config.key }}</div>
		<IconField class="w-1/2">
			<InputText :id="props.config.key" type="text" class="!py-1" v-model="val" @updated="update" />
			<InputIcon class="pi pi-times" @click="reset" v-if="changed" />
		</IconField>
		<div class="w-full text-muted-color">{{ props.config.documentation }}</div>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import InputText from "../basic/InputText.vue";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref(props.config.value as string);

const changed = computed(() => val.value !== props.config.value);

const emits = defineEmits(["filled", "reset"]);

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
