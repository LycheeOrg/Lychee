<template>
	<div class="h-svh overflow-y-auto">
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<div class="flex items-center gap-2">
					<Button icon="pi pi-chevron-left" severity="secondary" text @click="$router.push({ name: 'people' })" />
				</div>
			</template>
			<template #center>
				<span v-if="person">{{ person.name }}</span>
			</template>
			<template #end>
				<div v-if="person && canEdit" class="flex gap-2">
					<Button
						v-if="!isBatchMode"
						icon="pi pi-check-square"
						severity="secondary"
						class="border-none"
						text
						v-tooltip.bottom="$t('people.batch_select')"
						@click="startBatchMode"
					/>
					<template v-else>
						<Button :label="$t('people.batch_cancel')" severity="secondary" text @click="cancelBatchMode" />
						<Button
							:label="$t('people.batch_unassign')"
							icon="pi pi-minus-circle"
							class="border-none"
							severity="danger"
							text
							:disabled="selectedFaceIds.length === 0"
							:loading="batchLoading"
							@click="batchUnassign"
						/>
					</template>
					<Button
						icon="pi pi-arrow-down-left-and-arrow-up-right-to-center"
						severity="secondary"
						class="border-none"
						text
						v-tooltip.bottom="$t('people.merge.title')"
						@click="isMergeModalOpen = true"
					/>
					<Button icon="pi pi-pencil" class="border-none" severity="secondary" text v-tooltip.bottom="$t('people.person.edit')" @click="openEdit" />
					<Button
						:icon="person.is_searchable ? 'pi pi-eye' : 'pi pi-eye-slash'"
						severity="secondary"
						text
						v-tooltip.bottom="$t('people.person.toggle_searchable')"
						@click="toggleSearchable"
					/>
					<Button icon="pi pi-trash" class="border-none" severity="danger" text v-tooltip.bottom="$t('people.person.delete')" @click="confirmDelete" />
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

			<!-- Batch mode info bar -->
			<div v-if="isBatchMode" class="px-6 py-2 bg-surface-100 dark:bg-surface-800 flex items-center gap-3 text-sm">
				<Checkbox
					:modelValue="allSelected"
					:indeterminate="partiallySelected"
					binary
					@change="toggleSelectAll"
				/>
				<span>{{ $t("people.batch_selected", { count: String(selectedFaceIds.length) }) }}</span>
			</div>

			<!-- Photos grid -->
			<div v-if="photos.length === 0 && !photosLoading" class="text-muted-color text-center mt-10 p-4">
				{{ $t("search.no_results") }}
			</div>
			<div v-else class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2">
				<div
					v-for="photo in photos"
					:key="photo.id"
					class="relative aspect-square overflow-hidden rounded-lg bg-surface-800 group"
					:class="{ 'ring-2 ring-primary': isBatchMode && isPhotoSelected(photo), 'cursor-pointer': isBatchMode }"
					@click="isBatchMode ? togglePhotoSelection(photo) : undefined"
				>
					<img
						v-if="photo.size_variants.thumb"
						:src="photo.size_variants.thumb.url ?? ''"
						:alt="photo.title"
						class="w-full h-full object-cover"
					/>
					<div v-else class="w-full h-full flex items-center justify-center">
						<i class="pi pi-image text-3xl text-muted-color" />
					</div>
					<!-- Batch mode checkbox overlay -->
					<div v-if="isBatchMode" class="absolute top-1 left-1">
						<Checkbox :modelValue="isPhotoSelected(photo)" binary @click.stop="togglePhotoSelection(photo)" />
					</div>
					<!-- Remove from person button (shown on hover when not in batch mode) -->
					<div
						v-else-if="canEdit"
						class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-2"
					>
						<Button
							:label="$t('people.remove_from_person')"
							icon="pi pi-user-minus"
							class="border-none"
							severity="danger"
							size="small"
							@click.stop="removeFromPerson(photo)"
						/>
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
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import ConfirmDialog from "primevue/confirmdialog";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { trans } from "laravel-vue-i18n";
import MergePersonModal from "@/components/modals/faceRecog/MergePersonModal.vue";
import PeopleService from "@/services/people-service";
import FaceBatchService from "@/services/face-batch-service";
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

// Batch selection state
const isBatchMode = ref(false);
const selectedFaceIds = ref<string[]>([]);
const batchLoading = ref(false);

// Compute the face ID for a photo (the face belonging to the current person)
function getPersonFaceId(photo: App.Http.Resources.Models.PhotoResource): string | null {
	const face = photo.faces.find((f) => f.person_id === person.value?.id);
	return face?.id ?? null;
}

// All face IDs across all current photos
const allFaceIds = computed(() => {
	return photos.value.map((p) => getPersonFaceId(p)).filter((id): id is string => id !== null);
});

const allSelected = computed(() => allFaceIds.value.length > 0 && selectedFaceIds.value.length === allFaceIds.value.length);
const partiallySelected = computed(() => selectedFaceIds.value.length > 0 && selectedFaceIds.value.length < allFaceIds.value.length);

function isPhotoSelected(photo: App.Http.Resources.Models.PhotoResource): boolean {
	const faceId = getPersonFaceId(photo);
	return faceId !== null && selectedFaceIds.value.includes(faceId);
}

function togglePhotoSelection(photo: App.Http.Resources.Models.PhotoResource) {
	const faceId = getPersonFaceId(photo);
	if (!faceId) return;
	const idx = selectedFaceIds.value.indexOf(faceId);
	if (idx === -1) {
		selectedFaceIds.value.push(faceId);
	} else {
		selectedFaceIds.value.splice(idx, 1);
	}
}

function toggleSelectAll() {
	if (selectedFaceIds.value.length === allFaceIds.value.length) {
		selectedFaceIds.value = [];
	} else {
		selectedFaceIds.value = [...allFaceIds.value];
	}
}

function startBatchMode() {
	isBatchMode.value = true;
	selectedFaceIds.value = [];
}

function cancelBatchMode() {
	isBatchMode.value = false;
	selectedFaceIds.value = [];
}

function batchUnassign() {
	if (selectedFaceIds.value.length === 0) return;
	batchLoading.value = true;
	FaceBatchService.batchUnassign(selectedFaceIds.value)
		.then((data) => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.remove_from_person_success"), life: 3000 });
			// Remove affected photos from list
			const unassigned = new Set(selectedFaceIds.value);
			photos.value = photos.value.filter((p) => {
				const faceId = getPersonFaceId(p);
				return faceId === null || !unassigned.has(faceId);
			});
			if (person.value) {
				person.value.face_count = Math.max(0, person.value.face_count - data.affected_count);
				person.value.photo_count = Math.max(0, person.value.photo_count - data.affected_count);
			}
			cancelBatchMode();
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			batchLoading.value = false;
		});
}

function removeFromPerson(photo: App.Http.Resources.Models.PhotoResource) {
	const faceId = getPersonFaceId(photo);
	if (!faceId) return;
	FaceBatchService.batchUnassign([faceId])
		.then((data) => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.remove_from_person_success"), life: 3000 });
			photos.value = photos.value.filter((p) => p.id !== photo.id);
			if (person.value) {
				person.value.face_count = Math.max(0, person.value.face_count - data.affected_count);
				person.value.photo_count = Math.max(0, person.value.photo_count - data.affected_count);
			}
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		});
}

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
