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
			<template #end> </template>
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
					<h1 v-if="!isEditing" class="text-2xl font-bold text-surface-900 dark:text-white cursor-text" @click="canEdit && openEdit()">
						{{ person.name }}
						<i v-if="canEdit" class="pi pi-pencil text-sm text-muted-color ml-2 opacity-50" />
					</h1>
					<InputText
						v-else
						v-model="editName"
						class="text-2xl font-bold"
						@keydown.enter="saveEdit"
						@keydown.escape="isEditing = false"
						@blur="saveEditIfChanged"
						autofocus
					/>
					<div class="text-muted-color text-sm">
						{{ person.photo_count }} {{ $t("people.photos_label") }} &bull; {{ person.face_count }} {{ $t("people.faces_label") }}
					</div>
					<Tag v-if="!person.is_searchable" severity="secondary" :value="$t('people.not_searchable')" class="w-fit mx-auto sm:mx-0" />
					<div v-if="canEdit" class="flex gap-2 flex-wrap justify-center sm:justify-start mt-2">
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

						<Button
							:icon="person.is_searchable ? 'pi pi-eye' : 'pi pi-eye-slash'"
							severity="secondary"
							text
							v-tooltip.bottom="$t('people.person.toggle_searchable')"
							@click="toggleSearchable"
						/>
						<Button
							icon="pi pi-trash"
							class="border-none"
							severity="danger"
							text
							v-tooltip.bottom="$t('people.person.delete')"
							@click="confirmDelete"
						/>
					</div>
				</div>
			</div>

			<!-- Batch mode info bar -->
			<div v-if="isBatchMode" class="px-6 py-2 bg-surface-100 dark:bg-surface-800 flex items-center gap-3 text-sm">
				<Checkbox :modelValue="allSelected" :indeterminate="partiallySelected" binary @change="toggleSelectAll" />
				<span>{{ $t("people.batch_selected", { count: String(selectedFaceIds.length) }) }}</span>
			</div>

			<!-- Photos grid -->
			<div v-if="photos.length === 0 && !photosLoading" class="text-muted-color text-center mt-10 p-4">
				{{ $t("search.no_results") }}
			</div>
			<div
				v-else
				ref="photoListingRef"
				class="relative mx-4 my-4"
				:style="{ height: photoListingHeight + 'px' }"
				@mousedown="isBatchMode ? startDragSelect($event) : undefined"
			>
				<!-- Rubber-band selection rectangle -->
				<div
					v-if="dragVisible"
					class="absolute pointer-events-none border border-primary-400 bg-primary-400/20 z-10"
					:style="dragRectStyle"
				/>
				<div
					v-for="(photo, idx) in photos"
					:key="photo.id"
					class="absolute overflow-hidden rounded-lg bg-surface-800 group cursor-pointer"
					:class="{ 'outline outline-2 outline-primary-500': isBatchMode && isPhotoSelected(photo) }"
					:data-width="photo.size_variants.original?.width ?? photo.size_variants.small?.width ?? 1"
					:data-height="photo.size_variants.original?.height ?? photo.size_variants.small?.height ?? 1"
					:data-face-id="getPersonFaceId(photo)"
					@click="isBatchMode ? togglePhotoSelection(photo, idx, $event) : openPhoto(photo.id)"
				>
					<img
						v-if="photo.size_variants.small?.url ?? photo.size_variants.thumb?.url"
						:src="photo.size_variants.small?.url ?? photo.size_variants.thumb?.url ?? ''"
						:alt="photo.title"
						class="w-full h-full object-cover"
						loading="lazy"
					/>
					<div v-else class="w-full h-full flex items-center justify-center">
						<i class="pi pi-image text-3xl text-muted-color" />
					</div>
					<!-- Remove from person compact × badge (shown on hover when not in batch mode) -->
					<button
						v-if="canEdit && !isBatchMode"
						class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
						@click.stop="removeFromPerson(photo)"
						:title="$t('people.remove_from_person')"
					>
						×
					</button>
				</div>
			</div>
			<PaginationInfiniteScroll :loading="photosLoadingMore" :has-more="hasMorePhotos" @load-more="loadMorePhotos" />
		</template>

		<ConfirmDialog />
		<MergePersonModal v-if="person && isMergeModalOpen" v-model:visible="isMergeModalOpen" :source-person="person" @merged="onMerged" />

		<!-- Photo lightbox overlay -->
		<PhotoPanel
			v-if="photoStore.isLoaded"
			:is-map-visible="false"
			@go-back="closePhoto"
			@next="next"
			@previous="previous"
			@toggle-slide-show="slideshow"
			@rotate-overlay="rotateOverlay"
			@rotate-photo-c-w="() => {}"
			@rotate-photo-c-c-w="() => {}"
			@set-album-header="() => {}"
			@toggle-highlight="() => {}"
			@toggle-move="() => {}"
			@toggle-delete="() => {}"
			@updated="() => {}"
		/>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUpdated, onUnmounted, watch } from "vue";
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
import { useDebounceFn, onKeyStroke } from "@vueuse/core";
import createJustifiedLayout from "justified-layout";
import MergePersonModal from "@/components/modals/faceRecog/MergePersonModal.vue";
import PaginationInfiniteScroll from "@/components/pagination/PaginationInfiniteScroll.vue";
import PhotoPanel from "@/components/gallery/photoModule/PhotoPanel.vue";
import PeopleService from "@/services/people-service";
import FaceBatchService from "@/services/face-batch-service";
import { useUserStore } from "@/stores/UserState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { getNextPreviousPhoto } from "@/composables/photo/getNextPreviousPhoto";
import { storeToRefs } from "pinia";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import { useLtRorRtL } from "@/utils/Helpers";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useSlideshowFunction } from "@/composables/photo/slideshow";

const props = defineProps<{ personId: string; photoId?: string }>();

const router = useRouter();
const toast = useToast();
const confirm = useConfirm();
const userStore = useUserStore();
const { user } = storeToRefs(userStore);
const canEdit = ref(false);
const togglableStore = useTogglablesStateStore();
const lycheeStore = useLycheeStateStore();
const { isLTR } = useLtRorRtL();
const { are_details_open, is_slideshow_active } = storeToRefs(togglableStore);
const { slideshow_timeout, is_slideshow_enabled } = storeToRefs(lycheeStore);

const videoElement = ref<HTMLVideoElement | null>(null);

// Photo lightbox stores
const photoStore = usePhotoStore();
const photosStore = usePhotosStore();
const { getNext, getPrevious } = getNextPreviousPhoto(router, photoStore);
const {
	slideshow,
	next: nextSlideshow,
	previous: previousSlideshow,
	stop,
} = useSlideshowFunction(1000, is_slideshow_active, slideshow_timeout, videoElement, getNext, getPrevious);

const person = ref<App.Http.Resources.Models.PersonResource | undefined>(undefined);
const loading = ref(false);
const isEditing = ref(false);
const editName = ref("");

const photos = ref<App.Http.Resources.Models.PhotoResource[]>([]);
const photosLoading = ref(false);
const photosLoadingMore = ref(false);
const photosPage = ref(1);
const hasMorePhotos = ref(false);

// Justified layout state
const photoListingRef = ref<HTMLElement | null>(null);
const photoListingHeight = ref(0);

function runJustifiedLayout() {
	const el = photoListingRef.value;
	if (!el) return;
	const containerWidth = el.clientWidth;
	if (containerWidth <= 0) return;
	const items = [...el.childNodes].filter((n) => n.nodeType === 1) as HTMLElement[];
	const ratios = items.map((item) => {
		const w = parseFloat(item.dataset.width ?? "1");
		const h = parseFloat(item.dataset.height ?? "1");
		return h > 0 ? w / h : 1;
	});
	if (ratios.length === 0) {
		photoListingHeight.value = 0;
		return;
	}
	const geometry = createJustifiedLayout(ratios, { containerWidth, containerPadding: 0, targetRowHeight: 220, boxSpacing: 4 });
	photoListingHeight.value = geometry.containerHeight;
	items.forEach((item, i) => {
		const box = geometry.boxes[i];
		if (!box) return;
		item.style.position = "absolute";
		item.style.top = box.top + "px";
		item.style.left = box.left + "px";
		item.style.width = box.width + "px";
		item.style.height = box.height + "px";
	});
}

const debouncedLayout = useDebounceFn(runJustifiedLayout, 100);

let resizeObserver: ResizeObserver | null = null;

// Batch selection state
const isBatchMode = ref(false);
const selectedFaceIds = ref<string[]>([]);
const batchLoading = ref(false);
const lastSelectedIndex = ref<number>(-1);

// Rubber-band drag selection
const dragVisible = ref(false);
const dragAnchor = ref({ x: 0, y: 0 });
const dragCurrent = ref({ x: 0, y: 0 });

const dragRectStyle = computed(() => {
	const left = Math.min(dragAnchor.value.x, dragCurrent.value.x);
	const top = Math.min(dragAnchor.value.y, dragCurrent.value.y);
	const width = Math.abs(dragCurrent.value.x - dragAnchor.value.x);
	const height = Math.abs(dragCurrent.value.y - dragAnchor.value.y);
	return { left: left + "px", top: top + "px", width: width + "px", height: height + "px" };
});

function startDragSelect(e: MouseEvent) {
	if (e.button !== 0) return;
	const target = e.target as HTMLElement;
	// Only start drag on empty space (not on a photo tile or interactive child)
	if (target.closest("[data-face-id], button, input")) return;
	e.preventDefault();
	const containerRect = photoListingRef.value!.getBoundingClientRect();
	const containerScrollTop = photoListingRef.value!.parentElement?.scrollTop ?? 0;
	dragAnchor.value = { x: e.clientX - containerRect.left, y: e.clientY - containerRect.top + containerScrollTop };
	dragCurrent.value = { ...dragAnchor.value };
	dragVisible.value = true;
	document.addEventListener("mousemove", onDragMove);
	document.addEventListener("mouseup", onDragEnd);
}

function onDragMove(e: MouseEvent) {
	const containerRect = photoListingRef.value?.getBoundingClientRect();
	if (!containerRect) return;
	const containerScrollTop = photoListingRef.value?.parentElement?.scrollTop ?? 0;
	dragCurrent.value = { x: e.clientX - containerRect.left, y: e.clientY - containerRect.top + containerScrollTop };
}

function onDragEnd(e: MouseEvent) {
	document.removeEventListener("mousemove", onDragMove);
	document.removeEventListener("mouseup", onDragEnd);
	if (dragVisible.value && photoListingRef.value) {
		const selLeft = Math.min(dragAnchor.value.x, dragCurrent.value.x);
		const selTop = Math.min(dragAnchor.value.y, dragCurrent.value.y);
		const selRight = Math.max(dragAnchor.value.x, dragCurrent.value.x);
		const selBottom = Math.max(dragAnchor.value.y, dragCurrent.value.y);
		if (selRight - selLeft > 4 || selBottom - selTop > 4) {
			const containerRect = photoListingRef.value.getBoundingClientRect();
			const containerScrollTop = photoListingRef.value.parentElement?.scrollTop ?? 0;
			const tiles = photoListingRef.value.querySelectorAll<HTMLElement>("[data-face-id]");
			const intersected: string[] = [];
			tiles.forEach((tile) => {
				const tileRect = tile.getBoundingClientRect();
				const tileLeft = tileRect.left - containerRect.left;
				const tileTop = tileRect.top - containerRect.top + containerScrollTop;
				const tileRight = tileLeft + tileRect.width;
				const tileBottom = tileTop + tileRect.height;
				if (tileLeft < selRight && tileRight > selLeft && tileTop < selBottom && tileBottom > selTop) {
					const faceId = tile.dataset.faceId;
					if (faceId) intersected.push(faceId);
				}
			});
			if (e.ctrlKey || e.metaKey) {
				const toAdd = intersected.filter((id) => !selectedFaceIds.value.includes(id));
				selectedFaceIds.value = [...selectedFaceIds.value, ...toAdd];
			} else {
				selectedFaceIds.value = intersected;
			}
		}
	}
	dragVisible.value = false;
}

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

function togglePhotoSelection(photo: App.Http.Resources.Models.PhotoResource, idx: number, event: MouseEvent) {
	const faceId = getPersonFaceId(photo);
	if (!faceId) return;

	if (event.shiftKey && lastSelectedIndex.value >= 0) {
		// Range select from lastSelectedIndex to idx
		const from = Math.min(lastSelectedIndex.value, idx);
		const to = Math.max(lastSelectedIndex.value, idx);
		const rangeIds = photos.value
			.slice(from, to + 1)
			.map(getPersonFaceId)
			.filter((id): id is string => id !== null);
		const allAlreadySelected = rangeIds.every((id) => selectedFaceIds.value.includes(id));
		if (allAlreadySelected) {
			selectedFaceIds.value = selectedFaceIds.value.filter((id) => !rangeIds.includes(id));
		} else {
			const toAdd = rangeIds.filter((id) => !selectedFaceIds.value.includes(id));
			selectedFaceIds.value = [...selectedFaceIds.value, ...toAdd];
		}
	} else {
		const existing = selectedFaceIds.value.indexOf(faceId);
		if (existing === -1) {
			selectedFaceIds.value.push(faceId);
		} else {
			selectedFaceIds.value.splice(existing, 1);
		}
		lastSelectedIndex.value = idx;
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
	lastSelectedIndex.value = -1;
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
			// Keep photosStore in sync so lightbox navigation works
			photosStore.photos = photos.value;
		})
		.finally(() => {
			photosLoadingMore.value = false;
		});
}

// Lightbox actions
function openPhoto(photoId: string) {
	router.push({ name: "person", params: { personId: props.personId, photoId } });
}

function closePhoto() {
	photoStore.reset();
	router.push({ name: "person", params: { personId: props.personId } });
}

function next() {
	if (is_slideshow_active.value) {
		nextSlideshow();
	} else {
		getNext();
	}
}

function previous() {
	if (is_slideshow_active.value) {
		previousSlideshow();
	} else {
		getPrevious();
	}
}

function toggleDetails() {
	are_details_open.value = !are_details_open.value;
}

function rotateOverlay() {
	const overlays = ["none", "desc", "date", "exif"] as App.Enum.ImageOverlayType[];
	for (let i = 0; i < overlays.length; i++) {
		if (lycheeStore.image_overlay_type === overlays[i]) {
			lycheeStore.image_overlay_type = overlays[(i + 1) % overlays.length];
			return;
		}
	}
}

// Sync photoStore when photoId route param changes
watch(
	() => props.photoId,
	(id) => {
		if (id) {
			photosStore.photos = photos.value;
			photoStore.photoId = id;
			photoStore.load();
		} else {
			photoStore.reset();
		}
	},
	{ immediate: true },
);

function openEdit() {
	editName.value = person.value?.name ?? "";
	isEditing.value = true;
}

function saveEditIfChanged() {
	if (editName.value.trim() && editName.value.trim() !== person.value?.name) {
		saveEdit();
	} else {
		isEditing.value = false;
	}
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
	resizeObserver = new ResizeObserver(debouncedLayout);
	if (photoListingRef.value) resizeObserver.observe(photoListingRef.value);
});

onUpdated(() => {
	runJustifiedLayout();
	if (resizeObserver && photoListingRef.value) {
		resizeObserver.observe(photoListingRef.value);
	}
});

onUnmounted(() => {
	resizeObserver?.disconnect();
	document.removeEventListener("mousemove", onDragMove);
	document.removeEventListener("mouseup", onDragEnd);
});

// Merge modal
const isMergeModalOpen = ref(false);

function onMerged(targetPersonId: string) {
	router.push({ name: "person", params: { personId: targetPersonId } });
}

// Keybindings
// Photo operations (arrow keys are flipped for RTL languages)
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasPrevious && previous());
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && isLTR() && photoStore.hasNext && next());
onKeyStroke("ArrowLeft", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasNext && next());
onKeyStroke("ArrowRight", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && !isLTR() && photoStore.hasPrevious && previous());
onKeyStroke("i", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && toggleDetails());
onKeyStroke("o", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && rotateOverlay());
onKeyStroke(" ", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && is_slideshow_enabled.value && slideshow());
onKeyStroke("f", () => !shouldIgnoreKeystroke() && photoStore.isLoaded && togglableStore.toggleFullScreen());

// Escape handling
onKeyStroke("Escape", () => {
	// Stop slideshow if active
	if (is_slideshow_active.value) {
		stop();
		return;
	}

	// Lose focus if input is focused
	if (shouldIgnoreKeystroke() && document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
		return;
	}

	// If photo is open, close it
	if (photoStore.isLoaded) {
		closePhoto();
		return;
	}

	// Otherwise, go back to people list
	router.push({ name: "people" });
});
</script>
