<template>
	<div class="h-svh overflow-y-auto">
		<!-- Header -->
		<Toolbar class="w-full border-0 h-14">
			<template #start>
				<OpenLeftMenu />
			</template>
			<template #center> Renamer Rules </template>

			<template #end>
				<div class="flex items-center space-x-2">
					<Button icon="pi pi-plus" label="Create Rule" @click="showCreateModal = true" />
				</div>
			</template>
		</Toolbar>

		<!-- Content -->
		<Panel v-if="rules !== undefined" class="text-center border-0 text-muted-color-emphasis max-w-5xl mx-auto">
			<template #header>
				<div class="flex items-center justify-end w-full">
					<div class="flex items-center space-x-2">
						<span class="text-sm text-muted-color">{{ rules.length }} rules</span>
					</div>
				</div>
			</template>

			<div v-if="rules.length === 0" class="text-center py-8">
				<div class="text-muted-color mb-4">
					<i class="pi pi-file-edit text-4xl"></i>
				</div>
				<p class="text-muted-color mb-4">No renamer rules found</p>
				<Button icon="pi pi-plus" class="border-none" label="Create your first rule" @click="showCreateModal = true" />
			</div>

			<div v-else>
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
			<span class="ml-2">Loading renamer rules...</span>
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
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import ProgressSpinner from "primevue/progressspinner";
import ConfirmDialog from "primevue/confirmdialog";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import RenamerRuleLine from "@/components/renamer/RenamerRuleLine.vue";
import RenamerRuleModal from "@/components/renamer/RenamerRuleModal.vue";
import RenamerService from "@/services/renamer-service";

const rules = ref<App.Http.Resources.Models.RenamerRuleResource[] | undefined>(undefined);
const showCreateModal = ref(false);
const selectedRule = ref<App.Http.Resources.Models.RenamerRuleResource | undefined>(undefined);

const confirm = useConfirm();
const toast = useToast();

function loadRules() {
	RenamerService.list()
		.then((response) => {
			rules.value = response.data;
		})
		.catch((error) => {
			console.error("Failed to load renamer rules:", error);
			toast.add({
				severity: "error",
				summary: "Error",
				detail: "Failed to load renamer rules",
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
		message: `Are you sure you want to delete the rule "${rule.rule}"?`,
		header: "Confirm Deletion",
		icon: "pi pi-exclamation-triangle",
		rejectClass: "p-button-secondary p-button-outlined",
		rejectLabel: "Cancel",
		acceptLabel: "Delete",
		accept: () => {
			RenamerService.delete(rule.id)
				.then(() => {
					toast.add({
						severity: "success",
						summary: "Success",
						detail: "Renamer rule deleted successfully",
						life: 3000,
					});
					loadRules();
				})
				.catch((error) => {
					console.error("Failed to delete renamer rule:", error);
					toast.add({
						severity: "error",
						summary: "Error",
						detail: "Failed to delete renamer rule",
						life: 3000,
					});
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
