<template>
	<div class="flex gap-4 items-center justify-between">
		<label
			:for="props.config.key"
			:class="{
				'w-full': true,
				'text-primary': props.config.require_se,
				'text-highlighted': !props.config.require_se,
			}"
			v-html="props.label ?? tDoc(props.config)"
		/>
		<div class="flex gap-4 items-center">
			<ResetField v-if="changed" @click="reset" />
			<USelectMenu :id="props.config.key" v-model="val" class="border-none" :items="options" @update:model-value="update" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ResetField from "@/v8/components/forms/settings/ResetField.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc } = useTranslation();

type Props = {
	config: App.Http.Resources.Models.ConfigResource;
	label?: string;
};

const props = defineProps<Props>();

const val = ref(props.config.value);
const options = ref(props.config.type.split("|"));

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
