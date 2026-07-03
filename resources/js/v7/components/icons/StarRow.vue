<template>
	<div
		class="flex items-center"
		:class="{
			'h-3.5 gap-px': props.size === 'small',
			'h-5 gap-0.5': props.size === 'medium',
			'h-6 gap-0.5': props.size === 'large',
		}"
	>
		<span
			class="block px-0.5"
			v-for="index in 5"
			:key="`avg-star-${index}`"
			:class="{
				'w-3.5 h-3.5': props.size === 'small',
				'w-5 h-5': props.size === 'medium',
				'w-6 h-6': props.size === 'large',
			}"
		>
			<i
				:class="{
					absolute: true,
					'pi pi-star-half-fill text-amber-500': index === Math.ceil(props.rating) && props.rating % 1 >= 0.25 && props.rating % 1 < 0.75,
					'pi pi-star  text-muted-color': index > Math.ceil(props.rating) || (index === Math.ceil(props.rating) && props.rating % 1 < 0.25),
					'text-xs': props.size === 'small',
					'text-lg': props.size === 'medium',
					'text-xl': props.size === 'large',
				}"
			/>
			<i
				:class="{
					absolute: true,
					'pi pi-star-fill text-amber-500':
						index <= Math.floor(props.rating) || (index === Math.ceil(props.rating) && props.rating % 1 >= 0.75),
					'text-xs': props.size === 'small',
					'text-lg': props.size === 'medium',
					'text-xl': props.size === 'large',
				}"
			/>
		</span>
	</div>
</template>
<script lang="ts" setup>
const props = defineProps<{
	rating: number;
	size: "small" | "medium" | "large";
}>();
</script>
