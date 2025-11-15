<template>
	<div class="relative w-6">
		<div
			:class="{
				'absolute top-0 left-1/2 -translate-x-1/2 flex justify-center items-center h-8 w-6 text-surface-0 text-2xl filter-shadow': true,
				'md:opacity-0 md:group-hover:opacity-50 md:transition-all md:ease-out': !props.isInBasket,
			}"
		>
			<span
				:class="{
					'pi pi-check-circle': props.isInBasket,
					'pi pi-cart-arrow-down': !props.isInBasket,
				}"
			></span>
		</div>
		<div
			:class="{
				'absolute top-0 left-1/2 -translate-x-1/2 flex justify-center items-center h-8 w-6 text-surface-0 text-2xl cursor-pointer': true,
				'opacity-0 md:opacity-0 md:hover:opacity-100 md:transition-all md:ease-out': !props.isInBasket,
			}"
			@click="propagateClick"
		>
			<span
				:class="{
					'pi pi-check-circle': props.isInBasket,
					'pi pi-cart-arrow-down': !props.isInBasket,
				}"
			></span>
		</div>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	isInBasket: boolean;
}>();

const emits = defineEmits<{
	click: [];
}>();
function propagateClick(e: MouseEvent) {
	e.stopPropagation();
	emits("click");
}
</script>
