<template>
	<Card
		class="cursor-pointer hover:shadow-lg transition-shadow duration-200"
		:class="ctrlHeld && !isTouchDev ? 'border-2 border-dashed border-red-500' : ''"
		@click="$router.push({ name: 'person', params: { personId: person.id } })"
	>
		<template #header>
			<div class="aspect-square overflow-hidden rounded-t-lg bg-surface-800 flex items-center justify-center">
				<img
					v-if="person.representative_crop_url"
					:src="person.representative_crop_url"
					:alt="person.name"
					class="w-full h-full object-cover"
				/>
				<i v-else class="pi pi-user text-6xl text-muted-color" />
			</div>
		</template>
		<template #title>
			<div class="flex items-center gap-2">
				<span class="truncate font-semibold">{{ person.name }}</span>
				<Tag v-if="!person.is_searchable" severity="secondary" :value="$t('people.not_searchable')" class="text-xs" />
			</div>
		</template>
		<template #content>
			<div class="text-sm text-muted-color">{{ person.photo_count }} {{ $t("people.photos_label") }}</div>
		</template>
	</Card>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import Card from "primevue/card";
import Tag from "primevue/tag";
import { isTouchDevice } from "@/utils/keybindings-utils";

defineProps<{
	person: App.Http.Resources.Models.PersonResource;
}>();

const isTouchDev = isTouchDevice();
const ctrlHeld = ref(false);

function onKeyDown(e: KeyboardEvent) {
	if (e.key === "Control" || e.key === "Meta") {
		ctrlHeld.value = true;
	}
}

function onKeyUp(e: KeyboardEvent) {
	if (e.key === "Control" || e.key === "Meta") {
		ctrlHeld.value = false;
	}
}

onMounted(() => {
	if (!isTouchDev) {
		window.addEventListener("keydown", onKeyDown);
		window.addEventListener("keyup", onKeyUp);
	}
});

onUnmounted(() => {
	if (!isTouchDev) {
		window.removeEventListener("keydown", onKeyDown);
		window.removeEventListener("keyup", onKeyUp);
	}
});
</script>
