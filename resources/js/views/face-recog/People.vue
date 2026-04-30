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

		<div v-else class="p-6 flex flex-wrap gap-4">
			<PersonCard v-for="person in people" :key="person.id" :person="person" @contextmenu="openContextMenu($event, person)" />
		</div>

		<div v-if="hasMorePages" class="flex justify-center pb-8 mt-4">
			<Button :label="$t('gallery.load_more') || 'Load more'" severity="secondary" outlined @click="loadMore" :loading="loadingMore" />
		</div>

		<ContextMenu ref="contextMenuRef" :model="contextMenuItems" />

		<!-- Assign to user dialog (admin only) -->
		<Dialog v-model:visible="userPickerVisible" modal :header="$t('people.assign_to_user')" class="w-80">
			<div class="flex flex-col gap-4">
				<AutoComplete
					v-model="selectedUser"
					:suggestions="userSuggestions"
					optionLabel="username"
					:placeholder="$t('people.search_user')"
					class="w-full"
					dropdown
					forceSelection
					@complete="searchUsers"
				/>
				<div class="flex gap-2 justify-end">
					<Button :label="$t('gallery.cancel')" severity="secondary" text @click="userPickerVisible = false" />
					<Button :label="$t('gallery.done')" severity="primary" :disabled="!selectedUser" @click="confirmAssignUser" />
				</div>
			</div>
		</Dialog>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import ContextMenu from "primevue/contextmenu";
import AutoComplete from "primevue/autocomplete";
import Dialog from "primevue/dialog";
import type { MenuItem } from "primevue/menuitem";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import PersonCard from "@/components/gallery/PersonCard.vue";
import PeopleService from "@/services/people-service";
import UserManagementService from "@/services/user-management-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";

const toast = useToast();
const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);

const people = ref<App.Http.Resources.Models.PersonResource[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const currentPage = ref(1);
const hasMorePages = ref(false);

// Context menu
const contextMenuRef = ref<InstanceType<typeof ContextMenu> | null>(null);
const contextMenuPerson = ref<App.Http.Resources.Models.PersonResource | null>(null);
const contextMenuItems = ref<MenuItem[]>([]);

// User picker
const userPickerVisible = ref(false);
const allUsers = ref<App.Http.Resources.Models.UserManagementResource[]>([]);
const userSuggestions = ref<App.Http.Resources.Models.UserManagementResource[]>([]);
const selectedUser = ref<App.Http.Resources.Models.UserManagementResource | null>(null);

function buildContextMenuItems(person: App.Http.Resources.Models.PersonResource): MenuItem[] {
	const items: MenuItem[] = [
		{
			label: trans("people.person.toggle_searchable"),
			icon: person.is_searchable ? "pi pi-eye-slash" : "pi pi-eye",
			command: () => toggleSearchable(person),
		},
	];

	if (initData.value?.user_management.can_edit) {
		items.push({
			label: trans("people.assign_to_user"),
			icon: "pi pi-user-edit",
			command: () => openUserPicker(person),
		});
	}

	return items;
}

function openContextMenu(event: MouseEvent, person: App.Http.Resources.Models.PersonResource) {
	contextMenuPerson.value = person;
	contextMenuItems.value = buildContextMenuItems(person);
	contextMenuRef.value?.show(event);
}

function toggleSearchable(person: App.Http.Resources.Models.PersonResource) {
	PeopleService.update(person.id, { is_searchable: !person.is_searchable })
		.then((response) => {
			const idx = people.value.findIndex((p) => p.id === person.id);
			if (idx !== -1) people.value[idx] = response.data;
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function openUserPicker(person: App.Http.Resources.Models.PersonResource) {
	contextMenuPerson.value = person;
	selectedUser.value = null;
	if (allUsers.value.length === 0) {
		UserManagementService.get()
			.then((response) => {
				allUsers.value = response.data;
			})
			.catch(() => {
				/* ignore */
			});
	}
	userPickerVisible.value = true;
}

function searchUsers(event: { query: string }) {
	const q = event.query.toLowerCase();
	userSuggestions.value = allUsers.value.filter((u) => u.username.toLowerCase().includes(q));
}

function confirmAssignUser() {
	if (!contextMenuPerson.value || !selectedUser.value) return;
	const personId = contextMenuPerson.value.id;
	const userId = selectedUser.value.id;
	userPickerVisible.value = false;
	PeopleService.update(personId, { user_id: userId })
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

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
