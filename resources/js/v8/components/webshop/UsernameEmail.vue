<template>
	<span v-if="props.username">{{ props.username }}</span>
	<a :href="'mailto:' + props.email" v-if="props.username && props.email" class="hover:text-primary-400">({{ props.email }})</a>
	<a :href="'mailto:' + props.email" v-else class="hover:text-primary-400">{{ props.email }}</a>
</template>
<script setup lang="ts">
const props = defineProps<{
	username: string | undefined | null;
	email: string | undefined | null;
}>();
</script>
