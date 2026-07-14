<template>
	<div class="relative w-6">
		<!-- React when mouse over the thumb -->
		<div
			:class="{
				'absolute top-0 left-1/2 -translate-x-1/2 flex justify-center items-center h-8 w-6 text-white text-2xl filter-shadow': true,
				'md:opacity-0 md:group-hover:opacity-50 md:transition-all md:ease-out': !props.isFavourite,
			}"
		>
			<UIcon name="lucide:heart" :class="props.isFavourite ? FILL_OVERRIDE_CLASS : ''" />
		</div>
		<!-- React when mouse over the icon -->
		<div
			:class="{
				'absolute top-0 left-1/2 -translate-x-1/2 flex justify-center items-center h-8 w-6 text-white text-2xl cursor-pointer': true,
				'opacity-0 md:opacity-0 md:hover:opacity-100 md:transition-all md:ease-out': !props.isFavourite,
			}"
			@click="propagateClick"
		>
			<UIcon name="lucide:heart" :class="props.isFavourite ? '' : FILL_OVERRIDE_CLASS" />
		</div>
	</div>
</template>
<script setup lang="ts">
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";

const props = defineProps<{
	isFavourite: boolean;
}>();
const emits = defineEmits<{
	click: [];
}>();
function propagateClick(e: MouseEvent) {
	e.stopPropagation();
	emits("click");
}
</script>
