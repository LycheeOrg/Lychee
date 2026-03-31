<template>
	<Dialog v-model:visible="visible" pt:root:class="border-none" modal :dismissable-mask="true" @hide="onHide">
		<template #container>
			<div class="pt-9 px-9 w-full text-center font-bold text-xl">
				{{ webhook ? $t("webhook.modal_edit_title") : $t("webhook.modal_create_title") }}
			</div>
			<div class="p-9 text-center text-muted-color w-2xl max-w-2xl text-wrap">
				<form @submit.prevent="save">
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
						<small v-if="webhook" class="text-muted-color -mt-4 ltr:text-left rtl:text-right">
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
					@click="onHide"
				/>
				<Button
					:label="webhook ? $t('webhook.save') : $t('webhook.create')"
					class="w-full border-none rounded-none rounded-br-xl font-bold"
					:loading="isSaving"
					@click="save"
				/>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useToast } from "primevue/usetoast";
import { trans } from "laravel-vue-i18n";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import FloatLabel from "primevue/floatlabel";
import Select from "primevue/select";
import ToggleSwitch from "primevue/toggleswitch";
import InputText from "@/components/forms/basic/InputText.vue";
import WebhookService, { type CreateWebhookRequest } from "@/services/webhook-service";

const props = defineProps<{
	webhook?: App.Http.Resources.Models.WebhookResource;
}>();

const visible = defineModel<boolean>("visible", { default: false });
const emits = defineEmits<{
	saved: [];
}>();

const toast = useToast();
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
