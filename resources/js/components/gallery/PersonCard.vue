<template>
	<div
		class="cursor-pointer hover:shadow-lg transition-shadow duration-200 rounded-xl overflow-hidden w-40"
		@click="$router.push({ name: 'person', params: { personId: person.id } })"
		@contextmenu.prevent="emit('contextmenu', $event)"
	>
		<div class="mx-auto aspect-square overflow-hidden bg-surface-800 flex items-center justify-center rounded-full w-18 h-18">
			<img v-if="person.representative_crop_url" :src="person.representative_crop_url" :alt="person.name" class="w-full h-full object-cover" />
			<i v-else class="pi pi-user text-6xl text-muted-color" />
		</div>
		<div class="flex items-center gap-2 justify-center">
			<span class="text-lg truncate font-semibold text-color">{{ person.name }}</span>
		</div>
		<div class="flex gap-2 items-center justify-center">
			<div class="text-sm text-muted-color">{{ person.photo_count }} {{ $t("people.photos_label") }}</div>
			<Tag v-if="!person.is_searchable" severity="secondary" :value="$t('people.not_searchable')" class="text-xs" />
		</div>
	</div>
</template>

<script setup lang="ts">
import Tag from "primevue/tag";

defineProps<{
	person: App.Http.Resources.Models.PersonResource;
}>();

const emit = defineEmits<{
	contextmenu: [event: MouseEvent];
}>();
</script>
