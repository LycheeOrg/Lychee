<template>
	<div class="h-svh overflow-y-auto">
		<div class="w-full border-0 h-14 flex items-center justify-between px-2">
			<OpenLeftMenu />
			<span class="absolute left-1/2 -translate-x-1/2 pointer-events-none">{{ $t("people.title") }}</span>
			<div class="flex items-center gap-2">
				<UButton
					v-if="userStore.isAdmin"
					:label="$t('maintenance.face_quality.title')"
					color="neutral"
					variant="outline"
					icon="prime:filter"
					@click="$router.push({ name: 'face-maintenance' })"
				/>
				<UButton
					:label="$t('people.clusters_title')"
					color="neutral"
					variant="outline"
					icon="prime:sitemap"
					@click="$router.push('/people/clusters')"
				/>
			</div>
		</div>

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<Spinner class="text-4xl" />
		</div>

		<div v-else-if="people.length === 0" class="text-muted text-center mt-20 p-4">
			{{ $t("people.no_people") }}
		</div>

		<UContextMenu v-else :items="contextMenuSections" :disabled="contextMenuItems.length === 0" class="contents">
			<div class="p-6 flex flex-wrap gap-4">
				<PersonCard v-for="person in people" :key="person.id" :person="person" @contextmenu="openContextMenu($event, person)" />
			</div>
		</UContextMenu>

		<div v-if="hasMorePages" class="flex justify-center pb-8 mt-4">
			<UButton :label="$t('gallery.load_more') || 'Load more'" color="neutral" variant="outline" @click="loadMore" :loading="loadingMore" />
		</div>

		<PersonDeleteDialog v-if="contextMenuPerson" v-model:open="deletePersonVisible" :person="contextMenuPerson" @deleted="onPersonDeleted" />

		<!-- Assign to user dialog (admin only) -->
		<UModal v-model:open="userPickerVisible" :dismissible="true">
			<template #header>
				<span class="font-bold">{{ $t("people.assign_to_user") }}</span>
			</template>
			<template #body>
				<div class="flex flex-col gap-4">
					<UInputMenu
						v-model="selectedUserMenuValue"
						:items="userMenuItems"
						label-key="username"
						:placeholder="$t('people.search_user')"
						class="w-full"
					/>
				</div>
			</template>
			<template #footer>
				<div class="flex gap-2 justify-end w-full">
					<UButton :label="$t('gallery.cancel')" color="neutral" variant="soft" @click="userPickerVisible = false" />
					<UButton :label="$t('gallery.done')" color="neutral" :disabled="!selectedUser" @click="confirmAssignUser" />
				</div>
			</template>
		</UModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import PersonCard from "@/v8/components/gallery/PersonCard.vue";
import PersonDeleteDialog from "@/v8/components/forms/people/PersonDeleteDialog.vue";
import Spinner from "@/v8/components/Spinner.vue";
import PeopleService from "@/services/people-service";
import UserManagementService from "@/services/user-management-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { useUserStore } from "@/stores/UserState";
import { storeToRefs } from "pinia";
import type { ContextMenuItem, InputMenuItem } from "@nuxt/ui";

const toast = useAppToast();
const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);
const userStore = useUserStore();

const people = ref<App.Http.Resources.Models.PersonResource[]>([]);
const loading = ref(false);
const loadingMore = ref(false);
const currentPage = ref(1);
const hasMorePages = ref(false);

// Context menu
const contextMenuPerson = ref<App.Http.Resources.Models.PersonResource | null>(null);

type ContextMenuAction = {
	label: string;
	icon: string;
	command: () => void;
};

const contextMenuItems = ref<ContextMenuAction[]>([]);

// Delete dialog
const deletePersonVisible = ref(false);

// User picker
const userPickerVisible = ref(false);
const allUsers = ref<App.Http.Resources.Models.UserManagementResource[]>([]);
const selectedUser = ref<App.Http.Resources.Models.UserManagementResource | null>(null);

// UInputMenu's item type reserves `description` as `string | undefined`; UserManagementResource's
// own `description` field is `string | null`, so bind through an opaque cast.
const userMenuItems = computed(() => allUsers.value as unknown as InputMenuItem[]);
const selectedUserMenuValue = computed<InputMenuItem | undefined>({
	get: () => (selectedUser.value as unknown as InputMenuItem | undefined) ?? undefined,
	set: (v) => {
		selectedUser.value = (v as unknown as App.Http.Resources.Models.UserManagementResource | undefined) ?? null;
	},
});

function buildContextMenuItems(person: App.Http.Resources.Models.PersonResource): ContextMenuAction[] {
	const items: ContextMenuAction[] = [
		{
			label: trans("people.person.toggle_searchable"),
			icon: person.is_searchable ? "prime:eye-slash" : "prime:eye",
			command: () => toggleSearchable(person),
		},
	];

	if (initData.value?.user_management.can_edit) {
		items.push({
			label: trans("people.assign_to_user"),
			icon: "prime:user-edit",
			command: () => openUserPicker(person),
		});
	}

	items.push({
		label: trans("people.person.delete"),
		icon: "prime:trash",
		command: () => openDeleteDialog(person),
	});

	return items;
}

const contextMenuSections = computed<ContextMenuItem[][]>(() => [
	contextMenuItems.value.map((item) => ({
		label: item.label,
		icon: item.icon,
		onSelect: item.command,
	})),
]);

function openContextMenu(_event: MouseEvent, person: App.Http.Resources.Models.PersonResource) {
	contextMenuPerson.value = person;
	contextMenuItems.value = buildContextMenuItems(person);
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

function openDeleteDialog(person: App.Http.Resources.Models.PersonResource) {
	contextMenuPerson.value = person;
	deletePersonVisible.value = true;
}

function onPersonDeleted() {
	if (contextMenuPerson.value) {
		people.value = people.value.filter((p) => p.id !== contextMenuPerson.value!.id);
	}
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
			people.value = response.data.data;
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
			people.value = [...people.value, ...response.data.data];
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
