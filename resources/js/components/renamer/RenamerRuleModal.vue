<template>
	<Dialog v-model:visible="dialogVisible" pt:root:class="border-none" modal :dismissable-mask="true" @hide="onHide">
		<template #container>
			<div class="pt-9 px-9 w-full text-center font-bold text-xl">
				{{ isEdit ? "Edit Renamer Rule" : "Create Renamer Rule" }}
			</div>
			<div class="p-9 text-center text-muted-color w-xl max-w-2xl text-wrap">
				<form @submit.prevent="save">
					<div class="flex flex-col gap-8">
						<!-- Rule Name -->
						<FloatLabel variant="on">
							<InputText id="rule" v-model="form.rule" :class="{ 'p-invalid': errors.rule }" class="w-full" required />
							<label for="rule" class="font-semibold">Rule Name <span class="text-red-500">*</span></label>
							<small v-if="errors.rule" class="p-error">{{ errors.rule }}</small>
						</FloatLabel>

						<!-- Description -->
						<FloatLabel variant="on">
							<label for="description" class="font-semibold">Description</label>
							<Textarea
								id="description"
								v-model="form.description"
								placeholder="Optional description of what this rule does"
								:rows="2"
								class="w-full"
							/>
						</FloatLabel>

						<!-- Needle (Pattern) -->
						<div class="flex flex-col">
							<FloatLabel variant="on">
								<InputText id="needle" v-model="form.needle" :class="{ 'p-invalid': errors.needle }" class="w-full" required />
								<label for="needle" class="font-semibold">Pattern <span class="text-red-500">*</span></label>
							</FloatLabel>
							<small v-if="errors.needle" class="p-error">{{ errors.needle }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right">Pattern to match (e.g., IMG_, DSC_)</small>
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
								<label for="replacement" class="font-semibold">Replacement <span class="text-red-500">*</span></label>
							</FloatLabel>
							<small v-if="errors.replacement" class="p-error">{{ errors.replacement }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right">Replacement text (e.g., Photo_, Camera_)</small>
						</div>

						<!-- Mode -->
						<div class="flex flex-col">
							<div class="flex items-center">
								<label for="mode" class="font-semibold w-full ltr:text-left rtl:text-right">
									Mode <span class="text-red-500">*</span>
								</label>
								<Select
									id="mode"
									v-model="form.mode"
									:options="modeOptions"
									option-label="label"
									option-value="value"
									placeholder="Select renaming mode"
									class="w-full border-none"
									:class="{ 'p-invalid': errors.mode }"
									required
								/>
							</div>
							<small v-if="errors.mode" class="p-error">{{ errors.mode }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right">
								<template v-if="form.mode === 'first'">Replace only the first occurrence</template>
								<template v-else-if="form.mode === 'all'">Replace all occurrences</template>
								<template v-else-if="form.mode === 'regex'">Use regular expression matching</template>
								<template v-else>Choose how the pattern matching should work</template>
							</small>
						</div>
						<!-- Order -->
						<div class="flex flex-col">
							<div class="flex items-center">
								<label for="order" class="font-semibold w-full ltr:text-left rtl:text-right"
									>Order <span class="text-red-500">*</span></label
								>
								<InputNumber
									id="order"
									v-model="form.order"
									:class="{ 'p-invalid': errors.order }"
									input-class="text-right pr-10"
									:min="1"
									:max="999"
									placeholder="Execution order"
									class="w-full"
									show-buttons
									required
								/>
							</div>
							<small v-if="errors.order" class="p-error">{{ errors.order }}</small>
							<small class="text-muted-color ltr:text-left rtl:text-right"
								>Lower numbers are processed first (1 = highest priority)
							</small>
						</div>

						<!-- Enabled -->
						<div class="flex flex-col">
							<div class="flex items-center">
								<Checkbox id="is_enabled" v-model="form.is_enabled" :binary="true" />
								<label for="is_enabled" class="ltr:ml-2 rtl:mr-2"><span class="font-semibold">Enabled</span> </label>
							</div>
							<small class="text-muted-color ltr:text-left rtl:text-right">(Only enabled rules will be applied during renaming)</small>
						</div>
					</div>
				</form>
			</div>
			<div class="flex">
				<Button label="Cancel" severity="secondary" class="w-full border-none rounded-none rounded-bl-xl font-bold" @click="onHide" />
				<Button
					:label="isEdit ? 'Update' : 'Create'"
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
import Dialog from "primevue/dialog";
import Button from "primevue/button";
import Textarea from "@/components/forms/basic/Textarea.vue";
import InputNumber from "primevue/inputnumber";
import Checkbox from "primevue/checkbox";
import RenamerService, { type CreateRenamerRuleRequest, type UpdateRenamerRuleRequest } from "@/services/renamer-service";
import InputText from "@/components/forms/basic/InputText.vue";
import FloatLabel from "primevue/floatlabel";
import Select from "primevue/select";

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

	if (!form.value.needle) {
		errors.value.needle = "Pattern is required";
	}

	if (!form.value.replacement) {
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
<style>
.p-inputnumber-input {
	border: none;
}
</style>
