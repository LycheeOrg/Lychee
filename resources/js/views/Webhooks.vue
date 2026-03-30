<template>
	<ConfirmDialog />

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
							<Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="confirmDelete(slotProps.data)" />
						</div>
					</template>
				</Column>
			</DataTable>
		</template>
	</Panel>

	<!-- Create / Edit modal -->
	<Dialog v-model:visible="showModal" pt:root:class="border-none" modal :dismissable-mask="true" @hide="onModalHide">
		<template #container>
			<div class="pt-9 px-9 w-full text-center font-bold text-xl">
				{{ editingWebhook ? $t("webhook.modal_edit_title") : $t("webhook.modal_create_title") }}
			</div>
			<div class="p-9 text-center text-muted-color w-2xl max-w-2xl text-wrap">
				<form @submit.prevent="saveWebhook">
					<div class="flex flex-col gap-6">
						<!-- Name -->
						<FloatLabel variant="on">
							<InputText id="wh_name" v-model="form.name" class="w-full" required />
							<label for="wh_name" class="font-semibold">{{ $t("webhook.field_name") }} <span class="text-red-500">*</span></label>
						</FloatLabel>

						<!-- Event -->
						<div class="flex items-center gap-4">
							<label class="font-semibold w-1/3 ltr:text-left rtl:text-right">
								{{ $t("webhook.field_event") }} <span class="text-red-500">*</span>
							</label>
							<Select
								v-model="form.event"
								:options="eventOptions"
								option-label="label"
								option-value="value"
								class="w-2/3 border-none"
								required
							/>
						</div>

						<!-- HTTP Method -->
						<div class="flex items-center gap-4">
							<label class="font-semibold w-1/3 ltr:text-left rtl:text-right">
								{{ $t("webhook.field_method") }} <span class="text-red-500">*</span>
							</label>
							<Select v-model="form.method" :options="methodOptions" class="w-2/3 border-none" required />
						</div>

						<!-- URL -->
						<FloatLabel variant="on">
							<InputText id="wh_url" v-model="form.url" class="w-full" type="url" required />
							<label for="wh_url" class="font-semibold">{{ $t("webhook.field_url") }} <span class="text-red-500">*</span></label>
						</FloatLabel>

						<!-- Payload Format -->
						<div class="flex items-center gap-4">
							<label class="font-semibold w-1/3 ltr:text-left rtl:text-right">
								{{ $t("webhook.field_format") }} <span class="text-red-500">*</span>
							</label>
							<Select
								v-model="form.payload_format"
								:options="formatOptions"
								option-label="label"
								option-value="value"
								class="w-2/3 border-none"
								required
							/>
						</div>

						<!-- Secret -->
						<FloatLabel variant="on">
							<InputText id="wh_secret" v-model="form.secret" class="w-full" type="password" autocomplete="new-password" />
							<label for="wh_secret" class="font-semibold">{{ $t("webhook.field_secret") }}</label>
						</FloatLabel>
						<small v-if="editingWebhook" class="text-muted-color -mt-4 ltr:text-left rtl:text-right">
							{{ $t("webhook.field_secret_placeholder") }}
						</small>

						<!-- Secret Header -->
						<FloatLabel variant="on">
							<InputText id="wh_secret_header" v-model="form.secret_header" class="w-full" />
							<label for="wh_secret_header" class="font-semibold">{{ $t("webhook.field_secret_header") }}</label>
						</FloatLabel>

						<!-- Payload toggles -->
						<div class="grid grid-cols-2 gap-3 ltr:text-left rtl:text-right">
							<div class="flex items-center gap-2">
								<ToggleSwitch v-model="form.send_photo_id" input-id="send_photo_id" />
								<label for="send_photo_id" class="font-semibold text-sm">{{ $t("webhook.field_send_photo_id") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch v-model="form.send_album_id" input-id="send_album_id" />
								<label for="send_album_id" class="font-semibold text-sm">{{ $t("webhook.field_send_album_id") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch v-model="form.send_title" input-id="send_title" />
								<label for="send_title" class="font-semibold text-sm">{{ $t("webhook.field_send_title") }}</label>
							</div>
							<div class="flex items-center gap-2">
								<ToggleSwitch v-model="form.send_size_variants" input-id="send_size_variants" />
								<label for="send_size_variants" class="font-semibold text-sm">{{ $t("webhook.field_send_size_variants") }}</label>
							</div>
						</div>

						<!-- Enabled -->
						<div class="flex items-center gap-2 ltr:text-left rtl:text-right">
							<ToggleSwitch v-model="form.enabled" input-id="wh_enabled" />
							<label for="wh_enabled" class="font-semibold">{{ $t("webhook.field_enabled") }}</label>
						</div>
					</div>
				</form>
			</div>
			<div class="flex">
				<Button
					:label="$t('webhook.cancel')"
					severity="secondary"
					class="w-full border-none rounded-none rounded-bl-xl font-bold"
					@click="onModalHide"
				/>
				<Button
					:label="editingWebhook ? $t('webhook.save') : $t('webhook.create')"
					class="w-full border-none rounded-none rounded-br-xl font-bold"
					:loading="isSaving"
					@click="saveWebhook"
				/>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import Panel from "primevue/panel";
import ProgressSpinner from "primevue/progressspinner";
import Select from "primevue/select";
import Tag from "primevue/tag";
import ToggleSwitch from "primevue/toggleswitch";
import Toolbar from "primevue/toolbar";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import InputText from "@/components/forms/basic/InputText.vue";
import WebhookService, { type CreateWebhookRequest } from "@/services/webhook-service";

const webhooks = ref<App.Http.Resources.Models.WebhookResource[]>([]);
const loading = ref(false);
const showModal = ref(false);
const isSaving = ref(false);
const editingWebhook = ref<App.Http.Resources.Models.WebhookResource | undefined>(undefined);

const defaultForm = (): CreateWebhookRequest => ({
	name: "",
	event: "photo.add",
	method: "POST",
	url: "",
	payload_format: "json",
	secret: "",
	secret_header: "",
	enabled: true,
	send_photo_id: true,
	send_album_id: true,
	send_title: true,
	send_size_variants: false,
	size_variant_types: null,
});

const form = ref<CreateWebhookRequest>(defaultForm());

const confirm = useConfirm();
const toast = useToast();

const eventOptions = [
	{ label: trans("webhook.event_photo_add"), value: "photo.add" as App.Enum.PhotoWebhookEvent },
	{ label: trans("webhook.event_photo_move"), value: "photo.move" as App.Enum.PhotoWebhookEvent },
	{ label: trans("webhook.event_photo_delete"), value: "photo.delete" as App.Enum.PhotoWebhookEvent },
];

const methodOptions: App.Enum.WebhookMethod[] = ["POST", "GET", "PUT", "PATCH", "DELETE"];

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
	form.value = defaultForm();
	showModal.value = true;
}

function openEditModal(webhook: App.Http.Resources.Models.WebhookResource): void {
	editingWebhook.value = webhook;
	form.value = {
		name: webhook.name,
		event: webhook.event,
		method: webhook.method,
		url: webhook.url,
		payload_format: webhook.payload_format,
		secret: "",
		secret_header: webhook.secret_header ?? "",
		enabled: webhook.enabled,
		send_photo_id: webhook.send_photo_id,
		send_album_id: webhook.send_album_id,
		send_title: webhook.send_title,
		send_size_variants: webhook.send_size_variants,
		size_variant_types: webhook.size_variant_types,
	};
	showModal.value = true;
}

function onModalHide(): void {
	showModal.value = false;
}

function saveWebhook(): void {
	isSaving.value = true;

	const savePromise = editingWebhook.value
		? WebhookService.update(editingWebhook.value.id, { ...form.value, webhook_id: editingWebhook.value.id })
		: WebhookService.create(form.value);

	savePromise
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans(editingWebhook.value ? "webhook.updated" : "webhook.created"),
				life: 3000,
			});
			showModal.value = false;
			load();
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("webhook.error_save"), life: 3000 });
		})
		.finally(() => {
			isSaving.value = false;
		});
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

function confirmDelete(webhook: App.Http.Resources.Models.WebhookResource): void {
	confirm.require({
		message: trans("webhook.confirm_delete_message", { name: webhook.name }),
		header: trans("webhook.confirm_delete_header"),
		icon: "pi pi-exclamation-triangle",
		rejectClass: "p-button-secondary p-button-outlined",
		rejectLabel: trans("webhook.cancel"),
		acceptLabel: trans("webhook.delete"),
		accept: () => {
			WebhookService.delete(webhook.id)
				.then(() => {
					toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("webhook.deleted"), life: 3000 });
					load();
					confirm.close();
				})
				.catch(() => {
					toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("webhook.error_delete"), life: 3000 });
					confirm.close();
				});
		},
	});
}

onMounted(() => {
	load();
});
</script>
