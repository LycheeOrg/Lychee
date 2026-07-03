<template>
	<USlideover v-model:open="isEditOpen" :dismissible="false" side="right" class="w-full max-w-4xl">
		<template #body>
			<UCard v-if="photo" id="lychee_sidebar" class="h-full wrap-break-word">
				<form class="w-full flex flex-col md:gap-y-4 md:grid md:grid-cols-[200px_minmax(auto,1fr)] justify-center">
					<label for="title" class="font-bold self-center">{{ $t("gallery.photo.edit.set_title") }}</label>
					<UInput id="title" v-model="title" :color="!title ? 'error' : undefined" />

					<label for="description" class="font-bold mt-4 md:mt-0">{{ $t("gallery.photo.edit.set_description") }}</label>
					<UTextarea id="description" v-model="descriptionForInput" class="w-full h-48" :rows="5" />

					<label for="tags" class="font-bold h-11 mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_tags") }}</label>
					<TagsInput id="tags" v-model="tags" :add="true" :placeholder="$t('gallery.photo.edit.no_tags')" />
					<label for="uploadDate" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_created_at") }}</label>
					<input
						id="uploadDate"
						v-model="uploadDateLocal"
						type="datetime-local"
						step="1"
						:class="{
							'border-0 p-0 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400 bg-transparent': true,
							'border-b-error': !uploadDate,
						}"
					/>

					<label for="takenAtDate" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_taken_at") }}</label>

					<div class="flex">
						<div class="flex items-center pr-2 border-t-0 rounded-t-none">
							<UTooltip text="Modify taken date">
								<UCheckbox v-model="is_taken_at_modified" />
							</UTooltip>
						</div>
						<input
							id="takenAtDate"
							v-model="takenAtDateLocal"
							type="datetime-local"
							step="1"
							:disabled="!is_taken_at_modified"
							:class="{
								'border-0 p-0 w-full border-b hover:border-b-primary-400 focus:border-b-primary-400 bg-transparent': true,
								'border-dashed': !is_taken_at_modified,
							}"
						/>
						<USelectMenu
							v-model="takenAtTzOption"
							:items="timeZoneOptions"
							label-key="label"
							:disabled="!is_taken_at_modified"
							class="border-none"
						>
							<template #item-label="{ item }">{{ item.label }}</template>
						</USelectMenu>
					</div>
					<div></div>
					<div
						class="mt-0 md:-mt-2 text-sm text-muted"
						v-html="sprintf($t('gallery.photo.edit.set_taken_at_info'), '<span class=\'text-warning-600\'>*</span>')"
					></div>

					<label for="license" class="font-bold mt-4 md:mt-0 self-center">{{ $t("gallery.photo.edit.set_license") }}</label>
					<USelectMenu id="license" v-model="license" class="w-72" :items="licenseOptions" label-key="label">
						<template #item-label="{ item }">{{ $t(item.label) }}</template>
					</USelectMenu>
					<UButton color="primary" class="w-full col-span-2 justify-center font-bold" @click="save">
						{{ $t("dialogs.button.save") }}
					</UButton>
				</form>
			</UCard>
		</template>
	</USlideover>
</template>
<script setup lang="ts">
import { computed, onMounted, ref, Ref, watch } from "vue";
import { licenseOptions, SelectOption, SelectBuilders, timeZoneOptions } from "@/config/constants";
import PhotoService from "@/services/photo-service";
import { useAppToast } from "@/v8/composables/useAppToast";
import { sprintf } from "sprintf-js";
import { useRouter } from "vue-router";
import { usePhotoRoute } from "@/composables/photo/photoRoute";
import TagsInput from "@/v8/components/forms/basic/TagsInput.vue";
import TagsService from "@/services/tags-service";
import AlbumService from "@/services/album-service";
import { usePhotoStore } from "@/stores/PhotoState";
import { storeToRefs } from "pinia";

const photoStore = usePhotoStore();

const { photo } = storeToRefs(photoStore);

const toast = useAppToast();
const router = useRouter();
const { getParentId } = usePhotoRoute(router);
const isEditOpen = defineModel("isEditOpen", { default: false }) as Ref<boolean>;

const photo_id = ref<string | undefined>(undefined);
const title = ref<string | undefined>(undefined);
const description = ref<string | undefined>(undefined);
const uploadDate = ref<Date | undefined>(undefined);
const takenAtDate = ref<Date | undefined>(undefined);
const tags = ref<string[]>([]);
const is_taken_at_modified = ref<boolean>(false);
const license = ref<SelectOption<App.Enum.LicenseType> | undefined>(undefined);
const uploadTz = ref<string | undefined>(undefined);
const takenAtTz = ref<string | undefined>(undefined);

// UTextarea's v-model requires `string | undefined` (no null).
const descriptionForInput = computed<string | undefined>({
	get: () => description.value ?? undefined,
	set: (v) => {
		description.value = v;
	},
});

const takenAtTzOption = computed<SelectOption<string> | undefined>({
	get: () => timeZoneOptions.find((o) => o.value === takenAtTz.value),
	set: (v) => {
		takenAtTz.value = v?.value;
	},
});

// Native <input type="datetime-local"> uses "YYYY-MM-DDTHH:mm:ss" in local time, with no timezone.
function dateToLocalInputValue(d: Date | undefined): string {
	if (d === undefined) return "";
	const pad = (n: number) => n.toString().padStart(2, "0");
	return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}

const uploadDateLocal = computed<string>({
	get: () => dateToLocalInputValue(uploadDate.value),
	set: (v) => {
		uploadDate.value = v ? new Date(v) : undefined;
	},
});

const takenAtDateLocal = computed<string>({
	get: () => dateToLocalInputValue(takenAtDate.value),
	set: (v) => {
		takenAtDate.value = v ? new Date(v) : undefined;
	},
});

function load(photoToEdit: App.Http.Resources.Models.PhotoResource) {
	photo_id.value = photoToEdit.id;
	title.value = photoToEdit.title;
	description.value = photoToEdit.description;
	tags.value = photoToEdit.tags.map((t) => t.name);

	const dataDate = (photoToEdit.created_at ?? "").slice(0, 19);
	uploadTz.value = (photoToEdit.created_at ?? "").slice(19);
	uploadDate.value = new Date(dataDate);
	is_taken_at_modified.value = photoToEdit.precomputed.is_taken_at_modified;

	if (photoToEdit.taken_at === null) {
		takenAtDate.value = undefined;
		takenAtTz.value = undefined;
	} else {
		const takenDate = (photoToEdit.taken_at ?? "").slice(0, 19);
		takenAtTz.value = (photoToEdit.taken_at ?? "").slice(19);
		takenAtDate.value = new Date(takenDate);
	}

	license.value = SelectBuilders.buildLicense(photoToEdit.license);
}

function save() {
	if (!photo_id.value || !title.value || !uploadDate.value) {
		return;
	}

	let takenDate = null;
	if (takenAtDate.value !== undefined) {
		takenDate = takenAtDate.value.toISOString().slice(0, 19) + (takenAtTz.value ?? "");
	}

	PhotoService.update(photo_id.value, getParentId() ?? null, {
		title: title.value,
		description: description.value ?? "",
		tags: tags.value ?? [],
		license: license.value?.value ?? "none",
		upload_date: uploadDate.value?.toISOString().slice(0, 19) + uploadTz.value,
		taken_at: is_taken_at_modified.value ? takenDate : null,
	}).then((response) => {
		toast.add({ severity: "success", summary: "Success", life: 3000 });
		// Clear cache of tags just in case we added any.
		TagsService.clearCache();
		// Update the parent album cache.
		// This is needed to ensure that the album view is updated with the new photo data
		// and that the tags input is updated with the new tags.
		AlbumService.clearCache(getParentId());
		load(response.data);
	});
}

onMounted(() => {
	if (photoStore.photo) {
		load(photoStore.photo);
	}
});

watch(
	() => photo.value,
	(newPhoto: App.Http.Resources.Models.PhotoResource | undefined, _oldPhoto) => {
		if (newPhoto) {
			load(newPhoto);
		} else {
			photo_id.value = undefined;
		}
	},
);
</script>
