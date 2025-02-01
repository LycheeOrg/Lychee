<template>
	<div>
		<div class="flex items-center justify-between gap-x-4 flex-wrap sm:flex-nowrap">
			<label
				:for="props.config.key"
				:class="{
					'w-full': true,
					'text-primary-emphasis': props.config.require_se,
					'text-muted-color-emphasis': !props.config.require_se,
				}"
				v-html="props.label ?? props.config.documentation"
			/>
			<FloatLabel class="w-full grow">
				<IconField>
					<InputText :id="props.config.key" type="text" class="!py-1" v-model="val" @update:modelValue="update" />
					<InputIcon
						class="pi pi-exclamation-circle text-warning-600 cursor-pointer"
						@click="reset"
						v-if="changed"
						v-tooltip="'Click me to reset!'"
					/>
				</IconField>
			</FloatLabel>
		</div>
		<div
			v-if="props.config.details || details !== undefined"
			class="text-muted-color text-sm hidden sm:block"
			v-html="props.details ?? props.config.details"
		/>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import FloatLabel from "primevue/floatlabel";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "@/components/forms/basic/InputText.vue";

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
	label?: string;
	details?: string;
}>();

const val = ref<string>(props.config.value);

const changed = computed(() => val.value !== props.config.value);

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
