<template>
	<template v-if="props.icon">
		<MiniIcon :icon="props.icon" :class="props.class ?? 'w-3 h-3'" v-if="!props.icon.startsWith('pi')" />
		<i v-else :class="props.icon + ' ' + props.class" />
	</template>
</template>
<script lang="ts" setup>
import MiniIcon from "./MiniIcon.vue";

const props = defineProps<{
	icon: string | undefined;
	class?: string;
}>();
</script>
