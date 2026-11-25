<template>
	<small class="block text-end text-xs tabular-nums" :class="isTooLong ? 'text-error' : 'text-muted'" dir="ltr">{{ length }} / {{ max }}</small>
</template>
<script setup lang="ts">
/**
 * `used / max` under a description input.
 * Counts like the server's DescriptionRule, see descriptionLength().
 */
import { computed } from "vue";
import { storeToRefs } from "pinia";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { descriptionLength, isDescriptionTooLong } from "@/v8/utils/descriptionLength";

const props = defineProps<{
	value: string | null | undefined;
}>();

const { description_max_length: max } = storeToRefs(useLycheeStateStore());

const length = computed(() => descriptionLength(props.value));
const isTooLong = computed(() => isDescriptionTooLong(length.value, max.value));
</script>
