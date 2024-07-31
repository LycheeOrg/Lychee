<template>
	<div class="py-1">
		<FloatLabel class="w-full flex-grow">
			<Select :id="props.config.key" class="w-96 border-none" v-model="val" optionLabel="label" :options="props.options" showClear>
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
			<label :for="props.config.key">{{ props.config.documentation }}</label>
		</FloatLabel>
	</div>
</template>

<script setup lang="ts" generic="T extends string">
import { computed, ref, watch } from "vue";
import Select from "primevue/select";
import { SelectOption } from "@/config/constants";
import FloatLabel from "primevue/floatlabel";

type Props = {
	config: App.Http.Resources.Models.ConfigResource;
	options: SelectOption<T>[];
	mapper: (value: string) => SelectOption<T> | undefined;
};

const props = defineProps<Props>();

const val = ref(props.mapper(props.config.value));

const changed = computed(() => val.value !== props.mapper(props.config.value));

const emits = defineEmits(["filled", "reset"]);

function update() {
	emits("filled", props.config.key, val.value?.value);
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
