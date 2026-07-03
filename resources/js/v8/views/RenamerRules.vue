<template>
	<div class="h-svh overflow-y-auto">
		<!-- Header -->
		<div class="w-full border-0 h-14 flex items-center justify-between px-2">
			<OpenLeftMenu />
			<span class="absolute left-1/2 -translate-x-1/2 pointer-events-none">{{ $t("renamer.title") }}</span>
			<div></div>
		</div>

		<!-- Content -->
		<div v-if="rules !== undefined" class="text-highlighted max-w-5xl mx-auto p-4">
			<!-- Test Section - Always visible -->
			<UCard class="mb-16" :ui="{ header: 'hidden' }">
				<div class="rounded-lg">
					<div class="grid grid-cols-1 gap-4">
						<UFormField :label="$t('renamer.test_input_placeholder')">
							<UInput id="testInput" v-model="testInput" class="w-full" @input="debouncedTest" />
						</UFormField>
						<div class="grid grid-cols-2">
							<div class="flex gap-2 items-center">
								<USwitch v-model="is_photo" id="is_photo_toggle_test" />
								<label class="text-highlighted" for="is_photo_toggle_test">{{ $t("renamer.apply_photo_rules") }}</label>
							</div>
							<div class="flex gap-2 items-center">
								<USwitch v-model="is_album" id="is_album_toggle_test" />
								<label class="text-highlighted" for="is_album_toggle_test">{{ $t("renamer.apply_album_rules") }}</label>
							</div>
						</div>
						<UProgress v-if="isTestLoading" class="w-full" />
						<div v-if="testResult !== null" class="grid grid-cols-1 md:grid-cols-2 gap-4">
							<div>
								<label class="block text-sm font-medium text-muted mb-2">
									{{ $t("renamer.test_original") }}
								</label>
								<div class="p-3 bg-elevated rounded text-muted font-mono text-sm break-all">
									{{ testResult.original }}
								</div>
							</div>
							<div>
								<label class="block text-sm font-medium text-muted mb-2">
									{{ $t("renamer.test_result") }}
								</label>
								<div
									class="p-3 bg-elevated rounded text-muted font-mono text-sm break-all"
									:class="{
										'border bg-green-100 dark:bg-green-50/75 border-green-300 dark:border-green-300/75':
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
			</UCard>

			<div class="flex items-center justify-end w-full">
				<div class="flex items-center gap-4">
					<span class="text-sm text-muted">{{ $t("renamer.rules_count", { count: rules.length.toString() }) }}</span>
					<UButton v-if="rules.length > 0" icon="prime:plus" size="sm" :label="$t('renamer.create_rule')" @click="showCreateModal = true" />
				</div>
			</div>

			<div v-if="rules.length === 0" class="text-center py-8">
				<div class="text-muted mb-4">
					<UIcon name="prime:file-edit" class="text-4xl" />
				</div>
				<p class="text-muted mb-4">{{ $t("renamer.no_rules") }}</p>
				<UButton icon="prime:plus" :label="$t('renamer.create_first_rule')" @click="showCreateModal = true" />
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
		</div>
		<div v-else class="flex justify-center items-center p-4">
			<Spinner class="text-2xl" />
			<span class="ml-2">{{ $t("renamer.loading") }}</span>
		</div>

		<!-- Create/Edit Modal -->
		<RenamerRuleModal v-model:visible="showCreateModal" :rule="selectedRule" @saved="onRuleSaved" />
	</div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useConfirmDialog } from "@/v8/composables/useConfirmDialog";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import RenamerRuleLine from "@/v8/components/renamer/RenamerRuleLine.vue";
import RenamerRuleModal from "@/v8/components/renamer/RenamerRuleModal.vue";
import RenamerService, { type TestRenamerResponse } from "@/services/renamer-service";
import Spinner from "@/v8/components/Spinner.vue";

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

const { confirm } = useConfirmDialog();
const toast = useAppToast();

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
	confirm({
		title: trans("renamer.confirm_delete_header"),
		message: trans("renamer.confirm_delete_message", { rule: rule.rule }),
		acceptLabel: trans("renamer.delete"),
		rejectLabel: trans("renamer.cancel"),
		severity: "danger",
	}).then((confirmed) => {
		if (!confirmed) {
			return;
		}
		RenamerService.delete(rule.id)
			.then(() => {
				toast.add({
					severity: "success",
					summary: trans("renamer.success"),
					detail: trans("renamer.rule_deleted"),
					life: 3000,
				});
				loadRules();
			})
			.catch((error) => {
				console.error("Failed to delete renamer rule:", error);
				toast.add({
					severity: "error",
					summary: trans("renamer.error"),
					detail: trans("renamer.failed_to_delete"),
					life: 3000,
				});
			});
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
