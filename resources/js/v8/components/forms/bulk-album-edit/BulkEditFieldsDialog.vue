<template>
	<UModal v-model:open="visible" :dismissible="true" :ui="{ content: 'max-w-2xl' }">
		<template #header>
			<span class="font-bold">{{ $t("bulk_album_edit.section_metadata") }}</span>
		</template>
		<template #body>
			<div class="overflow-y-auto max-h-[75vh]">
				<p class="mb-4 text-muted text-sm">{{ $t("bulk_album_edit.edit_fields_description") }}</p>

				<div class="grid grid-cols-1 gap-3">
					<p class="font-semibold text-sm">{{ $t("bulk_album_edit.section_metadata") }}</p>

					<div v-for="field in textFields" :key="field.key" class="flex items-start gap-3">
						<UCheckbox v-model="editEnabled[field.key]" class="mt-2 shrink-0" />
						<UFormField :label="$t('bulk_album_edit.' + field.label)" class="flex-1">
							<UInput
								:model-value="editTextValues[field.key] ?? undefined"
								class="w-full"
								size="sm"
								@update:model-value="(v: string | undefined) => onTextChange(field.key, v ?? null)"
							/>
						</UFormField>
					</div>

					<div v-for="field in enumFields" :key="field.key" class="flex items-start gap-3">
						<UCheckbox v-model="editEnabled[field.key]" class="mt-2 shrink-0" />
						<UFormField :label="$t('bulk_album_edit.' + field.label)" class="flex-1">
							<USelectMenu
								:model-value="findOption(field.options, editEnumValues[field.key])"
								:items="field.options"
								label-key="label"
								class="w-full"
								size="sm"
								@update:model-value="(v: SelectOption<string> | undefined) => onEnumChange(field.key, v?.value ?? null)"
							>
								<template #item-label="{ item }">{{ $t(item.label) }}</template>
								<template #default="{ modelValue }">
									<span v-if="modelValue">{{ $t((modelValue as SelectOption<string>).label) }}</span>
								</template>
							</USelectMenu>
						</UFormField>
					</div>

					<template v-for="pair in sortingPairs" :key="pair.col.key">
						<div class="flex items-start gap-2">
							<UCheckbox v-model="editEnabled[pair.col.key]" class="mt-2 shrink-0" />
							<UFormField :label="$t('bulk_album_edit.' + pair.col.label)" class="flex-1">
								<USelectMenu
									:model-value="findOption(pair.col.options, editEnumValues[pair.col.key])"
									:items="pair.col.options"
									label-key="label"
									class="w-full"
									size="sm"
									@update:model-value="(v: SelectOption<string> | undefined) => onEnumChange(pair.col.key, v?.value ?? null)"
								>
									<template #item-label="{ item }">{{ $t(item.label) }}</template>
									<template #default="{ modelValue }">
										<span v-if="modelValue">{{ $t((modelValue as SelectOption<string>).label) }}</span>
									</template>
								</USelectMenu>
							</UFormField>
							<UCheckbox v-model="editEnabled[pair.order.key]" class="mt-2 shrink-0" />
							<UFormField :label="$t('bulk_album_edit.' + pair.order.label)" class="flex-1">
								<USelectMenu
									:model-value="findOption(pair.order.options, editEnumValues[pair.order.key])"
									:items="pair.order.options"
									label-key="label"
									class="w-full"
									size="sm"
									@update:model-value="(v: SelectOption<string> | undefined) => onEnumChange(pair.order.key, v?.value ?? null)"
								>
									<template #item-label="{ item }">{{ $t(item.label) }}</template>
									<template #default="{ modelValue }">
										<span v-if="modelValue">{{ $t((modelValue as SelectOption<string>).label) }}</span>
									</template>
								</USelectMenu>
							</UFormField>
						</div>
					</template>

					<p class="font-semibold text-sm mt-2">{{ $t("bulk_album_edit.section_visibility") }}</p>

					<div class="grid grid-cols-2 gap-3">
						<div v-for="field in visibleBoolFields" :key="field.key" class="flex items-center gap-2">
							<UCheckbox v-model="editEnabled[field.key]" class="shrink-0" :disabled="field.seOnly === true && !is_se_enabled" />
							<label class="text-sm flex-1" :class="field.red ? 'text-error' : ''">{{ $t("bulk_album_edit." + field.label) }}</label>
							<USwitch
								:model-value="editBoolValues[field.key]"
								:disabled="field.seOnly === true && !is_se_enabled"
								:color="field.red ? 'error' : 'primary'"
								@update:model-value="(v: boolean) => onBoolChange(field.key, v)"
							/>
						</div>
					</div>
				</div>
			</div>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" :label="$t('bulk_album_edit.cancel')" color="neutral" variant="soft" @click="visible = false" />
				<UButton
					class="flex-1 justify-center"
					:label="$t('bulk_album_edit.apply')"
					color="primary"
					:disabled="!hasAnyEnabled"
					@click="doEditFields"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import BulkAlbumEditService from "@/services/bulk-album-edit-service";
import {
	licenseOptions,
	photoLayoutOptions,
	photoSortingColumnsOptions,
	albumSortingColumnsOptions,
	sortingOrdersOptions,
	aspectRatioOptions,
	timelinePhotoGranularityOptions,
	timelineAlbumGranularityOptions,
	type SelectOption,
} from "@/config/constants";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { trans } from "laravel-vue-i18n";

const props = defineProps<{
	albumIds: string[];
}>();

const emits = defineEmits<{
	patched: [];
}>();

const visible = defineModel<boolean>("visible", { default: false });

const toast = useAppToast();

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
	{ key: "album_thumb_aspect_ratio", label: "field_album_thumb_aspect_ratio", options: aspectRatioOptions },
	{ key: "album_timeline", label: "field_album_timeline", options: timelineAlbumGranularityOptions },
	{ key: "photo_timeline", label: "field_photo_timeline", options: timelinePhotoGranularityOptions },
] as { key: string; label: string; options: SelectOption<string>[] }[];

const sortingPairs = [
	{
		col: { key: "photo_sorting_col", label: "field_photo_sorting_col", options: photoSortingColumnsOptions },
		order: { key: "photo_sorting_order", label: "field_photo_sorting_order", options: sortingOrdersOptions },
	},
	{
		col: { key: "album_sorting_col", label: "field_album_sorting_col", options: albumSortingColumnsOptions },
		order: { key: "album_sorting_order", label: "field_album_sorting_order", options: sortingOrdersOptions },
	},
] as { col: { key: string; label: string; options: SelectOption<string>[] }; order: { key: string; label: string; options: SelectOption<string>[] } }[];

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

function findOption(options: SelectOption<string>[], value: string | null): SelectOption<string> | undefined {
	return options.find((o) => o.value === value);
}

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
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("bulk_album_edit.success_patch"), life: 3000 });
			visible.value = false;
			emits("patched");
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("bulk_album_edit.error_patch"), life: 3000 });
		});
}
</script>
