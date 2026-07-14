<template>
	<div
		:class="
			'v8-fieldset border rounded-lg border-b-0 ltr:border-r-0 ltr:rounded-r-none rtl:border-l-0 rtl:rounded-l-none rounded-b-none p-4 ' +
			(props.class ?? '')
		"
	>
		<template v-if="props.toggleable">
			<UCollapsible v-model:open="isOpen">
				<button type="button" class="flex items-center gap-2 w-full text-left font-semibold capitalize py-1">
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
			<div v-if="props.legend || $slots.legend" class="font-semibold capitalize pb-4">
				<slot name="legend">{{ props.legend }}</slot>
			</div>
			<slot />
		</template>
	</div>
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
