<template>
	<fieldset
		:class="
			'v8-fieldset relative min-w-0 border border-default rounded-lg border-b-0 ltr:border-r-0 ltr:rounded-r-none rtl:border-l-0 rtl:rounded-l-none rounded-b-none p-4 ' +
			(props.class ?? '')
		"
	>
		<template v-if="props.toggleable">
			<UCollapsible v-model:open="isOpen">
				<!-- `CollapsibleRoot` wraps trigger+content in its own element, so this button never ends
				up a direct child of <fieldset> the way <legend> below does - the browser's native
				border-notch trick isn't available, and a plain negative margin collapses through that
				wrapper and gets clamped at the fieldset's own padding. Absolute positioning (relative to
				the fieldset itself) escapes both limitations to fake the same overlap. -->
				<button
					type="button"
					class="absolute -top-4 ltr:left-2 rtl:right-2 inline-flex items-center gap-2 bg-default px-2 py-1 text-left font-semibold capitalize"
				>
					<UIcon name="lucide:chevron-down" class="transition-transform" :class="{ '-rotate-90': !isOpen }" />
					<slot name="legend">{{ props.legend }}</slot>
				</button>
				<template #content>
					<div class="pt-4">
						<slot />
					</div>
				</template>
			</UCollapsible>
		</template>
		<template v-else>
			<!-- A native <legend> straddles the fieldset's own top border automatically (the browser
			cuts a notch for it); no custom positioning needed to get that classic overlap look. -->
			<legend v-if="props.legend || $slots.legend" class="px-2 font-semibold capitalize">
				<slot name="legend">{{ props.legend }}</slot>
			</legend>
			<slot />
		</template>
	</fieldset>
</template>
<script setup lang="ts">
import { ref } from "vue";

const props = defineProps<{
	legend?: string;
	toggleable?: boolean;
	class?: string;
}>();
const collapsed = defineModel<boolean | undefined>("collapsed");
const isOpen = ref(!(collapsed.value ?? false));
</script>
