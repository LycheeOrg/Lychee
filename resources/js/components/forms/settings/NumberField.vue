<template>
	<div>
		<div class="flex items-center gap-4 justify-between">
			<div class="w-1/2 sm:w-full" :class="props.config.require_se ? 'text-primary-emphasis' : 'text-muted-color-emphasis'">
				{{ tDoc(props.config) }}
				<SETag v-if="config.require_se" />
			</div>
			<div class="flex gap-4 items-center">
				<ResetField v-if="changed" @click="reset" />
				<InputNumber
					v-model="val"
					:input-id="props.config.key"
					:min="props.min"
					:max="props?.max ?? undefined"
					show-buttons
					mode="decimal"
					:use-grouping="false"
					input-class="text-right pr-10"
					fluid
					class="w-28"
					@update:model-value="update"
				/>
			</div>
		</div>
		<div v-if="props.config.details" class="text-muted-color text-sm hidden sm:block" v-html="tDetails(props.config)" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import InputNumber from "primevue/inputnumber";
import ResetField from "@/components/forms/settings/ResetField.vue";
import SETag from "@/components/icons/SETag.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc, tDetails } = useTranslation();

const props = defineProps<{
	min: number;
	max?: number;
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref<number>(Number(props.config.value));

const changed = computed(() => val.value !== Number(props.config.value));

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

function update() {
	emits("filled", props.config.key, `${val.value}`);
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
.p-inputnumber-input {
	border: none;
}
</style>
