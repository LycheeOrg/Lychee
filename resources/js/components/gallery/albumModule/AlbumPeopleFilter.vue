<template>
	<div class="album-people-filter w-full flex flex-col gap-3">
		<!-- Active filter banner -->
		<div v-if="activePerson" class="flex items-center gap-2 text-sm text-muted-color justify-end">
			<i class="pi pi-filter" />
			<span>{{ $t("people.filter_active", { name: activePerson.name }) }}</span>
			<Button icon="pi pi-times" size="small" text severity="secondary" class="p-0" @click="albumStore.clearPersonFilter()" />
		</div>

		<!-- People grid -->
		<div class="flex flex-wrap gap-3 justify-start">
			<button
				v-for="person in albumStore.album_people"
				:key="person.id"
				class="flex flex-col items-center gap-1 p-2 rounded-xl transition-colors duration-150 cursor-pointer border"
				:class="
					albumStore.active_person_filter === person.id
						? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20'
						: 'border-transparent hover:bg-surface-100 dark:hover:bg-surface-800'
				"
				@click="togglePerson(person.id)"
			>
				<div class="w-14 h-14 rounded-full overflow-hidden bg-surface-300 dark:bg-surface-700 flex items-center justify-center shrink-0">
					<img
						v-if="person.representative_crop_url"
						:src="person.representative_crop_url"
						:alt="person.name"
						class="w-full h-full object-cover"
					/>
					<i v-else class="pi pi-user text-2xl text-muted-color" />
				</div>
				<span class="text-xs font-medium text-color truncate max-w-16 text-center">{{ person.name }}</span>
				<span class="text-xs text-muted-color">{{ person.photo_count }}</span>
			</button>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import Button from "primevue/button";
import { useAlbumStore } from "@/stores/AlbumState";

const albumStore = useAlbumStore();

const activePerson = computed(() =>
	albumStore.active_person_filter ? albumStore.album_people.find((p) => p.id === albumStore.active_person_filter) : null,
);

function togglePerson(personId: string) {
	if (albumStore.active_person_filter === personId) {
		albumStore.clearPersonFilter();
	} else {
		albumStore.setPersonFilter(personId);
	}
}
</script>
