<template>
	<div class="flex gap-4 justify-between items-center">
		<label class="w-full text-highlighted" :for="props.config.key" v-html="props.label ?? tDoc(props.config)" />
		<div class="flex gap-4 items-center">
			<ResetField v-if="changed" @click="reset" />
			<USelectMenu :id="props.config.key" v-model="val" class="border-none" :items="options" @update:model-value="update" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ResetField from "@/v8/components/forms/settings/ResetField.vue";
import SettingsService from "@/services/settings-service";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc } = useTranslation();

type Props = {
	config: App.Http.Resources.Models.ConfigResource;
	label?: string;
};

const props = defineProps<Props>();

const val = ref(props.config.value);
const options = ref<string[]>([]);

const changed = computed(() => val.value !== props.config.value);

const emits = defineEmits<{
	filled: [key: string, value: string];
	reset: [key: string];
}>();

SettingsService.getLanguages().then((response) => {
	options.value = response.data;
});

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
