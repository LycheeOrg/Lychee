<template>
	<Dialog v-model:visible="visible" modal :header="$t('people.merge.title')" pt:root:class="border-none w-full max-w-md">
		<div class="flex flex-col gap-4 p-2">
			<!-- Source person info -->
			<div class="flex items-center gap-3 p-3 rounded-lg bg-surface-800">
				<img
					v-if="sourcePerson.representative_crop_url"
					:src="sourcePerson.representative_crop_url"
					:alt="sourcePerson.name"
					class="w-10 h-10 rounded-full object-cover shrink-0"
				/>
				<i v-else class="pi pi-user w-10 h-10 flex items-center justify-center text-xl text-muted-color shrink-0" />
				<div>
					<div class="font-semibold">{{ sourcePerson.name }}</div>
					<div class="text-xs text-muted-color">{{ sourcePerson.face_count }} {{ $t("people.faces_label") }}</div>
				</div>
			</div>

			<div class="text-center text-sm text-muted-color">{{ $t("people.merge.into") }}</div>

			<!-- Target person dropdown -->
			<Select
				v-model="targetPersonId"
				:options="people.filter((p) => p.id !== sourcePerson.id)"
				option-label="name"
				option-value="id"
				:placeholder="$t('people.merge.select_target')"
				class="w-full"
				filter
				:loading="loadingPeople"
			>
				<template #option="slotProps">
					<div class="flex items-center gap-2">
						<img
							v-if="slotProps.option.representative_crop_url"
							:src="slotProps.option.representative_crop_url"
							class="w-6 h-6 rounded-full object-cover shrink-0"
							alt=""
						/>
						<i v-else class="pi pi-user w-6 h-6 flex items-center justify-center text-muted-color shrink-0" />
						<span class="flex-1 truncate">{{ slotProps.option.name }}</span>
						<span class="text-xs text-muted-color shrink-0">{{ slotProps.option.face_count }}</span>
					</div>
				</template>
			</Select>

			<!-- Warning -->
			<div class="text-xs text-warning-500 flex items-start gap-2">
				<i class="pi pi-exclamation-triangle mt-0.5 shrink-0" />
				<span>{{ $t("people.merge.warning") }}</span>
			</div>
		</div>

		<template #footer>
			<div class="flex gap-2 justify-end">
				<Button :label="$t('gallery.cancel')" severity="secondary" text @click="visible = false" />
				<Button :label="$t('people.merge.confirm')" severity="danger" :disabled="!targetPersonId" :loading="submitting" @click="submit" />
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Select from "primevue/select";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import PeopleService from "@/services/people-service";

const props = defineProps<{
	sourcePerson: App.Http.Resources.Models.PersonResource;
}>();

const emits = defineEmits<{
	merged: [targetPersonId: string];
}>();

const visible = defineModel<boolean>("visible", { default: false });
const toast = useToast();
const people = ref<App.Http.Resources.Models.PersonResource[]>([]);
const loadingPeople = ref(false);
const targetPersonId = ref<string | undefined>(undefined);
const submitting = ref(false);

function loadPeople() {
	loadingPeople.value = true;
	PeopleService.getPeople()
		.then((response) => {
			people.value = response.data.persons;
		})
		.finally(() => {
			loadingPeople.value = false;
		});
}

function submit() {
	if (!targetPersonId.value) {
		return;
	}
	submitting.value = true;
	PeopleService.merge(targetPersonId.value, props.sourcePerson.id)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.merge.success"), life: 3000 });
			visible.value = false;
			emits("merged", targetPersonId.value as string);
		})
		.catch((e: { response?: { data?: { message?: string } } }) => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: e.response?.data?.message, life: 3000 });
		})
		.finally(() => {
			submitting.value = false;
		});
}

onMounted(() => {
	loadPeople();
});
</script>
