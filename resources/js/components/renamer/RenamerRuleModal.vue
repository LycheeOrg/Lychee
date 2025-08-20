<template>
	<Dialog v-model:visible="dialogVisible" :modal="true" :closable="true" :style="{ width: '600px' }" class="p-dialog-maximized-md" @hide="onHide">
		<template #header>
			<span class="font-bold text-xl">{{ isEdit ? "Edit Renamer Rule" : "Create Renamer Rule" }}</span>
		</template>

		<form @submit.prevent="save">
			<div class="space-y-4">
				<!-- Rule Name -->
				<div class="field">
					<label for="rule" class="font-semibold">Rule Name <span class="text-red-500">*</span></label>
					<InputText
						id="rule"
						v-model="form.rule"
						:class="{ 'p-invalid': errors.rule }"
						placeholder="Enter rule name"
						class="w-full"
						required
					/>
					<small v-if="errors.rule" class="p-error">{{ errors.rule }}</small>
				</div>

				<!-- Description -->
				<div class="field">
					<label for="description" class="font-semibold">Description</label>
					<Textarea
						id="description"
						v-model="form.description"
						placeholder="Optional description of what this rule does"
						:rows="2"
						class="w-full"
					/>
				</div>

				<!-- Needle (Pattern) -->
				<div class="field">
					<label for="needle" class="font-semibold">Pattern <span class="text-red-500">*</span></label>
					<InputText
						id="needle"
						v-model="form.needle"
						:class="{ 'p-invalid': errors.needle }"
						placeholder="Pattern to match (e.g., IMG_, DSC_)"
						class="w-full"
						required
					/>
					<small v-if="errors.needle" class="p-error">{{ errors.needle }}</small>
					<small class="text-muted-color"> The text pattern to search for in filenames </small>
				</div>

				<!-- Replacement -->
				<div class="field">
					<label for="replacement" class="font-semibold">Replacement <span class="text-red-500">*</span></label>
					<InputText
						id="replacement"
						v-model="form.replacement"
						:class="{ 'p-invalid': errors.replacement }"
						placeholder="Replacement text (e.g., Photo_, Camera_)"
						class="w-full"
						required
					/>
					<small v-if="errors.replacement" class="p-error">{{ errors.replacement }}</small>
					<small class="text-muted-color"> The text to replace the pattern with </small>
				</div>

				<!-- Mode -->
				<div class="field">
					<label for="mode" class="font-semibold">Mode <span class="text-red-500">*</span></label>
					<Dropdown
						id="mode"
						v-model="form.mode"
						:options="modeOptions"
						option-label="label"
						option-value="value"
						placeholder="Select renaming mode"
						class="w-full"
						:class="{ 'p-invalid': errors.mode }"
						required
					/>
					<small v-if="errors.mode" class="p-error">{{ errors.mode }}</small>
					<small class="text-muted-color">
						<template v-if="form.mode === 'first'">Replace only the first occurrence</template>
						<template v-else-if="form.mode === 'all'">Replace all occurrences</template>
						<template v-else-if="form.mode === 'regex'">Use regular expression matching</template>
						<template v-else>Choose how the pattern matching should work</template>
					</small>
				</div>

				<!-- Order -->
				<div class="field">
					<label for="order" class="font-semibold">Order <span class="text-red-500">*</span></label>
					<InputNumber
						id="order"
						v-model="form.order"
						:class="{ 'p-invalid': errors.order }"
						:min="1"
						:max="999"
						placeholder="Execution order"
						class="w-full"
						show-buttons
						required
					/>
					<small v-if="errors.order" class="p-error">{{ errors.order }}</small>
					<small class="text-muted-color"> Lower numbers are processed first (1 = highest priority) </small>
				</div>

				<!-- Enabled -->
				<div class="field">
					<div class="flex items-center">
						<Checkbox id="is_enabled" v-model="form.is_enabled" :binary="true" />
						<label for="is_enabled" class="ml-2 font-semibold">Enabled</label>
					</div>
					<small class="text-muted-color"> Only enabled rules will be applied during renaming </small>
				</div>
			</div>
		</form>

		<template #footer>
			<div class="flex justify-end space-x-2">
				<Button label="Cancel" severity="secondary" outlined @click="onHide" />
				<Button :label="isEdit ? 'Update' : 'Create'" :loading="isLoading" @click="save" />
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import Textarea from "@/components/forms/basic/Textarea.vue";
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import Checkbox from "primevue/checkbox";
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

const toast = useToast();
const isLoading = ref(false);

const modeOptions = [
	{ label: "First occurrence", value: "first", description: "Replace only the first match" },
	{ label: "All occurrences", value: "all", description: "Replace all matches" },
	{ label: "Regular expression", value: "regex", description: "Use regex pattern matching" },
];

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
		errors.value.rule = "Rule name is required";
	}

	if (!form.value.needle.trim()) {
		errors.value.needle = "Pattern is required";
	}

	if (!form.value.replacement.trim()) {
		errors.value.replacement = "Replacement is required";
	}

	if (!form.value.mode) {
		errors.value.mode = "Mode is required";
	}

	if (!form.value.order || form.value.order < 1) {
		errors.value.order = "Order must be a positive number";
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
				renamer_rule_id: props.rule!.id,
			} as UpdateRenamerRuleRequest)
		: RenamerService.create(form.value);

	savePromise
		.then(() => {
			toast.add({
				severity: "success",
				summary: "Success",
				detail: `Renamer rule ${isEdit.value ? "updated" : "created"} successfully`,
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
					summary: "Error",
					detail: `Failed to ${isEdit.value ? "update" : "create"} renamer rule`,
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
