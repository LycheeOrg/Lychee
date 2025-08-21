<template>
	<Dialog v-model:visible="dialogVisible" pt:root:class="border-none" modal :dismissable-mask="true" @hide="onHide">
		<template #container>
			<div class="pt-9 px-9 w-full text-center font-bold text-xl">
				{{ isEdit ? $t("renamer.edit_rule") : $t("renamer.create_rule") }}
			</div>
			<div class="p-9 text-center text-muted-color w-2xl max-w-2xl text-wrap">
				<form @submit.prevent="save">
					<div class="flex flex-col gap-8">
						<!-- Rule Name -->
						<FloatLabel variant="on">
							<InputText id="rule" v-model="form.rule" :class="{ 'p-invalid': errors.rule }" class="w-full" required />
							<label for="rule" class="font-semibold">{{ $t("renamer.rule_name") }} <span class="text-red-500">*</span></label>
							<small v-if="errors.rule" class="p-error">{{ errors.rule }}</small>
						</FloatLabel>

						<!-- Description -->
						<FloatLabel variant="on">
							<label for="description" class="font-semibold">{{ $t("renamer.description") }}</label>
							<Textarea
								id="description"
								v-model="form.description"
								:placeholder="$t('renamer.description_placeholder')"
								:rows="2"
								class="w-full"
							/>
						</FloatLabel>

						<!-- Needle (Pattern) -->
						<div class="flex flex-col">
							<FloatLabel variant="on">
								<InputText id="needle" v-model="form.needle" :class="{ 'p-invalid': errors.needle }" class="w-full" required />
								<label for="needle" class="font-semibold">{{ $t("renamer.pattern") }} <span class="text-red-500">*</span></label>
							</FloatLabel>
							<small v-if="errors.needle" class="p-error">{{ errors.needle }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right">{{ $t("renamer.pattern_help") }}</small>
						</div>

						<!-- Replacement -->
						<div class="flex flex-col">
							<FloatLabel variant="on">
								<InputText
									id="replacement"
									v-model="form.replacement"
									:class="{ 'p-invalid': errors.replacement }"
									class="w-full"
									required
								/>
								<label for="replacement" class="font-semibold"
									>{{ $t("renamer.replacement") }} <span class="text-red-500">*</span></label
								>
							</FloatLabel>
							<small v-if="errors.replacement" class="p-error">{{ errors.replacement }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right">{{ $t("renamer.replacement_help") }}</small>
						</div>

						<!-- Mode -->
						<div class="flex flex-col">
							<div class="flex items-center">
								<label for="mode" class="font-semibold w-full ltr:text-left rtl:text-right">
									{{ $t("renamer.mode") }} <span class="text-red-500">*</span>
								</label>
								<Select
									id="mode"
									v-model="form.mode"
									:options="modeOptions"
									option-label="label"
									option-value="value"
									:placeholder="$t('renamer.select_mode')"
									class="w-full border-none"
									:class="{ 'p-invalid': errors.mode }"
									required
								/>
							</div>
							<small v-if="errors.mode" class="p-error">{{ errors.mode }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right">
								<template v-if="form.mode === 'first'">{{ $t("renamer.mode_help_first") }}</template>
								<template v-else-if="form.mode === 'all'">{{ $t("renamer.mode_help_all") }}</template>
								<template v-else-if="form.mode === 'regex'"
									>{{ $t("renamer.mode_help_regex") }}
									<span class="text-xs pi pi-question-circle cursor-pointer" @click="showHelpRegex = !showHelpRegex"></span
								></template>
								<template v-else>{{ $t("renamer.mode_help_default") }}</template>
							</small>
						</div>
						<div
							v-if="showHelpRegex && form.mode === 'regex'"
							class="text-muted text-justify text-xs -mt-4 bg-surface-100 dark:bg-surface-900 rounded p-2"
							@click="showHelpRegex = false"
						>
							<span class="pi pi-question-circle ltr:mr-2 rtl:ml-2"></span>
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
								<InputNumber
									id="order"
									v-model="form.order"
									:class="{ 'p-invalid': errors.order }"
									input-class="text-right pr-10"
									:min="1"
									:max="999"
									:placeholder="$t('renamer.execution_order')"
									class="w-full"
									show-buttons
									required
								/>
							</div>
							<small v-if="errors.order" class="p-error">{{ errors.order }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right">{{ $t("renamer.order_help") }} </small>
						</div>

						<!-- Enabled -->
						<div class="flex flex-col">
							<div class="flex items-center">
								<ToggleSwitch id="is_enabled" v-model="form.is_enabled" :binary="true" input-id="is_enabled" />
								<label for="is_enabled" class="ltr:ml-2 rtl:mr-2"
									><span class="font-semibold">{{ $t("renamer.enabled") }}</span>
								</label>
							</div>
							<small class="text-muted-color ltr:text-left rtl:text-right">{{ $t("renamer.enabled_help") }}</small>
						</div>
					</div>
				</form>
			</div>
			<div class="flex">
				<Button
					:label="$t('renamer.cancel')"
					severity="secondary"
					class="w-full border-none rounded-none rounded-bl-xl font-bold"
					@click="onHide"
				/>
				<Button
					:label="isEdit ? $t('renamer.update') : $t('renamer.create')"
					class="w-full border-none rounded-none rounded-br-xl font-bold"
					:loading="isLoading"
					@click="save"
				/>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Textarea from "@/components/forms/basic/Textarea.vue";
import InputNumber from "primevue/inputnumber";
import RenamerService, { type CreateRenamerRuleRequest, type UpdateRenamerRuleRequest } from "@/services/renamer-service";
import InputText from "@/components/forms/basic/InputText.vue";
import FloatLabel from "primevue/floatlabel";
import Select from "primevue/select";
import ToggleSwitch from "primevue/toggleswitch";

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

const toast = useToast();
const isLoading = ref(false);
const showHelpRegex = ref(false);

const modeOptions = computed(() => [
	{ label: trans("renamer.mode_first"), value: "first", description: trans("renamer.mode_first_description") },
	{ label: trans("renamer.mode_all"), value: "all", description: trans("renamer.mode_all_description") },
	{ label: trans("renamer.mode_regex"), value: "regex", description: trans("renamer.mode_regex_description") },
]);

const form = ref<CreateRenamerRuleRequest>({
	rule: "",
	description: "",
	needle: "",
	replacement: "",
	mode: "all" as App.Enum.RenamerModeType,
	order: 1,
	is_enabled: true,
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
		};
	}
	errors.value = {};
}

function validateForm(): boolean {
	errors.value = {};

	if (!form.value.rule.trim()) {
		errors.value.rule = trans("renamer.rule_name_required");
	}

	if (!form.value.needle) {
		errors.value.needle = trans("renamer.pattern_required");
	}

	if (!form.value.replacement) {
		errors.value.replacement = trans("renamer.replacement_required");
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
.p-inputnumber-input {
	border: none;
}

.renamer-help-regex code {
	background-color: var(--p-surface-200);
	font-family: var(--font-mono);
	font-size: var(--text-2xs);
	padding: 2px 4px;
	border-radius: 4px;
}

.dark .renamer-help-regex code {
	background-color: var(--p-surface-800);
}
</style>
