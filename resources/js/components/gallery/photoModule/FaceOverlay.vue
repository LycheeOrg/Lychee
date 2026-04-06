<template>
	<div v-if="overlayEnabled && (faces.length > 0 || hiddenFaceCount > 0)" class="absolute inset-0 pointer-events-none">
		<!-- Face bounding box overlays -->
		<template v-for="face in visibleFaces" :key="face.id">
			<div
				v-if="!face.is_dismissed && isVisible"
				class="absolute rounded transition-opacity duration-200 pointer-events-auto cursor-pointer z-50"
				:class="
					ctrlHeld && !isTouchDev
						? ['border-2', 'border-dashed', 'border-red-500', 'cursor-crosshair']
						: face.person_id
							? ['border-2', 'border-primary-400', 'hover:border-primary-300']
							: ['border-2', 'border-yellow-400', 'hover:border-yellow-300']
				"
				:style="{
					left: face.x * 100 + '%',
					top: face.y * 100 + '%',
					width: face.width * 100 + '%',
					height: face.height * 100 + '%',
				}"
				@click.stop="handleClick(face)"
			>
				<div
					class="absolute top-full left-0 mt-0.5 px-1.5 py-0.5 text-xs rounded whitespace-nowrap max-w-32 truncate"
					:class="
						ctrlHeld && !isTouchDev ? 'bg-red-600 text-white' : face.person_id ? 'bg-primary-500 text-white' : 'bg-yellow-500 text-black'
					"
				>
					{{ ctrlHeld && !isTouchDev ? $t("people.dismiss") : faceLabel(face) }}
				</div>
			</div>
		</template>

		<!-- Privacy notice for hidden faces -->
		<div v-if="hiddenFaceCount > 0" class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded pointer-events-none">
			{{ hiddenFaceCount }} {{ $t("people.hidden_faces") }}
		</div>

		<!-- Assignment modal -->
		<FaceAssignmentModal
			v-if="selectedFace"
			v-model:visible="isAssignmentOpen"
			:face="selectedFace"
			@assigned="handleFaceUpdated"
			@dismissed="handleFaceDismissed"
		/>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue";
import { onKeyStroke } from "@vueuse/core";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import FaceAssignmentModal from "@/components/modals/FaceAssignmentModal.vue";
import FaceDetectionService from "@/services/face-detection-service";
import { shouldIgnoreKeystroke, isTouchDevice } from "@/utils/keybindings-utils";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { storeToRefs } from "pinia";

const props = defineProps<{
	faces: App.Http.Resources.Models.FaceResource[];
	hiddenFaceCount: number;
}>();

const emits = defineEmits<{
	facesUpdated: [];
}>();

const toast = useToast();
const leftMenuStore = useLeftMenuStateStore();
const photoStore = usePhotoStore();
const photosStore = usePhotosStore();
const { initData } = storeToRefs(leftMenuStore);

const isTouchDev = isTouchDevice();

// Config-driven: is the overlay feature enabled at all?
const overlayEnabled = computed(() => initData.value?.modules.is_face_overlay_enabled ?? true);

// Visibility toggle (P key) — init from config default
const defaultVisibility = computed(() => (initData.value?.modules.face_overlay_default_visibility ?? "visible") === "visible");
const isVisible = ref(true);

onMounted(() => {
	isVisible.value = defaultVisibility.value;
});

// P key toggles overlay visibility (confirmed free — F = fullscreen)
onKeyStroke("p", () => {
	if (shouldIgnoreKeystroke()) {
		return;
	}
	isVisible.value = !isVisible.value;
});

// CTRL+click dismiss mode — desktop only
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

const isAssignmentOpen = ref(false);
const selectedFace = ref<App.Http.Resources.Models.FaceResource | undefined>(undefined);

const visibleFaces = computed(() => props.faces.filter((f) => !f.is_dismissed));

function faceLabel(face: App.Http.Resources.Models.FaceResource): string {
	return face.person_name ?? "Unknown";
}

function removeFaceFromStores(faceId: string) {
	// Remove from photoStore
	if (photoStore.photo?.faces) {
		const photoFaceIndex = photoStore.photo.faces.findIndex((f) => f.id === faceId);
		if (photoFaceIndex !== -1) {
			photoStore.photo.faces.splice(photoFaceIndex, 1);
		}
	}

	// Remove from photosStore (for album view)
	const photoInAlbum = photosStore.photos.find((p) => p.id === photoStore.photo?.id);
	if (photoInAlbum?.faces) {
		const albumFaceIndex = photoInAlbum.faces.findIndex((f) => f.id === faceId);
		if (albumFaceIndex !== -1) {
			photoInAlbum.faces.splice(albumFaceIndex, 1);
		}
	}
}

function handleClick(face: App.Http.Resources.Models.FaceResource) {
	if (ctrlHeld.value && !isTouchDev) {
		// CTRL+click: dismiss directly without modal
		// Immediately remove from stores for instant feedback
		removeFaceFromStores(face.id);

		FaceDetectionService.toggleDismissed(face.id)
			.then(() => {
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.assignment.dismissed"), life: 3000 });
				emits("facesUpdated");
			})
			.catch((e: { response?: { data?: { message?: string } } }) => {
				// On error, reload to restore face
				photoStore.load();
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
			});
	} else {
		selectedFace.value = face;
		isAssignmentOpen.value = true;
	}
}

function handleFaceUpdated() {
	emits("facesUpdated");
}

function handleFaceDismissed() {
	// Immediately remove the dismissed face from stores
	if (selectedFace.value) {
		removeFaceFromStores(selectedFace.value.id);
	}
	emits("facesUpdated");
}
</script>
