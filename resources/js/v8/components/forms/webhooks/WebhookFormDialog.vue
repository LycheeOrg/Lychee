<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #header>
			<span class="font-bold text-xl">{{ webhook ? $t("webhook.modal_edit_title") : $t("webhook.modal_create_title") }}</span>
		</template>
		<template #body>
			<form @submit.prevent="save">
				<div class="flex flex-col gap-6">
					<!-- Name -->
					<UFormField :label="$t('webhook.field_name')" required>
						<UInput id="wh_name" v-model="form.name" class="w-full" required />
					</UFormField>

					<!-- Event -->
					<div class="flex items-center gap-4">
						<label class="font-semibold w-1/3 ltr:text-left rtl:text-right">
							{{ $t("webhook.field_event") }} <span class="text-red-500">*</span>
						</label>
						<USelectMenu v-model="selectedEvent" :items="eventOptions" label-key="label" class="w-2/3">
							<template #item-label="{ item }">{{ item.label }}</template>
						</USelectMenu>
					</div>

					<!-- HTTP Method -->
					<div class="flex items-center gap-4">
						<label class="font-semibold w-1/3 ltr:text-left rtl:text-right">
							{{ $t("webhook.field_method") }} <span class="text-red-500">*</span>
						</label>
						<USelectMenu v-model="form.method" :items="methodOptions" class="w-2/3" />
					</div>

					<!-- URL -->
					<UFormField :label="$t('webhook.field_url')" required>
						<UInput id="wh_url" v-model="form.url" class="w-full" type="url" required />
					</UFormField>

					<!-- Payload Format -->
					<div class="flex items-center gap-4">
						<label class="font-semibold w-1/3 ltr:text-left rtl:text-right">
							{{ $t("webhook.field_format") }} <span class="text-red-500">*</span>
						</label>
						<USelectMenu v-model="selectedFormat" :items="formatOptions" label-key="label" class="w-2/3">
							<template #item-label="{ item }">{{ item.label }}</template>
						</USelectMenu>
					</div>

					<!-- Secret -->
					<UFormField :label="$t('webhook.field_secret')">
						<UInput id="wh_secret" v-model="secretForInput" class="w-full" type="password" autocomplete="new-password" />
						<template #hint>
							<small v-if="webhook" class="text-muted">{{ $t("webhook.field_secret_placeholder") }}</small>
						</template>
					</UFormField>

					<!-- Secret Header -->
					<UFormField :label="$t('webhook.field_secret_header')">
						<UInput id="wh_secret_header" v-model="secretHeaderForInput" class="w-full" />
					</UFormField>

					<!-- Payload toggles -->
					<div class="grid grid-cols-2 gap-3 ltr:text-left rtl:text-right">
						<div class="flex items-center gap-2">
							<USwitch v-model="form.send_photo_id" id="send_photo_id" />
							<label for="send_photo_id" class="font-semibold text-sm">{{ $t("webhook.field_send_photo_id") }}</label>
						</div>
						<div class="flex items-center gap-2">
							<USwitch v-model="form.send_album_id" id="send_album_id" />
							<label for="send_album_id" class="font-semibold text-sm">{{ $t("webhook.field_send_album_id") }}</label>
						</div>
						<div class="flex items-center gap-2">
							<USwitch v-model="form.send_title" id="send_title" />
							<label for="send_title" class="font-semibold text-sm">{{ $t("webhook.field_send_title") }}</label>
						</div>
						<div class="flex items-center gap-2">
							<USwitch v-model="form.send_size_variants" id="send_size_variants" />
							<label for="send_size_variants" class="font-semibold text-sm">{{ $t("webhook.field_send_size_variants") }}</label>
						</div>
					</div>

					<!-- Enabled -->
					<div class="flex items-center gap-2 ltr:text-left rtl:text-right">
						<USwitch v-model="form.enabled" id="wh_enabled" />
						<label for="wh_enabled" class="font-semibold">{{ $t("webhook.field_enabled") }}</label>
					</div>
				</div>
			</form>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton :label="$t('webhook.cancel')" color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="onHide" />
				<UButton
					:label="webhook ? $t('webhook.save') : $t('webhook.create')"
					color="neutral"
					class="flex-1 justify-center font-bold"
					:loading="isSaving"
					@click="save"
				/>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import WebhookService, { type CreateWebhookRequest } from "@/services/webhook-service";

const props = defineProps<{
	webhook?: App.Http.Resources.Models.WebhookResource;
}>();

const visible = defineModel<boolean>("open", { default: false });
const emits = defineEmits<{
	saved: [];
}>();

const toast = useAppToast();
const isSaving = ref(false);

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

type EventOption = { label: string; value: App.Enum.PhotoWebhookEvent };
type FormatOption = { label: string; value: App.Enum.WebhookPayloadFormat };

const eventOptions: EventOption[] = [
	{ label: trans("webhook.event_photo_add"), value: "photo.add" },
	{ label: trans("webhook.event_photo_move"), value: "photo.move" },
	{ label: trans("webhook.event_photo_delete"), value: "photo.delete" },
];

const methodOptions: App.Enum.WebhookMethod[] = ["POST", "GET", "PUT", "PATCH", "DELETE"];

const formatOptions: FormatOption[] = [
	{ label: trans("webhook.format_json"), value: "json" },
	{ label: trans("webhook.format_query_string"), value: "query_string" },
];

const selectedEvent = computed<EventOption | undefined>({
	get: () => eventOptions.find((o) => o.value === form.value.event),
	set: (v) => {
		if (v) form.value.event = v.value;
	},
});

const selectedFormat = computed<FormatOption | undefined>({
	get: () => formatOptions.find((o) => o.value === form.value.payload_format),
	set: (v) => {
		if (v) form.value.payload_format = v.value;
	},
});

// UInput's v-model requires `string | undefined` (no null); the request payload allows null
// for these optional fields.
const secretForInput = computed<string | undefined>({
	get: () => form.value.secret ?? undefined,
	set: (v) => {
		form.value.secret = v;
	},
});
const secretHeaderForInput = computed<string | undefined>({
	get: () => form.value.secret_header ?? undefined,
	set: (v) => {
		form.value.secret_header = v;
	},
});

function onHide(): void {
	visible.value = false;
}

function save(): void {
	isSaving.value = true;

	const savePromise = props.webhook
		? WebhookService.update(props.webhook.id, { ...form.value, webhook_id: props.webhook.id })
		: WebhookService.create(form.value);

	savePromise
		.then(() => {
			toast.add({
				severity: "success",
				summary: trans("toasts.success"),
				detail: trans(props.webhook ? "webhook.updated" : "webhook.created"),
				life: 3000,
			});
			visible.value = false;
			emits("saved");
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("webhook.error_save"), life: 3000 });
		})
		.finally(() => {
			isSaving.value = false;
		});
}

// Initialize form when webhook prop changes
watch(
	() => props.webhook,
	(webhook) => {
		if (webhook) {
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
		} else {
			form.value = defaultForm();
		}
	},
	{ immediate: true },
);
</script>
