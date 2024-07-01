<template>
	<header
		id="lychee_toolbar_container"
		class="px-2 w-full flex-none bg-gradient-to-b from-bg-700 to-bg-800 border-b border-b-solid border-b-neutral-900 {{ props.class }}"
	>
		<div class="flex w-full justify-between items-center box-border">
			<slot />
		</div>
	</header>
</template>
<script setup lang="ts">
const props = defineProps<{
	class?: string;
}>();
</script>
