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
import { useToast } from "primevue/usetoast";
import AutoComplete, { AutoCompleteCompleteEvent } from "primevue/autocomplete";
import { usePeopleList } from "@/composables/usePeopleList";

const toast = useToast();
const props = defineProps<{
	placeholder?: string;
}>();

const modelValue = defineModel<Nullable<App.Http.Resources.Models.PersonResource[]>>();

const { people, load } = usePeopleList();
const filteredPersons = ref<App.Http.Resources.Models.PersonResource[]>([]);

function search(event: AutoCompleteCompleteEvent) {
	setTimeout(() => {
		if (!event.query.trim().length) {
			filteredPersons.value = people.value;
		} else {
			filteredPersons.value = people.value.filter((p) => p.name.toLowerCase().startsWith(event.query.toLowerCase()));
		}
	}, 250);
}

onMounted(() => {
	load().catch(() => {
		toast.add({
			severity: "error",
			summary: "Error",
			detail: "Failed to fetch persons.",
			life: 3000,
		});
	});
});
</script>
