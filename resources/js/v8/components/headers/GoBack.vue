<template>
	<UButton v-if="isLTR()" icon="lucide:chevron-left" class="mr-2" color="neutral" variant="ghost" @click="emits('goBack')" />
	<UButton v-else icon="lucide:chevron-right" class="ml-2" color="neutral" variant="ghost" @click="emits('goBack')" />
</template>
<script setup lang="ts">
import { useLtRorRtL } from "@/utils/Helpers";

const { isLTR } = useLtRorRtL();

const emits = defineEmits<{
	goBack: [];
}>();
</script>
