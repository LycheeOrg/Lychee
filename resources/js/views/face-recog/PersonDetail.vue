<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<div class="flex items-center gap-2">
					<OpenLeftMenu />
					<Button icon="pi pi-arrow-left" severity="secondary" text @click="$router.push({ name: 'people' })" />
				</div>
			</template>
			<template #center>
				<span v-if="person">{{ person.name }}</span>
			</template>
			<template #end>
				<div v-if="person && canEdit" class="flex gap-2">
					<Button
						icon="pi pi-code-branch"
						severity="secondary"
						text
						v-tooltip.bottom="$t('people.merge.title')"
						@click="isMergeModalOpen = true"
					/>
					<Button icon="pi pi-pencil" severity="secondary" text v-tooltip.bottom="$t('people.person.edit')" @click="openEdit" />
					<Button
						:icon="person.is_searchable ? 'pi pi-eye' : 'pi pi-eye-slash'"
						severity="secondary"
						text
						v-tooltip.bottom="$t('people.person.toggle_searchable')"
						@click="toggleSearchable"
					/>
					<Button icon="pi pi-trash" severity="danger" text v-tooltip.bottom="$t('people.person.delete')" @click="confirmDelete" />
				</div>
			</template>
		</Toolbar>

		<div v-if="loading" class="flex justify-center items-center mt-20">
			<ProgressSpinner />
		</div>

		<template v-else-if="person">
			<!-- Person header -->
			<div class="flex flex-col sm:flex-row items-center gap-6 p-6 border-b border-surface-700">
				<div class="w-24 h-24 rounded-full overflow-hidden bg-surface-800 flex items-center justify-center shrink-0">
					<img
						v-if="person.representative_crop_url"
						:src="person.representative_crop_url"
						:alt="person.name"
						class="w-full h-full object-cover"
					/>
					<i v-else class="pi pi-user text-4xl text-muted-color" />
				</div>
				<div class="flex flex-col gap-2 text-center sm:text-left">
					<h1 class="text-2xl font-bold">{{ person.name }}</h1>
					<div class="text-muted-color text-sm">
						{{ person.photo_count }} {{ $t("people.photos_label") }} &bull; {{ person.face_count }} {{ $t("people.faces_label") }}
					</div>
					<Tag v-if="!person.is_searchable" severity="secondary" :value="$t('people.not_searchable')" class="w-fit mx-auto sm:mx-0" />
				</div>
			</div>

			<!-- Edit form -->
			<div v-if="isEditing" class="p-6 border-b border-surface-700 flex gap-4 items-end">
				<div class="flex-1">
					<label class="block text-sm text-muted-color mb-1">{{ $t("users.name") }}</label>
					<InputText v-model="editName" class="w-full" @keydown.enter="saveEdit" @keydown.escape="isEditing = false" />
				</div>
				<Button :label="$t('gallery.done')" severity="primary" @click="saveEdit" />
				<Button :label="$t('gallery.cancel')" severity="secondary" text @click="isEditing = false" />
			</div>

			<!-- Photos grid -->
			<div v-if="photos.length === 0 && !photosLoading" class="text-muted-color text-center mt-10 p-4">
				{{ $t("search.no_results") }}
			</div>
			<div v-else class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2">
				<div v-for="photo in photos" :key="photo.id" class="aspect-square overflow-hidden rounded-lg bg-surface-800">
					<img
						v-if="photo.size_variants.thumb"
						:src="photo.size_variants.thumb.url ?? ''"
						:alt="photo.title"
						class="w-full h-full object-cover"
					/>
					<div v-else class="w-full h-full flex items-center justify-center">
						<i class="pi pi-image text-3xl text-muted-color" />
					</div>
				</div>
			</div>
			<div v-if="photosLoading" class="flex justify-center py-4">
				<ProgressSpinner class="w-8 h-8" />
			</div>
			<div v-if="hasMorePhotos" class="flex justify-center pb-8 mt-4">
				<Button
					:label="$t('gallery.load_more') || 'Load more'"
					severity="secondary"
					outlined
					@click="loadMorePhotos"
					:loading="photosLoadingMore"
				/>
			</div>
		</template>

		<ConfirmDialog />
		<MergePersonModal v-if="person && isMergeModalOpen" v-model:visible="isMergeModalOpen" :source-person="person" @merged="onMerged" />
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import ConfirmDialog from "primevue/confirmdialog";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import MergePersonModal from "@/components/modals/MergePersonModal.vue";
import PeopleService from "@/services/people-service";
import { useUserStore } from "@/stores/UserState";
import { storeToRefs } from "pinia";

const props = defineProps<{ personId: string }>();

const router = useRouter();
const toast = useToast();
const confirm = useConfirm();
const userStore = useUserStore();
const { user } = storeToRefs(userStore);
const canEdit = ref(false);

const person = ref<App.Http.Resources.Models.PersonResource | undefined>(undefined);
const loading = ref(false);
const isEditing = ref(false);
const editName = ref("");

const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
const photosLoading = ref(false);
const photosLoadingMore = ref(false);
const photosPage = ref(1);
const hasMorePhotos = ref(false);

function load() {
	loading.value = true;
	PeopleService.getPerson(props.personId)
		.then((response) => {
			person.value = response.data;
			editName.value = response.data.name;
			canEdit.value = user.value?.id !== undefined && user.value?.id !== null;
			loadPhotos();
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

function loadPhotos() {
	photosLoading.value = true;
	PeopleService.getPhotos(props.personId, 1)
		.then((response) => {
			photos.value = response.data.photos;
			photosPage.value = 1;
			hasMorePhotos.value = response.data.current_page < response.data.last_page;
		})
		.finally(() => {
			photosLoading.value = false;
		});
}

function loadMorePhotos() {
	photosLoadingMore.value = true;
	const nextPage = photosPage.value + 1;
	PeopleService.getPhotos(props.personId, nextPage)
		.then((response) => {
			photos.value = [...photos.value, ...response.data.photos];
			photosPage.value = nextPage;
			hasMorePhotos.value = response.data.current_page < response.data.last_page;
		})
		.finally(() => {
			photosLoadingMore.value = false;
		});
}

function openEdit() {
	editName.value = person.value?.name ?? "";
	isEditing.value = true;
}

function saveEdit() {
	if (!editName.value.trim() || !person.value) {
		return;
	}
	PeopleService.update(person.value.id, { name: editName.value.trim() })
		.then((response) => {
			person.value = response.data;
			isEditing.value = false;
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function toggleSearchable() {
	if (!person.value) {
		return;
	}
	PeopleService.update(person.value.id, { is_searchable: !person.value.is_searchable })
		.then((response) => {
			person.value = response.data;
			toast.add({ severity: "success", summary: trans("toasts.success"), life: 3000 });
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

function confirmDelete() {
	confirm.require({
		message: trans("dialogs.delete_confirm"),
		header: trans("dialogs.delete"),
		icon: "pi pi-trash",
		acceptClass: "p-button-danger",
		accept() {
			PeopleService.destroy(props.personId)
				.then(() => {
					router.push({ name: "people" });
				})
				.catch((e) => {
					toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
				});
		},
	});
}

onMounted(() => {
	load();
});

// Merge modal
const isMergeModalOpen = ref(false);

function onMerged(targetPersonId: string) {
	router.push({ name: "person", params: { personId: targetPersonId } });
}
</script>
