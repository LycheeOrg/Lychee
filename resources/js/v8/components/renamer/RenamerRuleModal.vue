<template>
	<UModal v-model:open="dialogVisible" :dismissible="true">
		<template #header>
			<span class="font-bold text-xl">{{ isEdit ? $t("renamer.edit_rule") : $t("renamer.create_rule") }}</span>
		</template>
		<template #body>
			<form @submit.prevent="save">
				<div class="flex flex-col gap-8">
					<!-- Rule Name -->
					<UFormField :label="$t('renamer.rule_name')" required :error="errors.rule">
						<UInput id="rule" v-model="form.rule" class="w-full" required />
					</UFormField>

					<!-- Description -->
					<UFormField :label="$t('renamer.description')">
						<UTextarea
							id="description"
							v-model="descriptionForInput"
							:placeholder="$t('renamer.description_placeholder')"
							:rows="2"
							class="w-full"
						/>
					</UFormField>

					<!-- Mode -->
					<div class="flex flex-col">
						<div class="flex items-center">
							<label for="mode" class="font-semibold w-full ltr:text-left rtl:text-right">
								{{ $t("renamer.mode") }} <span class="text-red-500">*</span>
							</label>
							<USelectMenu
								id="mode"
								v-model="selectedMode"
								:items="modeOptions"
								label-key="label"
								:placeholder="$t('renamer.select_mode')"
								class="w-full"
							>
								<template #item-label="{ item }">{{ item.label }}</template>
							</USelectMenu>
						</div>
						<small v-if="errors.mode" class="text-error">{{ errors.mode }}</small>
						<small class="text-muted ltr:text-left rtl:text-right">
							<template v-if="form.mode === 'regex'"
								>{{ $t("renamer.mode_regex_description") }}
								<UIcon name="prime:question-circle" class="text-xs cursor-pointer" @click="showHelpRegex = !showHelpRegex" />
							</template>
							<template v-else>{{ modeOptions.find((o) => o.value === form.mode)?.description }}</template>
						</small>
					</div>

					<!-- Needle (Pattern) -->
					<UFormField v-if="hasExtraParams(form.mode)" :label="$t('renamer.pattern')" required :error="errors.needle">
						<UInput id="needle" v-model="form.needle" class="w-full" required />
						<template #hint>
							<small class="text-muted">{{ $t("renamer.pattern_help") }}</small>
						</template>
					</UFormField>

					<!-- Replacement -->
					<UFormField v-if="hasExtraParams(form.mode)" :label="$t('renamer.replacement')" required :error="errors.replacement">
						<UInput id="replacement" v-model="form.replacement" class="w-full" required />
						<template #hint>
							<small class="text-muted">{{ $t("renamer.replacement_help") }}</small>
						</template>
					</UFormField>

					<div
						v-if="showHelpRegex && form.mode === 'regex'"
						class="text-muted text-justify text-xs -mt-4 bg-elevated rounded p-2"
						@click="showHelpRegex = false"
					>
						<UIcon name="prime:question-circle" class="ltr:mr-2 rtl:ml-2" />
						<span class="renamer-help-regex" v-html="$t('renamer.regex_help')"></span><br />
						<a href="https://regex101.com" target="_blank" rel="noopener noreferrer" class="text-primary-500 hover:underline"
							>https://regex101.com</a
						><br />
						<a
							href="https://www.php.net/manual/en/function.preg-replace.php"
							target="_blank"
							rel="noopener noreferrer"
							class="text-primary-500 hover:underline"
							>PHP preg_replace</a
						>
					</div>
					<!-- Order -->
					<div class="flex flex-col">
						<div class="flex items-center">
							<label for="order" class="font-semibold w-full ltr:text-left rtl:text-right"
								>{{ $t("renamer.order") }} <span class="text-red-500">*</span></label
							>
							<UInputNumber
								id="order"
								v-model="form.order"
								:min="1"
								:max="999"
								:placeholder="$t('renamer.execution_order')"
								class="w-full"
								required
							/>
						</div>
						<small v-if="errors.order" class="text-error">{{ errors.order }}</small>
						<small class="text-muted ltr:text-left rtl:text-right">{{ $t("renamer.order_help") }} </small>
					</div>

					<!-- Enabled -->
					<div class="grid grid-cols-2">
						<div class="flex flex-col">
							<div class="flex items-center gap-2">
								<USwitch id="is_enabled" v-model="form.is_enabled" />
								<label for="is_enabled"
									><span class="font-semibold">{{ $t("renamer.enabled") }}</span></label
								>
							</div>
							<small class="text-muted ltr:text-left rtl:text-right">{{ $t("renamer.enabled_help") }}</small>
						</div>
						<div class="flex flex-col">
							<div class="flex items-center gap-2">
								<USwitch id="is_photo_rule" v-model="form.is_photo_rule" />
								<label for="is_photo_rule"
									><span class="font-semibold">{{ $t("renamer.photo_rule") }}</span></label
								>
							</div>
							<div class="flex items-center gap-2">
								<USwitch id="is_album_rule" v-model="form.is_album_rule" />
								<label for="is_album_rule"
									><span class="font-semibold">{{ $t("renamer.album_rule") }}</span></label
								>
							</div>
						</div>
					</div>
				</div>
			</form>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton :label="$t('renamer.cancel')" color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="onHide" />
				<UButton
					:label="isEdit ? $t('renamer.update') : $t('renamer.create')"
					color="neutral"
					class="flex-1 justify-center font-bold"
					:loading="isLoading"
					@click="save"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import RenamerService, { type CreateRenamerRuleRequest, type UpdateRenamerRuleRequest } from "@/services/renamer-service";

const props = defineProps<{
	visible: boolean;
	rule?: App.Http.Resources.Models.RenamerRuleResource;
}>();

const emit = defineEmits<{
	"update:visible": [value: boolean];
	saved: [];
}>();

const dialogVisible = computed({
	get: () => props.visible,
	set: (value: boolean) => emit("update:visible", value),
});

const isEdit = computed(() => props.rule !== undefined);

const toast = useAppToast();
const isLoading = ref(false);
const showHelpRegex = ref(false);

type ModeOption = { label: string; value: App.Enum.RenamerModeType; description: string };

const modeOptions = computed<ModeOption[]>(() => [
	{ label: trans("renamer.mode_first"), value: "first", description: trans("renamer.mode_first_description") },
	{ label: trans("renamer.mode_all"), value: "all", description: trans("renamer.mode_all_description") },
	{ label: trans("renamer.mode_regex"), value: "regex", description: trans("renamer.mode_regex_description") },
	{ label: trans("renamer.mode_trim"), value: "trim", description: trans("renamer.mode_trim_description") },
	{ label: trans("renamer.mode_strtolower"), value: "strtolower", description: trans("renamer.mode_strtolower_description") },
	{ label: trans("renamer.mode_strtoupper"), value: "strtoupper", description: trans("renamer.mode_strtoupper_description") },
	{ label: trans("renamer.mode_ucwords"), value: "ucwords", description: trans("renamer.mode_ucwords_description") },
	{ label: trans("renamer.mode_ucfirst"), value: "ucfirst", description: trans("renamer.mode_ucfirst_description") },
]);

function hasExtraParams(mode: App.Enum.RenamerModeType): boolean {
	return ["first", "all", "regex"].includes(mode);
}

const form = ref<CreateRenamerRuleRequest>({
	rule: "",
	description: "",
	needle: "",
	replacement: "",
	mode: "all" as App.Enum.RenamerModeType,
	order: 1,
	is_enabled: true,
	is_photo_rule: true,
	is_album_rule: true,
});

// UTextarea's v-model requires `string | undefined` (no null); `form.description` carries
// `string | null` to match the create/update request payload shape.
const descriptionForInput = computed<string | undefined>({
	get: () => form.value.description ?? undefined,
	set: (v) => {
		form.value.description = v ?? "";
	},
});

const selectedMode = computed<ModeOption | undefined>({
	get: () => modeOptions.value.find((o) => o.value === form.value.mode),
	set: (v) => {
		if (v) form.value.mode = v.value;
	},
});

const errors = ref<Record<string, string>>({});

function resetForm() {
	if (props.rule) {
		// Edit mode - populate form with existing rule
		form.value = {
			rule: props.rule.rule,
			description: props.rule.description,
			needle: props.rule.needle,
			replacement: props.rule.replacement,
			mode: props.rule.mode,
			order: props.rule.order,
			is_enabled: props.rule.is_enabled,
			is_photo_rule: props.rule.is_photo_rule,
			is_album_rule: props.rule.is_album_rule,
		};
	} else {
		// Create mode - reset to defaults
		form.value = {
			rule: "",
			description: "",
			needle: "",
			replacement: "",
			mode: "all" as App.Enum.RenamerModeType,
			order: 1,
			is_enabled: true,
			is_photo_rule: true,
			is_album_rule: true,
		};
	}
	errors.value = {};
}

function validateForm(): boolean {
	errors.value = {};

	if (!form.value.rule.trim()) {
		errors.value.rule = trans("renamer.rule_name_required");
	}

	if (hasExtraParams(form.value.mode) && !form.value.needle) {
		errors.value.needle = trans("renamer.pattern_required");
	}

	if (!form.value.mode) {
		errors.value.mode = trans("renamer.mode_required");
	}

	if (!form.value.order || form.value.order < 1) {
		errors.value.order = trans("renamer.order_positive");
	}

	return Object.keys(errors.value).length === 0;
}

function save() {
	if (!validateForm()) {
		return;
	}

	isLoading.value = true;

	const savePromise = isEdit.value
		? RenamerService.update({
				...form.value,
				rule_id: props.rule!.id,
			} as UpdateRenamerRuleRequest)
		: RenamerService.create(form.value);

	savePromise
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("renamer.success"),
				detail: trans(isEdit.value ? "renamer.rule_updated" : "renamer.rule_created"),
				life: 3000,
			});
			emit("saved");
		})
		.catch((error) => {
			console.error("Failed to save renamer rule:", error);

			// Handle validation errors from backend
			if (error.response?.status === 422 && error.response?.data?.errors) {
				const backendErrors = error.response.data.errors;
				Object.keys(backendErrors).forEach((field) => {
					errors.value[field] = Array.isArray(backendErrors[field]) ? backendErrors[field][0] : backendErrors[field];
				});
			} else {
				toast.add({
					severity: "error",
					summary: trans("renamer.error"),
					detail: trans(isEdit.value ? "renamer.failed_to_update" : "renamer.failed_to_create"),
					life: 3000,
				});
			}
		})
		.finally(() => {
			isLoading.value = false;
		});
}

function onHide() {
	emit("update:visible", false);
}

// Watch for prop changes to reset form
watch(
	() => props.visible,
	(newValue) => {
		if (newValue) {
			resetForm();
		}
	},
);
</script>
<style>
.renamer-help-regex code {
	background-color: var(--ui-bg-elevated);
	font-family: var(--font-mono);
	font-size: var(--text-2xs);
	padding: 2px 4px;
	border-radius: 4px;
}
</style>
