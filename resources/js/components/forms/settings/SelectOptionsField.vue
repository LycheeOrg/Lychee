<template>
	<div class="flex justify-between items-center gap-4">
		<label
			:class="{
				'w-1/2 sm:w-full': true,
				'text-primary-emphasis': props.config.require_se,
				'text-muted-color-emphasis': !props.config.require_se,
			}"
			:for="props.config.key"
			v-html="props.label ?? props.config.documentation"
		/>
		<div class="flex gap-4 items-center">
			<ResetField v-if="changed" @click="reset" />
			<Select :id="props.config.key" class="border-none" v-model="val" optionLabel="label" :options="props.options" @update:modelValue="update">
				<template #value="slotProps">
					<div v-if="slotProps.value" class="flex items-center">
						<div>{{ $t(slotProps.value.label) }}</div>
					</div>
				</template>
				<template #option="slotProps">
					<div class="flex items-center">
						<div>{{ $t(slotProps.option.label) }}</div>
					</div>
				</template>
			</Select>
		</div>
	</div>
	<div
		v-if="props.config.details || props.details !== undefined"
		class="w-full text-muted-color text-sm -mt-4"
		v-html="props.details ?? props.config.details"
	/>
</template>

<script setup lang="ts" generic="T extends string">
import { computed, ref, watch } from "vue";
import Select from "primevue/select";
import { SelectOption } from "@/config/constants";
import ResetField from "@/components/forms/settings/ResetField.vue";

type Props = {
	config: App.Http.Resources.Models.ConfigResource;
	options: SelectOption<T>[];
	mapper: (value: string) => SelectOption<T> | undefined;
	label?: string;
	details?: string;
};

const props = defineProps<Props>();

const val = ref(props.mapper(props.config.value));

const changed = computed(() => val.value?.value !== props.mapper(props.config.value)?.value);

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

function update() {
	emits("filled", props.config.key, val.value?.value as string);
}

function reset() {
	emits("reset", props.config.key);
	val.value = props.mapper(props.config.value);
}

// We watch props in case of updates.
watch(
	() => props.config,
	(newValue, _oldValue) => (val.value = props.mapper(newValue.value)),
);
</script>
