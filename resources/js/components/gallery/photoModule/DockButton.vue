<template>
	<a class="px-3 py-2.5 cursor-pointer transition-all duration-300 ease-in h-full inline-flex" :class="props.class">
		<MiniIcon v-if="props.icon" :icon="props.icon" fill="" class="my-0 w-4 h-4 mr-0 ml-0" />
		<i :class="piClass" class="m-0" />
	</a>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { computed } from "vue";

const props = defineProps<{
	icon?: string;
	class?: string;
	pi?: string;
}>();

const piClass = computed(() => (props.pi ? `pi pi-${props.pi}` : ""));
</script>
