<template>
	<div
		class="cursor-pointer w-40 rounded-lg bg-elevated shadow-[0_0_16px] shadow-primary-500/25 dark:shadow-primary-400/30 p-3 flex flex-col items-center gap-2 transition-[transform,box-shadow] duration-200 hover:-translate-y-1 hover:shadow-[0_0_28px] hover:shadow-primary-500/40 dark:hover:shadow-primary-400/45"
		@click="$router.push({ name: 'person', params: { personId: person.id } })"
		@contextmenu="emit('contextmenu', $event)"
	>
		<div class="mx-auto aspect-square overflow-hidden bg-accented flex items-center justify-center rounded-full w-18 h-18">
			<img v-if="person.representative_crop_url" :src="person.representative_crop_url" :alt="person.name" class="w-full h-full object-cover" />
			<UIcon v-else name="lucide:user" class="text-6xl text-muted" />
		</div>
		<div class="flex items-center gap-2 justify-center">
			<span class="text-lg truncate font-semibold text-default">{{ person.name }}</span>
		</div>
		<div class="flex gap-2 items-center justify-center">
			<div class="text-sm text-muted">{{ person.photo_count }} {{ $t("people.photos_label") }}</div>
			<UBadge v-if="!person.is_searchable" color="neutral" variant="soft" class="text-xs">{{ $t("people.not_searchable") }}</UBadge>
		</div>
	</div>
</template>

<script setup lang="ts">
defineProps<{
	person: App.Http.Resources.Models.PersonResource;
}>();

const emit = defineEmits<{
	contextmenu: [event: MouseEvent];
}>();
</script>
