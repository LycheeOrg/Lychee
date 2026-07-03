<template>
	<template v-if="props.icon">
		<MiniIcon v-if="!props.icon.startsWith('pi')" :icon="props.icon" :class="props.class ?? 'w-3 h-3'" />
		<UIcon v-else :name="iconifyName" :class="props.class ?? 'w-3 h-3'" />
	</template>
</template>
<script lang="ts" setup>
import { computed } from "vue";
import MiniIcon from "./MiniIcon.vue";
import { primeIconToIconifyName } from "@/v8/icons";

const props = defineProps<{
	icon: string | undefined;
	class?: string;
}>();

const iconifyName = computed(() => (props.icon ? primeIconToIconifyName(props.icon) : ""));
</script>
