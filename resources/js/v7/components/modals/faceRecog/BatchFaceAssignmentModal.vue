<template>
	<Dialog
		v-model:visible="visible"
		modal
		:header="$t('people.assignment.batch_title', { count: String(faceIds.length) })"
		pt:root:class="border-none w-full max-w-md"
		@show="onShow"
	>
		<template #container="{ closeCallback }">
			<div class="p-9">
				<PersonInput ref="personInputRef" v-model:person-id="selectedPersonId" v-model:new-person-name="newPersonName" />
			</div>

			<div class="flex justify-end">
				<Button
					class="w-full border-none rounded-none rounded-bl-xl"
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
import FaceMaintenanceService from "@/services/face-maintenance-service";
import PersonInput from "@/v7/components/forms/basic/PersonInput.vue";

const props = defineProps<{
	faceIds: string[];
}>();

const emits = defineEmits<{
	assigned: [response: { assigned_count: number; person_id: string }];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useToast();
const selectedPersonId = ref<string | undefined>(undefined);
const newPersonName = ref("");
const submitting = ref(false);
const personInputRef = ref<InstanceType<typeof PersonInput> | null>(null);

function submit() {
	submitting.value = true;
	const data: { person_id?: string; new_person_name?: string } = {};
	if (selectedPersonId.value) {
		data.person_id = selectedPersonId.value;
	} else if (newPersonName.value.trim()) {
		data.new_person_name = newPersonName.value.trim();
	}

	FaceMaintenanceService.batchAssign(props.faceIds, data)
		.then((response) => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans("people.assigned_faces", { count: String(response.data.assigned_count) }),
				life: 3000,
			});
			visible.value = false;
			emits("assigned", response.data);
		})
		.catch((e) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			submitting.value = false;
		});
}

function onShow() {
	personInputRef.value?.reset();
}
</script>
