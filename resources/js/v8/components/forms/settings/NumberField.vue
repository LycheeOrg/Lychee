<template>
	<div>
		<div class="flex items-center gap-4 justify-between">
			<div class="w-1/2 sm:w-full" :class="props.config.require_se ? 'text-primary' : 'text-highlighted'">
				{{ tDoc(props.config) }}
				<SETag v-if="config.require_se" />
			</div>
			<div class="flex gap-4 items-center">
				<ResetField v-if="changed" @click="reset" />
				<UInputNumber
					v-model="val"
					:id="props.config.key"
					:min="props.min"
					:max="props?.max ?? undefined"
					class="w-28"
					@update:model-value="update"
				/>
			</div>
		</div>
		<div v-if="props.config.details" class="text-muted text-sm hidden sm:block" v-html="tDetails(props.config)" />
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ResetField from "@/v8/components/forms/settings/ResetField.vue";
import SETag from "@/v8/components/icons/SETag.vue";
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
