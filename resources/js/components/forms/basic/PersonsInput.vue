<template>
	<AutoComplete
		id="persons"
		input-id="persons"
		v-model="modelValue"
		:suggestions="filteredPersons"
		:force-selection="true"
		option-label="name"
		multiple
		class="pt-3 border-b hover:border-b-0 w-full"
		pt:inputmultiple:class="w-full border-t-0 border-l-0 border-r-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
		:placeholder="modelValue?.length === 0 ? (props.placeholder ?? '') : ''"
		@complete="search"
	>
		<template #option="slotProps">
			<div class="flex items-center">
				{{ slotProps.option.name }}
			</div>
		</template>
	</AutoComplete>
</template>
<script setup lang="ts">
import { onMounted, ref } from "vue";
import type { Nullable } from "@primevue/core";
import PeopleService from "@/services/people-service";
import { useToast } from "primevue/usetoast";
import AutoComplete, { AutoCompleteCompleteEvent } from "primevue/autocomplete";

const toast = useToast();
const props = defineProps<{
	placeholder?: string;
}>();

const modelValue = defineModel<Nullable<App.Http.Resources.Models.PersonResource[]>>();

const persons = ref<App.Http.Resources.Models.PersonResource[]>([]);
const filteredPersons = ref<App.Http.Resources.Models.PersonResource[]>([]);

function fetchPage(page: number, all: App.Http.Resources.Models.PersonResource[]): void {
	PeopleService.getPeople(page)
		.then((response) => {
			all.push(...response.data.data);
			const lastPage = response.data.last_page;
			if (page < lastPage) {
				fetchPage(page + 1, all);
			} else {
				persons.value = all;
			}
		})
		.catch(() => {
			toast.add({
				severity: "error",
				summary: "Error",
				detail: "Failed to fetch persons.",
				life: 3000,
			});
		});
}

function fetchPersons(): void {
	fetchPage(1, []);
}

function search(event: AutoCompleteCompleteEvent) {
	setTimeout(() => {
		if (!event.query.trim().length) {
			filteredPersons.value = persons.value;
		} else {
			filteredPersons.value = persons.value.filter((p) => p.name.toLowerCase().startsWith(event.query.toLowerCase()));
		}
	}, 250);
}

onMounted(() => {
	fetchPersons();
});
</script>
