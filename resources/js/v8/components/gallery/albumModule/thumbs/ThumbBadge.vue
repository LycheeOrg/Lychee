<template>
	<span class="badge inline-block ltr:ml-1 rtl:mr-1 px-2 pt-2 pb-1 rounded-md rounded-t-none text-white text-center" :class="spanClass">
		<UIcon v-if="props.pi" :name="piIconName" :class="piExtraClasses" />
		<svg v-if="props.icon" class="iconic inline w-4 h-4 fill-white">
			<use :xlink:href="iconHref" />
		</svg>
	</span>
</template>
<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	class: string;
	icon?: string;
	/** Full Iconify name, optionally followed by extra classes for the icon: "lucide:shield text-amber-500". */
	pi?: string;
	borderColor?: string;
}>();

const iconHref = computed(() => (props.icon ? `#${props.icon}` : ""));
const spanClass = computed(() => props.class + (props.borderColor ? ` ${props.borderColor}` : " border-solid border-white border border-t-0"));
// `pi` carries extra Tailwind classes space-separated after the icon name (e.g. "lucide:shield text-amber-500").
const piIconName = computed(() => props.pi?.split(" ")[0] ?? "");
const piExtraClasses = computed(() => props.pi?.split(" ").slice(1).join(" ") ?? "");
</script>
