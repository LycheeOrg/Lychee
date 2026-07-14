<template>
	<!-- A colon means the value is already a resolved Iconify name (e.g. "lucide:heart"); otherwise
			it's a legacy sprite id rendered by the old SVG-sprite icon system. -->
	<MiniIcon v-if="!props.icon?.includes(':')" :icon="props.icon" :class="props.class ?? 'w-3 h-3'" />
	<UIcon v-else :name="props.icon" :class="props.class ?? 'w-3 h-3'" size="1rem" />
</template>
<script lang="ts" setup>
import MiniIcon from "./MiniIcon.vue";

const props = defineProps<{
	icon: string;
	class?: string;
}>();
</script>
