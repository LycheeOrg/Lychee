<template>
	<div class="flex flex-wrap items-center w-full">
		<div class="w-1/2" :class="props.config.require_se ? 'text-primary-emphasis' : 'text-highlighted'">
			{{ props.config.key }}
			<sub v-if="props.config.order !== null" class="text-muted text-2xs"> ({{ props.config.order }}) </sub>
		</div>
		<UInput :id="props.config.key" v-model="val" type="text" class="w-1/2" @update:model-value="update">
			<template v-if="changed" #trailing>
				<UTooltip text="Click me to reset!">
					<UIcon :name="iconName" :class="[iconColorClass, 'cursor-pointer']" @click="reset" />
				</UTooltip>
			</template>
		</UInput>
		<UAlert
			v-if="changed && isVersion"
			class="w-full h-8 mt-0.5"
			color="error"
			description="We strongly recommend you do not modify this value."
		/>
		<div v-if="!changed || !isVersion" class="w-full text-muted">
			{{ tDoc(props.config) }}
			<br v-if="props.config.details" />
			<span v-html="tDetails(props.config)"></span>
		</div>
	</div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useTranslation } from "@/composables/useTranslation";

const { tDoc, tDetails } = useTranslation();

const props = defineProps<{
	config: App.Http.Resources.Models.ConfigResource;
}>();

const val = ref<string>(props.config.value);

const changed = computed(() => val.value !== props.config.value);
const isVersion = computed(() => props.config.key === "version");
const iconName = computed(() => (isVersion.value ? "prime:exclamation-triangle" : "prime:exclamation-circle"));
const iconColorClass = computed(() => (isVersion.value ? "text-error" : "text-warning-600"));

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
