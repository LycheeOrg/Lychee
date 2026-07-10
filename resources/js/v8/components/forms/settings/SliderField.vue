<template>
	<div>
		<div class="items-center justify-between gap-4 hidden sm:flex">
			<div
				:class="{
					'text-primary': props.config.require_se,
					'text-highlighted': !props.config.require_se,
				}"
				v-html="props.label ?? tDoc(props.config)"
			/>
			<div class="items-center flex gap-2">
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
		<div
			v-if="props.config.details || details !== undefined"
			class="text-muted text-sm hidden sm:block"
			v-html="props.details ?? tDetails(props.config)"
		/>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import ResetField from "./ResetField.vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc, tDetails } = useTranslation();

const props = defineProps<{
	label?: string;
	config: App.Http.Resources.Models.ConfigResource;
	details?: string;
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
