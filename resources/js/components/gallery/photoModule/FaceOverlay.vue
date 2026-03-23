<template>
	<div v-if="faces.length > 0 || hiddenFaceCount > 0" class="absolute inset-0 pointer-events-none">
		<!-- Face bounding box overlays -->
		<template v-for="face in visibleFaces" :key="face.id">
			<div
				v-if="!face.is_dismissed"
				class="absolute border-2 rounded transition-opacity duration-200 pointer-events-auto cursor-pointer"
				:class="face.person_id ? 'border-primary-400 hover:border-primary-300' : 'border-yellow-400 hover:border-yellow-300'"
				:style="{
					left: face.x * 100 + '%',
					top: face.y * 100 + '%',
					width: face.width * 100 + '%',
					height: face.height * 100 + '%',
				}"
				@click.stop="openAssignment(face)"
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
		<FaceAssignmentModal v-if="selectedFace" v-model:visible="isAssignmentOpen" :face="selectedFace" @assigned="emits('facesUpdated')" />
	</div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import FaceAssignmentModal from "@/components/modals/FaceAssignmentModal.vue";

const props = defineProps<{
	faces: App.Http.Resources.Models.FaceResource[];
	hiddenFaceCount: number;
}>();

const emits = defineEmits<{
	facesUpdated: [];
}>();

const isAssignmentOpen = ref(false);
const selectedFace = ref<App.Http.Resources.Models.FaceResource | undefined>(undefined);

const visibleFaces = computed(() => props.faces.filter((f) => !f.is_dismissed));

function faceLabel(face: App.Http.Resources.Models.FaceResource): string {
	return face.person_name ?? "Unknown";
}

function openAssignment(face: App.Http.Resources.Models.FaceResource) {
	selectedFace.value = face;
	isAssignmentOpen.value = true;
}
</script>
