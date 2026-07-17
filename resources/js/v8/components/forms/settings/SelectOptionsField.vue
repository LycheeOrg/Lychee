<template>
	<div class="flex justify-between items-center gap-4">
		<label
			:class="{
				'w-1/2 sm:w-full': true,
				'text-primary': props.config.require_se,
				'text-highlighted': !props.config.require_se,
			}"
			:for="props.config.key"
			v-html="props.label ?? tDoc(props.config)"
		/>
		<div class="flex gap-4 items-center">
			<ResetField v-if="changed" @click="reset" />
			<USelectMenu
				:id="props.config.key"
				v-model="val"
				class="border-none"
				label-key="label"
				:items="props.options"
				@update:model-value="update"
			>
				<template #default="{ modelValue }">{{ selectedLabel(modelValue) }}</template>
				<template #item-label="{ item }">{{ $t(item.label) }}</template>
			</USelectMenu>
		</div>
	</div>
	<div
		v-if="props.config.details || props.details !== undefined"
		class="w-full text-muted text-sm"
		v-html="props.details ?? tDetails(props.config)"
	/>
</template>

<script setup lang="ts" generic="T extends string">
import { computed, ref, watch, type Ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { SelectOption } from "@/config/constants";
import ResetField from "@/v8/components/forms/settings/ResetField.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc, tDetails } = useTranslation();

function selectedLabel(option: SelectOption<T> | undefined): string {
	return option ? trans(option.label) : "";
}

type Props = {
	config: App.Http.Resources.Models.ConfigResource;
	options: SelectOption<T>[];
	mapper: (value: string) => SelectOption<T> | undefined;
	label?: string;
	details?: string;
};

const props = defineProps<Props>();

const val = ref(props.mapper(props.config.value)) as Ref<SelectOption<T> | undefined>;

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
