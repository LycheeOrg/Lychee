<template>
	<Fieldset
		:legend="props.legend"
		:class="'border-b-0 ltr:border-r-0 ltr:rounded-r-none rtl:border-l-0 rtl:rounded-l-none rounded-b-none ' + (props.class ?? '')"
		:toggleable="props.toggleable"
		:collapsed="collapsed"
		:pt:legendlabel:class="'capitalize'"
	>
		<slot />
	</Fieldset>
</template>
<script setup lang="ts">
import Fieldset from "primevue/fieldset";

const props = defineProps<{
	legend?: string;
	toggleable?: boolean;
	class?: string;
}>();
const collapsed = defineModel<boolean | undefined>("collapsed");
</script>
