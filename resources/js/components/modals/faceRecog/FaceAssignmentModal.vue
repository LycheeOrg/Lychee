<template>
	<Dialog v-model:visible="visible" modal :header="$t('people.assignment.title')" pt:root:class="border-none w-full max-w-md" @show="onShow">
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 p-9">
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
				<div class="text-center text-sm text-muted-color grid grid-cols-2 gap-1">
					<div class="text-right">{{ $t("people.confidence") }}:</div>
					<div class="text-left">{{ Math.round(face.confidence * 100) }}%</div>
					<div class="text-right">{{ $t("people.laplacian_variance") }}:</div>
					<div class="text-left">{{ Math.round(face.laplacian_variance * 10) / 10 }}</div>
				</div>

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

				<PersonInput ref="personInputRef" v-model:person-id="selectedPersonId" v-model:new-person-name="newPersonName" />
			</div>

			<div class="flex justify-end">
				<Button
					class="w-full border-none rounded-none rounded-bl-xl"
					:label="$t('people.assignment.dismiss')"
					severity="danger"
					text
					:loading="dismissing"
					@click="dismiss"
				/>
				<Button
					class="w-full border-none rounded-none"
					:label="$t('people.assignment.cancel')"
					severity="secondary"
					text
					@click="closeCallback"
				/>
				<Button
					class="w-full border-none rounded-none rounded-br-xl"
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
import { ref } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import FaceDetectionService from "@/services/face-detection-service";
import PersonInput from "@/components/forms/basic/PersonInput.vue";

const props = defineProps<{
	face: App.Http.Resources.Models.FaceResource;
}>();

const emits = defineEmits<{
	assigned: [face: App.Http.Resources.Models.FaceResource];
	dismissed: [];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useToast();
const selectedPersonId = ref<string | undefined>(undefined);
const newPersonName = ref("");
const submitting = ref(false);
const dismissing = ref(false);
const personInputRef = ref<InstanceType<typeof PersonInput> | null>(null);

function selectSuggestion(suggestion: App.Http.Resources.Models.FaceSuggestionResource) {
	if (suggestion.person_name) {
		personInputRef.value?.selectByName(suggestion.person_name);
	}
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
		.then((response) => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.assignment.success"), life: 3000 });
			visible.value = false;
			emits("assigned", response.data as App.Http.Resources.Models.FaceResource);
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			submitting.value = false;
		});
}

function dismiss() {
	dismissing.value = true;
	FaceDetectionService.toggleDismissed(props.face.id)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.assignment.dismissed"), life: 3000 });
			visible.value = false;
			emits("dismissed");
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			dismissing.value = false;
		});
}

function onShow() {
	personInputRef.value?.reset();
}
</script>
