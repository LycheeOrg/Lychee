<template>
	<UModal v-model:open="visible" :dismissible="true" @update:open="(v) => v && onShow()">
		<template #header>
			<span class="font-bold">{{ $t("people.assignment.batch_title", { count: String(faceIds.length) }) }}</span>
		</template>
		<template #body>
			<PersonInput ref="personInputRef" v-model:person-id="selectedPersonId" v-model:new-person-name="newPersonName" />
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" :label="$t('people.assignment.cancel')" color="neutral" variant="soft" @click="visible = false" />
				<UButton
					class="flex-1 justify-center"
					:label="$t('people.assignment.confirm')"
					color="primary"
					:disabled="!selectedPersonId && !newPersonName.trim()"
					:loading="submitting"
					@click="submit"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import FaceMaintenanceService from "@/services/face-maintenance-service";
import PersonInput from "@/v8/components/forms/basic/PersonInput.vue";

const props = defineProps<{
	faceIds: string[];
}>();

const emits = defineEmits<{
	assigned: [response: { assigned_count: number; person_id: string }];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useAppToast();
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
