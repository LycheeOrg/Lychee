<template>
	<div class="items-center flex-wrap gap-4 justify-between hidden sm:flex">
		<div class="text-highlighted">{{ tDoc(props.config) }}</div>
		<div class="flex gap-4 items-center">
			<ResetField v-if="changed" @click="reset" />
			<UFieldGroup>
				<UButton
					v-for="option in options"
					:key="option"
					size="sm"
					:color="val === option ? 'primary' : 'neutral'"
					:variant="val === option ? 'solid' : 'outline'"
					@click="
						val = option;
						update();
					"
				>
					{{ option }}
				</UButton>
			</UFieldGroup>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ResetField from "@/v8/components/forms/settings/ResetField.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc } = useTranslation();

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref<string>(props.config.value);
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
