<template>
	<svg :class="'inline-block ' + props.fill + ' ' + props.class">
		<use :xlink:href="'#' + props.icon" />
	</svg>
</template>

<script setup lang="ts">
type IconProps = {
	class?: string;
	fill?: string;
	icon: string;
};

const props = withDefaults(defineProps<IconProps>(), {
	class: "",
	fill: "fill-neutral-400",
});
</script>
