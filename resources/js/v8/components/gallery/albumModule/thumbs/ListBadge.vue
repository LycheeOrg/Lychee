<template>
	<span class="inline-block ltr:ml-1 rtl:mr-1 text-center">
		<UIcon v-if="props.pi" :name="props.pi" :class="props.class" />
		<svg v-if="props.icon" class="iconic inline w-4 h-4" :class="props.class">
			<use :xlink:href="iconHref" />
		</svg>
	</span>
</template>
<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	class: string;
	icon?: string;
	/** Full Iconify name, e.g. "lucide:heart". Combine with FILL_OVERRIDE_CLASS in `class` for a solid look. */
	pi?: string;
}>();

const iconHref = computed(() => (props.icon ? `#${props.icon}` : ""));
</script>
