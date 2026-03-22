<template>
	<Dialog v-model:visible="visible" modal :header="$t('people.assignment.title')" pt:root:class="border-none w-full max-w-md">
		<div class="flex flex-col gap-4 p-2">
			<!-- Face crop preview -->
			<div class="flex justify-center">
				<img
					v-if="face.crop_url"
					:src="face.crop_url"
					alt="Face crop"
					class="w-24 h-24 rounded-full object-cover border-2 border-surface-700"
				/>
				<div v-else class="w-24 h-24 rounded-full bg-surface-800 flex items-center justify-center">
					<i class="pi pi-user text-4xl text-muted-color" />
				</div>
			</div>
			<div class="text-center text-sm text-muted-color">{{ $t("people.confidence") }}: {{ Math.round(face.confidence * 100) }}%</div>

			<!-- Suggestions -->
			<div v-if="face.suggestions.length > 0" class="flex flex-col gap-2">
				<div class="text-sm font-semibold text-muted-color-emphasis">Suggestions</div>
				<div class="flex gap-2 flex-wrap">
					<Button
						v-for="suggestion in face.suggestions"
						:key="suggestion.suggested_face_id"
						severity="secondary"
						outlined
						size="small"
						@click="selectSuggestion(suggestion)"
					>
						<img v-if="suggestion.crop_url" :src="suggestion.crop_url" class="w-6 h-6 rounded-full object-cover mr-1" alt="" />
						{{ suggestion.person_name ?? $t("people.unknown") }}
						({{ Math.round(suggestion.confidence * 100) }}%)
					</Button>
				</div>
			</div>

			<!-- Select existing person -->
			<div>
				<label class="block text-sm text-muted-color mb-1">{{ $t("people.assignment.select_person") }}</label>
				<Select
					v-model="selectedPersonId"
					:options="people"
					option-label="name"
					option-value="id"
					:placeholder="$t('people.assignment.select_person')"
					class="w-full"
					filter
					:loading="loadingPeople"
					@change="newPersonName = ''"
				/>
			</div>

			<!-- Or create new person -->
			<div>
				<label class="block text-sm text-muted-color mb-1">{{ $t("people.assignment.new_person") }}</label>
				<InputText
					v-model="newPersonName"
					class="w-full"
					:placeholder="$t('people.assignment.new_person_placeholder')"
					@input="selectedPersonId = undefined"
				/>
			</div>
		</div>

		<template #footer>
			<div class="flex gap-2 justify-end">
				<Button :label="$t('people.assignment.cancel')" severity="secondary" text @click="visible = false" />
				<Button
					:label="$t('people.assignment.confirm')"
					severity="primary"
					:disabled="!selectedPersonId && !newPersonName.trim()"
					:loading="submitting"
					@click="submit"
				/>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Select from "primevue/select";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import FaceDetectionService from "@/services/face-detection-service";
import PeopleService from "@/services/people-service";

const props = defineProps<{
	face: App.Http.Resources.Models.FaceResource;
}>();

const emits = defineEmits<{
	assigned: [];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useToast();
const people = ref<App.Http.Resources.Models.PersonResource[]>([]);
const loadingPeople = ref(false);
const selectedPersonId = ref<string | undefined>(undefined);
const newPersonName = ref("");
const submitting = ref(false);

function selectSuggestion(suggestion: App.Http.Resources.Models.FaceSuggestionResource) {
	if (suggestion.person_name) {
		const match = people.value.find((p) => p.name === suggestion.person_name);
		if (match) {
			selectedPersonId.value = match.id;
			newPersonName.value = "";
		}
	}
}

function loadPeople() {
	loadingPeople.value = true;
	PeopleService.getPeople()
		.then((response) => {
			people.value = response.data.data;
		})
		.finally(() => {
			loadingPeople.value = false;
		});
}

function submit() {
	submitting.value = true;
	const data: { person_id?: string; new_person_name?: string } = {};
	if (selectedPersonId.value) {
		data.person_id = selectedPersonId.value;
	} else if (newPersonName.value.trim()) {
		data.new_person_name = newPersonName.value.trim();
	}

	FaceDetectionService.assignFace(props.face.id, data)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.assignment.success"), life: 3000 });
			visible.value = false;
			selectedPersonId.value = undefined;
			newPersonName.value = "";
			emits("assigned");
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			submitting.value = false;
		});
}

watch(visible, (newVal) => {
	if (newVal) {
		selectedPersonId.value = undefined;
		newPersonName.value = "";
		loadPeople();
	}
});
</script>
