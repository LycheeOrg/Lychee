<template>
	<WebhookDeleteDialog
		v-if="deletingWebhook !== undefined"
		v-model:open="isDeleteDialogVisible"
		:webhook="deletingWebhook"
		@deleted="
			deletingWebhook = undefined;
			load();
		"
	/>

	<WebhookFormDialog v-model:open="isFormDialogVisible" :webhook="editingWebhook" @saved="load" />

	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("webhook.title") }}
	</UHeader>

	<UCard class="max-w-5xl mx-auto mt-4">
		<p class="text-muted mb-6 text-center">{{ $t("webhook.description") }}</p>

		<!-- Loading -->
		<div v-if="loading" class="flex justify-center py-12">
			<LycheeLoadingIcon fast class="text-3xl" />
		</div>

		<!-- Empty state -->
		<div v-else-if="webhooks.length === 0" class="text-center py-12">
			<div class="text-muted mb-4">
				<UIcon name="lucide:send" class="text-4xl" />
			</div>
			<p class="text-muted mb-4">{{ $t("webhook.no_webhooks") }}</p>
			<UButton color="primary" variant="solid" icon="lucide:plus" :label="$t('webhook.create_first')" @click="openCreateModal" />
		</div>
		<template v-else>
			<div class="flex mb-4 justify-end">
				<UButton color="primary" variant="solid" icon="lucide:plus" size="sm" :label="$t('webhook.create')" @click="openCreateModal" />
			</div>

			<!-- Webhooks table -->
			<UTable :data="webhooks" :columns="columns" class="w-full">
				<template #name-cell="{ row }">
					<span class="font-medium">{{ row.original.name }}</span>
				</template>
				<template #event-cell="{ row }">
					<UBadge color="neutral">{{ eventLabel(row.original.event) }}</UBadge>
				</template>
				<template #method-cell="{ row }">
					<code class="text-xs bg-elevated px-1.5 py-0.5 rounded">{{ row.original.method }}</code>
				</template>
				<template #url-cell="{ row }">
					<span class="text-muted text-sm truncate max-w-xs block" :title="row.original.url">{{ row.original.url }}</span>
				</template>
				<template #format-cell="{ row }">
					<span class="text-muted text-sm">{{ formatLabel(row.original.payload_format) }}</span>
				</template>
				<template #enabled-cell="{ row }">
					<div class="flex justify-center">
						<USwitch :model-value="row.original.enabled" @update:model-value="() => toggleEnabled(row.original)" />
					</div>
				</template>
				<template #actions-cell="{ row }">
					<div class="flex justify-center gap-2">
						<UButton icon="lucide:pencil" color="neutral" variant="ghost" size="sm" @click="openEditModal(row.original)" />
						<UButton icon="lucide:trash" color="error" variant="ghost" size="sm" @click="openDeleteModal(row.original)" />
					</div>
				</template>
			</UTable>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import WebhookDeleteDialog from "@/v8/components/forms/webhooks/WebhookDeleteDialog.vue";
import WebhookFormDialog from "@/v8/components/forms/webhooks/WebhookFormDialog.vue";
import WebhookService from "@/services/webhook-service";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import UButton from "@nuxt/ui/components/Button.vue";
import USwitch from "@nuxt/ui/components/Switch.vue";
import UBadge from "@nuxt/ui/components/Badge.vue";
import type { TableColumn } from "@nuxt/ui";

type Webhook = App.Http.Resources.Models.WebhookResource;

const webhooks = ref<Webhook[]>([]);
const loading = ref(false);
const editingWebhook = ref<Webhook | undefined>(undefined);
const deletingWebhook = ref<Webhook | undefined>(undefined);
const isFormDialogVisible = ref(false);
const isDeleteDialogVisible = ref(false);

const toast = useAppToast();

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

const columns: TableColumn<Webhook>[] = [
	{ accessorKey: "name", header: trans("webhook.col_name") },
	{ id: "event", header: trans("webhook.col_event") },
	{ accessorKey: "method", header: trans("webhook.col_method") },
	{ accessorKey: "url", header: trans("webhook.col_url") },
	{ id: "format", header: trans("webhook.col_format") },
	{ id: "enabled", header: trans("webhook.col_enabled") },
	{ id: "actions", header: trans("webhook.col_actions") },
];

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

function openEditModal(webhook: Webhook): void {
	editingWebhook.value = webhook;
	isFormDialogVisible.value = true;
}

function toggleEnabled(webhook: Webhook): void {
	WebhookService.patch(webhook.id, { webhook_id: webhook.id, enabled: !webhook.enabled })
		.then((response) => {
			webhook.enabled = response.data.enabled;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("webhook.error_save"), life: 3000 });
		});
}

function openDeleteModal(webhook: Webhook): void {
	deletingWebhook.value = webhook;
	isDeleteDialogVisible.value = true;
}

onMounted(() => {
	load();
});
</script>
