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
			<Spinner class="text-3xl" />
		</div>

		<!-- Empty state -->
		<div v-else-if="webhooks.length === 0" class="text-center py-12">
			<div class="text-muted mb-4">
				<UIcon name="lucide:send" class="text-4xl" />
			</div>
			<p class="text-muted mb-4">{{ $t("webhook.no_webhooks") }}</p>
			<UButton icon="lucide:plus" :label="$t('webhook.create_first')" @click="openCreateModal" />
		</div>
		<template v-else>
			<div class="flex mb-4 justify-end">
				<UButton icon="lucide:plus" size="sm" :label="$t('webhook.create')" @click="openCreateModal" />
			</div>

			<!-- Webhooks table -->
			<UTable :data="webhooks" :columns="columns" class="w-full" />
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { h, onMounted, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import WebhookDeleteDialog from "@/v8/components/forms/webhooks/WebhookDeleteDialog.vue";
import WebhookFormDialog from "@/v8/components/forms/webhooks/WebhookFormDialog.vue";
import WebhookService from "@/services/webhook-service";
import Spinner from "@/v8/components/Spinner.vue";
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
	{
		accessorKey: "name",
		header: trans("webhook.col_name"),
		cell: ({ row }) => h("span", { class: "font-medium" }, row.original.name),
	},
	{
		id: "event",
		header: trans("webhook.col_event"),
		cell: ({ row }) => h(UBadge, { color: "neutral" }, () => eventLabel(row.original.event)),
	},
	{
		accessorKey: "method",
		header: trans("webhook.col_method"),
		cell: ({ row }) => h("code", { class: "text-xs bg-elevated px-1.5 py-0.5 rounded" }, row.original.method),
	},
	{
		accessorKey: "url",
		header: trans("webhook.col_url"),
		cell: ({ row }) => h("span", { class: "text-muted text-sm truncate max-w-xs block", title: row.original.url }, row.original.url),
	},
	{
		id: "format",
		header: trans("webhook.col_format"),
		cell: ({ row }) => h("span", { class: "text-muted text-sm" }, formatLabel(row.original.payload_format)),
	},
	{
		id: "enabled",
		header: trans("webhook.col_enabled"),
		cell: ({ row }) =>
			h("div", { class: "flex justify-center" }, [
				h(USwitch, { modelValue: row.original.enabled, "onUpdate:modelValue": () => toggleEnabled(row.original) }),
			]),
	},
	{
		id: "actions",
		header: trans("webhook.col_actions"),
		cell: ({ row }) =>
			h("div", { class: "flex justify-center gap-2" }, [
				h(UButton, { icon: "lucide:pencil", color: "neutral", variant: "ghost", size: "sm", onClick: () => openEditModal(row.original) }),
				h(UButton, { icon: "lucide:trash", color: "error", variant: "ghost", size: "sm", onClick: () => openDeleteModal(row.original) }),
			]),
	},
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
