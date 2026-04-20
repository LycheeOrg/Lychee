<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true">
		<template #container="{ closeCallback }">
			<div class="w-full max-w-2xl">
				<div class="p-6 overflow-y-auto max-h-[75vh]">
					<p class="mb-4 text-muted-color text-sm">{{ $t("bulk_album_edit.edit_fields_description") }}</p>

					<div class="grid grid-cols-1 gap-3">
						<p class="font-semibold text-sm">{{ $t("bulk_album_edit.section_metadata") }}</p>

						<div v-for="field in textFields" :key="field.key" class="flex items-start gap-3">
							<Checkbox v-model="editEnabled[field.key]" :binary="true" class="mt-1 shrink-0" />
							<div class="flex-1">
								<FloatLabel variant="on">
									<InputText
										:model-value="editTextValues[field.key]"
										class="w-full"
										size="small"
										@update:model-value="(v) => onTextChange(field.key, v ?? null)"
									/>
									<label class="block text-sm mb-1">{{ $t("bulk_album_edit." + field.label) }}</label>
								</FloatLabel>
							</div>
						</div>

						<div v-for="field in enumFields" :key="field.key" class="flex items-start gap-3">
							<Checkbox v-model="editEnabled[field.key]" :binary="true" class="mt-1 shrink-0" />
							<div class="flex-1">
								<FloatLabel variant="on">
									<Select
										:model-value="editEnumValues[field.key]"
										:options="field.options"
										option-label="label"
										option-value="value"
										show-clear
										class="w-full border-none"
										size="small"
										@update:model-value="(v) => onEnumChange(field.key, v)"
									>
										<template #value="slotProps">
											<span v-if="slotProps.value !== null && slotProps.value !== undefined">
												{{ $t(field.options.find((o) => o.value === slotProps.value)?.label ?? "") }}
											</span>
										</template>
										<template #option="slotProps">{{ $t(slotProps.option.label) }}</template>
									</Select>
									<label class="block text-sm mb-1">{{ $t("bulk_album_edit." + field.label) }}</label>
								</FloatLabel>
							</div>
						</div>

						<template v-for="pair in sortingPairs" :key="pair.col.key">
							<div class="flex items-start gap-2">
								<Checkbox v-model="editEnabled[pair.col.key]" :binary="true" class="mt-1 shrink-0" />
								<div class="flex-1">
									<FloatLabel variant="on">
										<Select
											:model-value="editEnumValues[pair.col.key]"
											:options="pair.col.options"
											option-label="label"
											option-value="value"
											show-clear
											class="w-full border-none"
											size="small"
											@update:model-value="(v) => onEnumChange(pair.col.key, v)"
										>
											<template #value="slotProps">
												<span v-if="slotProps.value !== null && slotProps.value !== undefined">
													{{ $t(pair.col.options.find((o) => o.value === slotProps.value)?.label ?? "") }}
												</span>
											</template>
											<template #option="slotProps">{{ $t(slotProps.option.label) }}</template>
										</Select>
										<label class="block text-sm mb-1">{{ $t("bulk_album_edit." + pair.col.label) }}</label>
									</FloatLabel>
								</div>
								<Checkbox v-model="editEnabled[pair.order.key]" :binary="true" class="mt-1 shrink-0" />
								<div class="flex-1">
									<FloatLabel variant="on">
										<Select
											:model-value="editEnumValues[pair.order.key]"
											:options="pair.order.options"
											option-label="label"
											option-value="value"
											show-clear
											class="w-full border-none"
											size="small"
											@update:model-value="(v) => onEnumChange(pair.order.key, v)"
										>
											<template #value="slotProps">
												<span v-if="slotProps.value !== null && slotProps.value !== undefined">
													{{ $t(pair.order.options.find((o) => o.value === slotProps.value)?.label ?? "") }}
												</span>
											</template>
											<template #option="slotProps">{{ $t(slotProps.option.label) }}</template>
										</Select>
										<label class="block text-sm mb-1">{{ $t("bulk_album_edit." + pair.order.label) }}</label>
									</FloatLabel>
								</div>
							</div>
						</template>

						<p class="font-semibold text-sm mt-2">{{ $t("bulk_album_edit.section_visibility") }}</p>

						<div class="grid grid-cols-2 gap-3">
							<div v-for="field in visibleBoolFields" :key="field.key" class="flex items-center gap-2">
								<Checkbox v-model="editEnabled[field.key]" :binary="true" class="shrink-0" />
								<label class="text-sm flex-1" :class="field.red ? 'text-red-500' : ''">{{
									$t("bulk_album_edit." + field.label)
								}}</label>
								<ToggleSwitch
									:model-value="editBoolValues[field.key]"
									:disabled="field.seOnly === true && !is_se_enabled"
									:style="
										field.red
											? '--p-toggleswitch-checked-background: var(--p-red-800); --p-toggleswitch-checked-hover-background: var(--p-red-900); --p-toggleswitch-hover-background: var(--p-red-900);'
											: ''
									"
									@update:model-value="(v) => onBoolChange(field.key, v as boolean)"
								/>
							</div>
						</div>
					</div>
				</div>
				<div class="flex">
					<Button severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="closeCallback">
						{{ $t("bulk_album_edit.cancel") }}
					</Button>
					<Button :disabled="!hasAnyEnabled" class="w-full border-none rounded-none rounded-br-xl font-bold" @click="doEditFields">
						{{ $t("bulk_album_edit.apply") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script lang="ts">
export default { name: "BulkEditFieldsDialog" };
</script>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Dialog from "primevue/dialog";
import InputText from "@/components/forms/basic/InputText.vue";
import Select from "primevue/select";
import ToggleSwitch from "primevue/toggleswitch";
import BulkAlbumEditService from "@/services/bulk-album-edit-service";
import {
	licenseOptions,
	photoLayoutOptions,
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	aspectRationOptions,
	timelinePhotoGranularityOptions,
	timelineAlbumGranularityOptions,
} from "@/config/constants";
import FloatLabel from "primevue/floatlabel";
import { useLycheeStateStore } from "@/stores/LycheeState";

const props = defineProps<{
	albumIds: string[];
}>();

const emits = defineEmits<{
	patched: [];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useToast();

const { is_se_enabled, is_se_preview_enabled } = storeToRefs(useLycheeStateStore());

const editEnabled = ref<Record<string, boolean>>({});
const editTextValues = ref<Record<string, string | null>>({});
const editEnumValues = ref<Record<string, string | null>>({});
const editBoolValues = ref<Record<string, boolean>>({});

const textFields = [
	{ key: "description", label: "field_description" },
	{ key: "copyright", label: "field_copyright" },
];

const enumFields = [
	{ key: "license", label: "field_license", options: licenseOptions },
	{ key: "photo_layout", label: "field_photo_layout", options: photoLayoutOptions },
	{ key: "album_thumb_aspect_ratio", label: "field_album_thumb_aspect_ratio", options: aspectRationOptions },
	{ key: "album_timeline", label: "field_album_timeline", options: timelineAlbumGranularityOptions },
	{ key: "photo_timeline", label: "field_photo_timeline", options: timelinePhotoGranularityOptions },
];

const sortingPairs = [
	{
		col: { key: "photo_sorting_col", label: "field_photo_sorting_col", options: photoSortingColumnsOptions },
		order: { key: "photo_sorting_order", label: "field_photo_sorting_order", options: sortingOrdersOptions },
	},
	{
		col: { key: "album_sorting_col", label: "field_album_sorting_col", options: albumSortingColumnsOptions },
		order: { key: "album_sorting_order", label: "field_album_sorting_order", options: sortingOrdersOptions },
	},
];

const boolFields = [
	{ key: "is_nsfw", label: "field_is_nsfw", red: true, seOnly: false },
	{ key: "is_public", label: "field_is_public", red: false, seOnly: false },
	{ key: "is_link_required", label: "field_is_link_required", red: false, seOnly: false },
	{ key: "grants_full_photo_access", label: "field_grants_full_photo_access", red: false, seOnly: false },
	{ key: "grants_download", label: "field_grants_download", red: false, seOnly: false },
	{ key: "grants_upload", label: "field_grants_upload", red: true, seOnly: true },
];

const visibleBoolFields = computed(() => boolFields.filter((f) => !f.seOnly || is_se_enabled.value || is_se_preview_enabled.value));

const hasAnyEnabled = computed(() => Object.values(editEnabled.value).some((v) => v === true));

function onTextChange(key: string, val: string | null): void {
	editTextValues.value[key] = val;
	editEnabled.value[key] = true;
}

function onEnumChange(key: string, val: string | null): void {
	editEnumValues.value[key] = val;
	editEnabled.value[key] = true;
}

function onBoolChange(key: string, val: boolean): void {
	editBoolValues.value[key] = val;
	editEnabled.value[key] = true;
}

watch(visible, (val) => {
	if (val) {
		const enabled: Record<string, boolean> = {};
		const textVals: Record<string, string | null> = {};
		const enumVals: Record<string, string | null> = {};
		const boolVals: Record<string, boolean> = {};
		textFields.forEach((f) => {
			enabled[f.key] = false;
			textVals[f.key] = null;
		});
		enumFields.forEach((f) => {
			enabled[f.key] = false;
			enumVals[f.key] = null;
		});
		sortingPairs.forEach((pair) => {
			[pair.col, pair.order].forEach((f) => {
				enabled[f.key] = false;
				enumVals[f.key] = null;
			});
		});
		boolFields.forEach((f) => {
			enabled[f.key] = false;
			boolVals[f.key] = false;
		});
		editEnabled.value = enabled;
		editTextValues.value = textVals;
		editEnumValues.value = enumVals;
		editBoolValues.value = boolVals;
	}
});

function doEditFields(): void {
	const payload: Record<string, unknown> = { album_ids: props.albumIds };
	textFields.forEach((f) => {
		if (editEnabled.value[f.key] === true) {
			payload[f.key] = editTextValues.value[f.key];
		}
	});
	enumFields.forEach((f) => {
		if (editEnabled.value[f.key] === true) {
			payload[f.key] = editEnumValues.value[f.key];
		}
	});
	sortingPairs.forEach((pair) => {
		[pair.col, pair.order].forEach((f) => {
			if (editEnabled.value[f.key] === true) {
				payload[f.key] = editEnumValues.value[f.key];
			}
		});
	});
	boolFields.forEach((f) => {
		if (editEnabled.value[f.key] === true) {
			payload[f.key] = editBoolValues.value[f.key];
		}
	});

	BulkAlbumEditService.patchAlbums(payload as Parameters<typeof BulkAlbumEditService.patchAlbums>[0])
		.then(() => {
			toast.add({ severity: "success", summary: "OK", detail: "bulk_album_edit.success_patch", life: 3000 });
			visible.value = false;
			emits("patched");
		})
		.catch(() => {
			toast.add({ severity: "error", summary: "Error", detail: "bulk_album_edit.error_patch", life: 3000 });
		});
}
</script>
