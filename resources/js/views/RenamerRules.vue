<template>
	<div class="h-svh overflow-y-auto">
		<!-- Header -->
		<Toolbar class="w-full border-0 h-14 rounded-none">
			<template #start>
				<OpenLeftMenu />
			</template>
			<template #center> {{ $t("renamer.title") }} </template>

			<template #end> </template>
		</Toolbar>

		<!-- Content -->
		<Panel v-if="rules !== undefined" class="border-0 text-muted-color-emphasis max-w-5xl mx-auto">
			<!-- Test Section - Always visible -->
			<Card class="bg-surface-100 dark:bg-surface-100/5 rounded-2xl mb-16" :pt:header:class="'hidden'">
				<template #content>
					<div class="p-4 rounded-lg">
						<div class="grid grid-cols-1 gap-4">
							<FloatLabel variant="on">
								<InputText id="testInput" v-model="testInput" class="w-full" @input="debouncedTest" />
								<label for="testInput" class="">
									{{ $t("renamer.test_input_placeholder") }}
								</label>
							</FloatLabel>
							<div class="grid grid-cols-2">
								<div class="flex gap-2 items-center">
									<ToggleSwitch v-model="is_photo" input-id="is_photo_toggle_test" />
									<label class="text-muted-color-emphasis" for="is_photo_toggle_test">{{ "Apply photo rules" }}</label>
								</div>
								<div class="flex gap-2 items-center">
									<ToggleSwitch v-model="is_album" input-id="is_album_toggle_test" />
									<label class="text-muted-color-emphasis" for="is_album_toggle_test">{{ "Apply album rules" }}</label>
								</div>
							</div>
							<ProgressBar v-if="isTestLoading" mode="indeterminate" class="w-full" />
							<div v-if="testResult !== null" class="grid grid-cols-1 md:grid-cols-2 gap-4">
								<div>
									<label class="block text-sm font-medium text-muted-color mb-2">
										{{ $t("renamer.test_original") }}
									</label>
									<div class="p-3 bg-surface-200 dark:bg-surface-100/5 rounded text-muted-color font-mono text-sm break-all">
										{{ testResult.original }}
									</div>
								</div>
								<div>
									<label class="block text-sm font-medium text-muted-color mb-2">
										{{ $t("renamer.test_result") }}
									</label>
									<div
										class="p-3 bg-surface-200 dark:bg-surface-100/5 rounded text-muted-color font-mono text-sm break-all"
										:class="{
											' border bg-green-100 dark:bg-green-50/75 border-green-300 dark:border-green-300/75':
												testResult.result !== testResult.original,
										}"
									>
										{{ testResult.result }}
									</div>
								</div>
							</div>
							<div v-if="testError" class="text-red-600 text-sm">
								{{ testError }}
							</div>
						</div>
					</div>
				</template>
			</Card>

			<div class="flex items-center justify-end w-full">
				<div class="flex items-center gap-4">
					<span class="text-sm text-muted-color">{{ $t("renamer.rules_count", { count: rules.length.toString() }) }}</span>
					<Button
						v-if="rules.length > 0"
						icon="pi pi-plus"
						class="border-none"
						size="small"
						:label="$t('renamer.create_rule')"
						@click="showCreateModal = true"
					/>
				</div>
			</div>

			<div v-if="rules.length === 0" class="text-center py-8">
				<div class="text-muted-color mb-4">
					<i class="pi pi-file-edit text-4xl"></i>
				</div>
				<p class="text-muted-color mb-4">{{ $t("renamer.no_rules") }}</p>
				<Button icon="pi pi-plus" class="border-none" :label="$t('renamer.create_first_rule')" @click="showCreateModal = true" />
			</div>

			<div v-if="rules.length > 0">
				<RenamerRuleLine
					v-for="rule in rules"
					:key="rule.id"
					:rule="rule"
					:can-edit="true"
					:can-delete="true"
					@edit="editRule(rule)"
					@delete="deleteRule(rule)"
				/>
			</div>
		</Panel>
		<div v-else class="flex justify-center items-center p-4">
			<ProgressSpinner />
			<span class="ml-2">{{ $t("renamer.loading") }}</span>
		</div>

		<!-- Create/Edit Modal -->
		<RenamerRuleModal v-model:visible="showCreateModal" :rule="selectedRule" @saved="onRuleSaved" />

		<!-- Delete Confirmation -->
		<ConfirmDialog />
	</div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import ConfirmDialog from "primevue/confirmdialog";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import RenamerRuleLine from "@/components/renamer/RenamerRuleLine.vue";
import RenamerRuleModal from "@/components/renamer/RenamerRuleModal.vue";
import RenamerService, { type TestRenamerResponse } from "@/services/renamer-service";
import InputText from "@/components/forms/basic/InputText.vue";
import Card from "primevue/card";
import FloatLabel from "primevue/floatlabel";
import ProgressBar from "primevue/progressbar";
import ToggleSwitch from "primevue/toggleswitch";

const rules = ref<App.Http.Resources.Models.RenamerRuleResource[] | undefined>(undefined);
const showCreateModal = ref(false);
const selectedRule = ref<App.Http.Resources.Models.RenamerRuleResource | undefined>(undefined);

// Test functionality
const testInput = ref("");
const testResult = ref<TestRenamerResponse | null>(null);
const isTestLoading = ref(false);
const is_photo = ref(true);
const is_album = ref(true);
const testError = ref<string | null>(null);
let testTimeout: NodeJS.Timeout | null = null;

const confirm = useConfirm();
const toast = useToast();

// Debounced test function
function debouncedTest() {
	if (testTimeout !== null) {
		clearTimeout(testTimeout);
	}

	testTimeout = setTimeout(() => {
		if (testInput.value.trim() !== "") {
			performTest();
		} else {
			testResult.value = null;
			testError.value = null;
		}
	}, 500);
}

function performTest() {
	if (testInput.value.trim() === "") {
		return;
	}

	isTestLoading.value = true;
	testError.value = null;

	RenamerService.test({ candidate: testInput.value, is_photo: is_photo.value, is_album: is_album.value })
		.then((response) => {
			testResult.value = response.data;
		})
		.catch((error) => {
			console.error("Failed to test renamer rules:", error);
			testError.value = trans("renamer.test_failed");
			testResult.value = null;
		})
		.finally(() => {
			isTestLoading.value = false;
		});
}

function loadRules() {
	RenamerService.list()
		.then((response) => {
			rules.value = response.data;
		})
		.catch((error) => {
			console.error("Failed to load renamer rules:", error);
			toast.add({
				severity: "error",
				summary: trans("renamer.error"),
				detail: trans("renamer.failed_to_load"),
				life: 3000,
			});
		});
}

function editRule(rule: App.Http.Resources.Models.RenamerRuleResource) {
	selectedRule.value = rule;
	showCreateModal.value = true;
}

function deleteRule(rule: App.Http.Resources.Models.RenamerRuleResource) {
	confirm.require({
		message: trans("renamer.confirm_delete_message", { rule: rule.rule }),
		header: trans("renamer.confirm_delete_header"),
		icon: "pi pi-exclamation-triangle",
		rejectClass: "p-button-secondary p-button-outlined",
		rejectLabel: trans("renamer.cancel"),
		acceptLabel: trans("renamer.delete"),
		accept: () => {
			RenamerService.delete(rule.id)
				.then(() => {
					toast.add({
						severity: "success",
						summary: trans("renamer.success"),
						detail: trans("renamer.rule_deleted"),
						life: 3000,
					});
					loadRules();
					confirm.close();
				})
				.catch((error) => {
					console.error("Failed to delete renamer rule:", error);
					toast.add({
						severity: "error",
						summary: trans("renamer.error"),
						detail: trans("renamer.failed_to_delete"),
						life: 3000,
					});
					confirm.close();
				});
		},
	});
}

function onRuleSaved() {
	showCreateModal.value = false;
	selectedRule.value = undefined;
	loadRules();
}

onMounted(() => {
	loadRules();
});
</script>
