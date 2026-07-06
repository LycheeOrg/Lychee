<template>
	<div class="relative w-6">
		<!-- React when mouse over the thumb -->
		<div
			:class="{
				'absolute top-0 left-1/2 -translate-x-1/2 flex justify-center items-center h-8 w-6 text-white text-2xl filter-shadow': true,
				'md:opacity-0 md:group-hover:opacity-50 md:transition-all md:ease-out': !props.isFavourite,
			}"
		>
			<UIcon :name="props.isFavourite ? 'prime:heart-fill' : 'prime:heart'" />
		</div>
		<!-- React when mouse over the icon -->
		<div
			:class="{
				'absolute top-0 left-1/2 -translate-x-1/2 flex justify-center items-center h-8 w-6 text-white text-2xl cursor-pointer': true,
				'opacity-0 md:opacity-0 md:hover:opacity-100 md:transition-all md:ease-out': !props.isFavourite,
			}"
			@click="propagateClick"
		>
			<UIcon :name="props.isFavourite ? 'prime:heart' : 'prime:heart-fill'" />
		</div>
	</div>
</template>
<script setup lang="ts">
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
