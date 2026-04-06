<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<OpenLeftMenu />
			</template>
			<template #center>
				{{ $t("people.title") }}
			</template>
			<template #end>
				<Button
					:label="$t('people.clusters_title')"
					class="border-none"
					icon="pi pi-sitemap"
					severity="secondary"
					outlined
					@click="$router.push('/people/clusters')"
				/>
			</template>
		</Toolbar>

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<ProgressSpinner />
		</div>

		<div v-else-if="people.length === 0" class="text-muted-color text-center mt-20 p-4">
			{{ $t("people.no_people") }}
		</div>

		<div v-else class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
			<PersonCard v-for="person in people" :key="person.id" :person="person" />
		</div>

		<div v-if="hasMorePages" class="flex justify-center pb-8 mt-4">
			<Button :label="$t('gallery.load_more') || 'Load more'" severity="secondary" outlined @click="loadMore" :loading="loadingMore" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import PersonCard from "@/components/gallery/PersonCard.vue";
import PeopleService from "@/services/people-service";

const toast = useToast();

const people = ref<App.Http.Resources.Models.PersonResource[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const currentPage = ref(1);
const hasMorePages = ref(false);

function load() {
	loading.value = true;
	PeopleService.getPeople(1)
		.then((response) => {
			people.value = response.data.persons;
			currentPage.value = 1;
			hasMorePages.value = response.data.current_page < response.data.last_page;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

function loadMore() {
	loadingMore.value = true;
	const nextPage = currentPage.value + 1;
	PeopleService.getPeople(nextPage)
		.then((response) => {
			people.value = [...people.value, ...response.data.persons];
			currentPage.value = nextPage;
			hasMorePages.value = response.data.current_page < response.data.last_page;
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			loadingMore.value = false;
		});
}

onMounted(() => {
	load();
});
</script>
