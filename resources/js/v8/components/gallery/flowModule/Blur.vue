<template>
	<div
		v-if="isBlurred"
		class="cursor-pointer absolute top-0 left-0 w-full h-full bg-[url(/img/noise.png)] backdrop-blur transition-opacity duration-1000 hover:opacity-0 flex justify-center items-center"
		@click="are_nsfw_consented = true"
	>
		<slot></slot>
	</div>
</template>
<script setup lang="ts">
import { useFlowStateStore } from "@/stores/FlowState";
import { storeToRefs } from "pinia";
import { computed } from "vue";

const flowState = useFlowStateStore();
const { are_nsfw_blurred, are_nsfw_consented } = storeToRefs(flowState);
const isBlurred = computed(() => are_nsfw_blurred.value && !are_nsfw_consented.value);
</script>
