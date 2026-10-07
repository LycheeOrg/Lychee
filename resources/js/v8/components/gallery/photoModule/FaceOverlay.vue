<template>
	<div v-if="overlayEnabled && isVisible && (faces.length > 0 || hiddenFaceCount > 0)" class="absolute inset-0 pointer-events-none">
		<!-- Face bounding box overlays -->
		<template v-for="face in visibleFaces" :key="face.id">
			<div
				v-if="!face.is_dismissed"
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
		<div v-if="hiddenFaceCount > 0" class="absolute bottom-2 left-2 bg-black/60 text-inverted text-xs px-2 py-1 rounded pointer-events-none">
			{{ hiddenFaceCount }} {{ $t("people.hidden_faces") }}
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import FaceDetectionService from "@/services/face-detection-service";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useGlobalRights } from "@/composables/useGlobalRights";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import { definePanelShortcuts } from "@/v8/composables/usePanelShortcuts";

const props = defineProps<{
	faces: App.Http.Resources.Models.FaceResource[];
	hiddenFaceCount: number;
}>();

const emits = defineEmits<{
	facesUpdated: [];
}>();

const toast = useAppToast();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const { rights } = useGlobalRights();
const { is_face_assignment_visible, face_for_assignment } = storeToRefs(togglableStore);

const isTouchDev = isTouchDevice();

// Config-driven: is the overlay feature enabled at all?
const overlayEnabled = computed(() => rights.value?.modules.is_face_overlay_enabled ?? true);

// Visibility toggle (P key) — stored in lycheeStore for persistence
const isVisible = computed(() => lycheeStore.is_face_overlay_visible);

// P key toggles overlay visibility
definePanelShortcuts({
	p: () => (lycheeStore.is_face_overlay_visible = !lycheeStore.is_face_overlay_visible),
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
		face_for_assignment.value = face;
		is_face_assignment_visible.value = true;
	}
}
</script>
