<template>
	<div class="flex flex-col gap-4">
		<USwitch v-model="is_expert_mode" :ui="{ label: 'text-muted flex items-center gap-2' }">
			<template #label>{{ $t("gallery.album.properties.expert_mode") }}<UIcon name="lucide:graduation-cap" size="1.25em" /></template>
		</USwitch>
		<p class="text-xs text-muted">{{ $t("gallery.album.properties.autosave_hint") }}</p>
	</div>
</template>
<script setup lang="ts">
const is_expert_mode = defineModel<boolean>({ required: true });
</script>
