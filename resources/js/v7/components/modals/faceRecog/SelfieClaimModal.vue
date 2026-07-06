<template>
	<Dialog v-model:visible="visible" modal :header="$t('people.claim_by_selfie')" pt:root:class="border-none w-full max-w-md">
		<div class="flex flex-col gap-4 p-2">
			<div class="text-sm text-muted-color text-center">{{ $t("people.claim_by_selfie_description") }}</div>

			<!-- Upload area -->
			<div
				v-if="!matchedPerson"
				class="border-2 border-dashed border-surface-600 rounded-lg p-8 flex flex-col items-center gap-3 cursor-pointer hover:border-primary-400 transition-colors"
				@click="triggerFileInput"
				@dragover.prevent
				@drop.prevent="onDrop"
			>
				<i class="pi pi-camera text-4xl text-muted-color" />
				<div class="text-sm text-muted-color text-center">{{ $t("people.claim_by_selfie_description") }}</div>
				<Button severity="primary" outlined size="small" :label="$t('gallery.upload') || 'Upload'" />
				<input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFileSelected" />
			</div>

			<!-- Preview file name -->
			<div v-if="selectedFile && !matchedPerson" class="text-sm text-center text-muted-color">
				{{ selectedFile.name }}
			</div>

			<!-- Match result -->
			<div v-if="matchedPerson" class="flex flex-col items-center gap-4">
				<div class="w-24 h-24 rounded-full overflow-hidden bg-surface-800 flex items-center justify-center">
					<img
						v-if="matchedPerson.representative_crop_url"
						:src="matchedPerson.representative_crop_url"
						:alt="matchedPerson.name"
						class="w-full h-full object-cover"
					/>
					<i v-else class="pi pi-user text-4xl text-muted-color" />
				</div>
				<div class="text-center">
					<div class="font-semibold text-lg">{{ matchedPerson.name }}</div>
					<div class="text-sm text-muted-color">{{ matchedPerson.photo_count }} {{ $t("people.photos_label") }}</div>
				</div>
				<div class="text-sm text-success-400">{{ $t("people.claims.success") }}</div>
			</div>

			<ProgressSpinner v-if="uploading" class="mx-auto w-8 h-8" />
		</div>

		<template #footer>
			<div class="flex gap-2 justify-end">
				<Button :label="$t('gallery.cancel')" severity="secondary" text @click="reset" />
				<Button
					v-if="selectedFile && !matchedPerson"
					:label="$t('people.claim_by_selfie')"
					severity="primary"
					:loading="uploading"
					:disabled="!selectedFile"
					@click="submit"
				/>
				<Button v-if="matchedPerson" :label="$t('gallery.done')" severity="success" @click="visible = false" />
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import ProgressSpinner from "primevue/progressspinner";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import PeopleService from "@/services/people-service";

const emits = defineEmits<{
	claimed: [person: App.Http.Resources.Models.PersonResource];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useToast();
const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | undefined>(undefined);
const matchedPerson = ref<App.Http.Resources.Models.PersonResource | undefined>(undefined);
const uploading = ref(false);

function triggerFileInput() {
	fileInput.value?.click();
}

function onFileSelected(event: Event) {
	const input = event.target as HTMLInputElement;
	if (input.files && input.files[0]) {
		selectedFile.value = input.files[0];
		matchedPerson.value = undefined;
	}
}

function onDrop(event: DragEvent) {
	if (event.dataTransfer?.files[0]) {
		selectedFile.value = event.dataTransfer.files[0];
		matchedPerson.value = undefined;
	}
}

function submit() {
	if (!selectedFile.value) {
		return;
	}
	uploading.value = true;
	PeopleService.claimBySelfie(selectedFile.value)
		.then((response) => {
			matchedPerson.value = response.data;
			emits("claimed", response.data);
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("people.claims.success"), life: 4000 });
		})
		.catch((e) => {
			const status = e.response?.status;
			let detail = e.response?.data?.message;
			if (status === 422) {
				detail = trans("people.claims.no_face");
			} else if (status === 404) {
				detail = trans("people.claims.no_match");
			} else if (status === 409) {
				detail = trans("people.claims.already_claimed");
			}
			toast.add({ severity: "error", summary: trans("toasts.error"), detail, life: 4000 });
		})
		.finally(() => {
			uploading.value = false;
		});
}

function reset() {
	selectedFile.value = undefined;
	matchedPerson.value = undefined;
	visible.value = false;
}
</script>
