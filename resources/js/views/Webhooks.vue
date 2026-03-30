<template>
	<WebhookDeleteDialog
		v-if="deletingWebhook !== undefined"
		v-model:visible="isDeleteDialogVisible"
		:webhook="deletingWebhook"
		@deleted="
			deletingWebhook = undefined;
			load();
		"
	/>

	<WebhookFormDialog v-model:visible="isFormDialogVisible" :webhook="editingWebhook" @saved="load" />

	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("webhook.title") }}
		</template>
	</Toolbar>

	<Panel class="border-0 max-w-5xl mx-auto mt-4">
		<p class="text-muted-color mb-6 text-center">{{ $t("webhook.description") }}</p>

		<!-- Loading -->
		<div v-if="loading" class="flex justify-center py-12">
			<ProgressSpinner />
		</div>

		<!-- Empty state -->
		<div v-else-if="webhooks.length === 0" class="text-center py-12">
			<div class="text-muted-color mb-4">
				<i class="pi pi-send text-4xl"></i>
			</div>
			<p class="text-muted-color mb-4">{{ $t("webhook.no_webhooks") }}</p>
			<Button icon="pi pi-plus" class="border-none" :label="$t('webhook.create_first')" @click="openCreateModal" />
		</div>
		<template v-else>
			<div class="flex mb-4 justify-end">
				<Button icon="pi pi-plus" class="border-none" size="small" :label="$t('webhook.create')" @click="openCreateModal" />
			</div>

			<!-- Webhooks table -->
			<DataTable :value="webhooks" class="w-full">
				<Column :header="$t('webhook.col_name')">
					<template #body="slotProps">
						<span class="font-medium">{{ slotProps.data.name }}</span>
					</template>
				</Column>
				<Column :header="$t('webhook.col_event')">
					<template #body="slotProps">
						<Tag :value="eventLabel(slotProps.data.event)" severity="secondary" />
					</template>
				</Column>
				<Column :header="$t('webhook.col_method')">
					<template #body="slotProps">
						<code class="text-xs bg-surface-100 dark:bg-surface-800 px-1.5 py-0.5 rounded">{{ slotProps.data.method }}</code>
					</template>
				</Column>
				<Column :header="$t('webhook.col_url')">
					<template #body="slotProps">
						<span class="text-muted-color text-sm truncate max-w-xs block" :title="slotProps.data.url">{{ slotProps.data.url }}</span>
					</template>
				</Column>
				<Column :header="$t('webhook.col_format')">
					<template #body="slotProps">
						<span class="text-muted-color text-sm">{{ formatLabel(slotProps.data.payload_format) }}</span>
					</template>
				</Column>
				<Column :header="$t('webhook.col_enabled')" header-class="text-center">
					<template #body="slotProps">
						<div class="flex justify-center">
							<ToggleSwitch :model-value="slotProps.data.enabled" @change="toggleEnabled(slotProps.data)" />
						</div>
					</template>
				</Column>
				<Column :header="$t('webhook.col_actions')" header-class="text-center">
					<template #body="slotProps">
						<div class="flex justify-center gap-2">
							<Button icon="pi pi-pencil" severity="secondary" text rounded size="small" @click="openEditModal(slotProps.data)" />
							<Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="openDeleteModal(slotProps.data)" />
						</div>
					</template>
				</Column>
			</DataTable>
		</template>
	</Panel>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import ProgressSpinner from "primevue/progressspinner";
import Tag from "primevue/tag";
import ToggleSwitch from "primevue/toggleswitch";
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import WebhookDeleteDialog from "@/components/forms/webhooks/WebhookDeleteDialog.vue";
import WebhookFormDialog from "@/components/forms/webhooks/WebhookFormDialog.vue";
import WebhookService from "@/services/webhook-service";

const webhooks = ref<App.Http.Resources.Models.WebhookResource[]>([]);
const loading = ref(false);
const editingWebhook = ref<App.Http.Resources.Models.WebhookResource | undefined>(undefined);
const deletingWebhook = ref<App.Http.Resources.Models.WebhookResource | undefined>(undefined);
const isFormDialogVisible = ref(false);
const isDeleteDialogVisible = ref(false);

const toast = useToast();

const eventOptions = [
	{ label: trans("webhook.event_photo_add"), value: "photo.add" as App.Enum.PhotoWebhookEvent },
	{ label: trans("webhook.event_photo_move"), value: "photo.move" as App.Enum.PhotoWebhookEvent },
	{ label: trans("webhook.event_photo_delete"), value: "photo.delete" as App.Enum.PhotoWebhookEvent },
];

const formatOptions = [
	{ label: trans("webhook.format_json"), value: "json" as App.Enum.WebhookPayloadFormat },
	{ label: trans("webhook.format_query_string"), value: "query_string" as App.Enum.WebhookPayloadFormat },
];

function eventLabel(event: App.Enum.PhotoWebhookEvent): string {
	return eventOptions.find((o) => o.value === event)?.label ?? event;
}

function formatLabel(format: App.Enum.WebhookPayloadFormat): string {
	return formatOptions.find((o) => o.value === format)?.label ?? format;
}

function load(): void {
	loading.value = true;
	WebhookService.list()
		.then((response) => {
			webhooks.value = response.data.webhooks;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("webhook.error_load"), life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

function openCreateModal(): void {
	editingWebhook.value = undefined;
	isFormDialogVisible.value = true;
}

function openEditModal(webhook: App.Http.Resources.Models.WebhookResource): void {
	editingWebhook.value = webhook;
	isFormDialogVisible.value = true;
}

function toggleEnabled(webhook: App.Http.Resources.Models.WebhookResource): void {
	WebhookService.patch(webhook.id, { webhook_id: webhook.id, enabled: !webhook.enabled })
		.then((response) => {
			webhook.enabled = response.data.enabled;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("webhook.error_save"), life: 3000 });
		});
}

function openDeleteModal(webhook: App.Http.Resources.Models.WebhookResource): void {
	deletingWebhook.value = webhook;
	isDeleteDialogVisible.value = true;
}

onMounted(() => {
	load();
});
</script>
