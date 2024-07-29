<template>
	<div class="flex">
		<span class="mr-4">{{ props.config.documentation }}:</span>
		<InputOtp v-model="val" :length="6" style="gap: 0" integerOnly>
			<template #default="{ attrs, events, index }">
				<input type="text" v-bind="attrs" v-on="events" class="h-4 w-3 text-center border-b border-primary-500" />
				<div v-if="index === 2 || index === 4" class="">.</div>
			</template>
		</InputOtp>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import InputOtp from "primevue/inputotp";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref(props.config.value);

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
