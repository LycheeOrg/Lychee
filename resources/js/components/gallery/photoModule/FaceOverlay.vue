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
					:class="face.person_id ? 'bg-primary-500 text-white' : 'bg-yellow-500 text-black'"
				>
					{{ faceLabel(face) }}
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
			@assigned="emits('facesUpdated')"
			@dismissed="emits('facesUpdated')"
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

function handleClick(face: App.Http.Resources.Models.FaceResource) {
	if (ctrlHeld.value && !isTouchDev) {
		// CTRL+click: dismiss directly without modal
		FaceDetectionService.toggleDismissed(face.id)
			.then(() => {
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.assignment.dismissed"), life: 3000 });
				emits("facesUpdated");
			})
			.catch((e: { response?: { data?: { message?: string } } }) => {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
			});
	} else {
		selectedFace.value = face;
		isAssignmentOpen.value = true;
	}
}
</script>
