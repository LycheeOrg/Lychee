<template>
	<!-- Both albumId and photoId exist: title is a clickable link to the photo in its album -->
	<RouterLink
		v-if="albumId !== null && photoId !== null"
		:to="{ name: 'album', params: { albumId: albumId, photoId: photoId } }"
		target="_blank"
		class="hover:underline"
	>
		{{ title }}
	</RouterLink>

	<!-- Album is gone but photoId is known: show a red forbidden icon and the photo ID -->
	<span v-else-if="albumId === null && photoId !== null" class="inline-flex items-center gap-1">
		<i class="pi pi-ban text-red-600 text-xs" />
		<span class="font-mono text-xs">{{ photoId }}</span>
	</span>

	<!-- Both are missing: show the title in italic muted style as a fallback -->
	<span v-else class="italic text-muted-color">{{ title }}</span>
</template>
<script setup lang="ts">
import { RouterLink } from "vue-router";

defineProps<{
	photoId: string | null;
	albumId: string | null;
	title: string | null;
}>();
</script>
