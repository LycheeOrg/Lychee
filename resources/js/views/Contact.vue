<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ contactConfig?.header ? contactConfig.header : $t("contact.title") }}
		</template>
		<template #end></template>
	</Toolbar>

	<Panel class="border-0 max-w-2xl mx-auto mt-6" v-if="contactConfig">
		<div v-if="submitted" class="text-center p-8">
			<i class="pi pi-check-circle text-green-500 text-5xl mb-4 block" />
			<p class="text-lg">{{ contactConfig.thank_you_message ? contactConfig.thank_you_message : $t("contact.success_message") }}</p>
			<Button class="mt-6 border-none" severity="secondary" :label="$t('contact.clear_button')" @click="reset" />
		</div>

		<form v-else class="flex flex-col gap-5" @submit.prevent="submit">
			<p class="text-muted-color">{{ contactConfig.headline ? contactConfig.headline : $t("contact.description") }}</p>

			<!-- Name -->
			<div class="flex flex-col gap-1">
				<label for="contact-name" class="font-medium">{{ $t("contact.name_label") }} <span class="text-red-500">*</span></label>
				<InputText
					id="contact-name"
					v-model="form.name"
					:placeholder="$t('contact.name_placeholder')"
					:invalid="errors.name !== ''"
					maxlength="255"
					class="w-full"
				/>
				<small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
			</div>

			<!-- Email -->
			<div class="flex flex-col gap-1">
				<label for="contact-email" class="font-medium"
					>{{ contactConfig.contact_method ? contactConfig.contact_method : $t("contact.email_label") }}
					<span class="text-red-500">*</span></label
				>
				<InputText
					id="contact-email"
					v-model="form.email"
					:placeholder="$t('contact.email_placeholder')"
					:invalid="errors.email !== ''"
					maxlength="255"
					class="w-full"
				/>
				<small v-if="errors.email" class="text-red-500">{{ errors.email }}</small>
			</div>

			<!-- Security Question (optional) -->
			<div v-if="contactConfig.security_question" class="flex flex-col gap-1">
				<label for="contact-security" class="font-medium"
					>{{ contactConfig.security_question ? contactConfig.security_question : $t("contact.security_question_label") }}
					<span class="text-red-500">*</span></label
				>
				<InputText
					id="contact-security"
					v-model="form.security_answer"
					:placeholder="$t('contact.security_answer_placeholder')"
					:invalid="errors.security_answer !== ''"
					class="w-full"
				/>
				<small v-if="errors.security_answer" class="text-red-500">{{ errors.security_answer }}</small>
			</div>

			<!-- Message -->
			<div class="flex flex-col gap-1">
				<label for="contact-message" class="font-medium"
					>{{ contactConfig.message_label ? contactConfig.message_label : $t("contact.message_label") }}
					<span class="text-red-500">*</span></label
				>
				<Textarea
					id="contact-message"
					v-model="form.message"
					:placeholder="contactConfig.message_answer ? contactConfig.message_answer : $t('contact.message_placeholder')"
					:invalid="errors.message !== ''"
					:rows="5"
					maxlength="5000"
					class="w-full"
				/>
				<div class="flex justify-between">
					<small v-if="errors.message" class="text-red-500">{{ errors.message }}</small>
					<small class="text-muted-color ltr:ml-auto rtl:mr-auto">{{ form.message.length }} / 5000</small>
				</div>
			</div>

			<!-- Consent (optional) -->
			<div v-if="contactConfig.is_consent_required" class="flex items-start gap-3">
				<Checkbox v-model="form.consent_agreed" binary input-id="contact-consent" :invalid="errors.consent_agreed !== ''" />
				<label for="contact-consent" class="text-sm cursor-pointer">
					{{ contactConfig.consent_text ? contactConfig.consent_text : $t("contact.consent_label") }} <span class="text-red-500">*</span
					><br />
					<a
						v-if="contactConfig.privacy_policy_url"
						:href="contactConfig.privacy_policy_url"
						target="_blank"
						rel="noopener noreferrer"
						class="underline"
					>
						{{ $t("contact.consent_privacy_link") }}
					</a>
				</label>
			</div>
			<small v-if="errors.consent_agreed" class="text-red-500">{{ errors.consent_agreed }}</small>

			<!-- Global error -->
			<div v-if="globalError" class="text-red-500 text-sm">{{ globalError }}</div>

			<!-- Actions -->
			<div class="flex gap-3 justify-end">
				<Button type="button" severity="secondary" class="border-none" :label="$t('contact.clear_button')" @click="reset" />
				<Button
					type="submit"
					class="border-none"
					:label="contactConfig.submit_button_text ? contactConfig.submit_button_text : $t('contact.submit_button')"
					:loading="loading"
					:disabled="loading"
				/>
			</div>
		</form>
	</Panel>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import ContactService from "@/services/contact-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import InputText from "@/components/forms/basic/InputText.vue";
import Textarea from "@/components/forms/basic/Textarea.vue";
import { useRouter } from "vue-router";

const lycheeStore = useLycheeStateStore();
lycheeStore.load();

const router = useRouter();
const contactConfig = ref<App.Http.Resources.GalleryConfigs.ContactConfig | undefined>(undefined);
const submitted = ref(false);
const loading = ref(false);
const globalError = ref("");

const form = ref({
	name: "",
	email: "",
	message: "",
	security_answer: "",
	consent_agreed: false,
});

const errors = ref({
	name: "",
	email: "",
	message: "",
	security_answer: "",
	consent_agreed: "",
});

function loadConfig(): void {
	ContactService.init().then((response) => {
		contactConfig.value = response.data;
		if (!contactConfig.value.is_contact_form_enabled) {
			router.push("/gallery");
		}
	});
}

function validate(): boolean {
	let valid = true;
	errors.value = { name: "", email: "", message: "", security_answer: "", consent_agreed: "" };

	if (form.value.name.trim() === "") {
		errors.value.name = trans("contact.name_required_error");
		valid = false;
	}
	if (form.value.email.trim() === "") {
		errors.value.email = trans("contact.email_required_error");
		valid = false;
	}
	if (form.value.message.trim().length < 10) {
		errors.value.message = trans("contact.message_min_length_error");
		valid = false;
	}
	if (contactConfig.value?.security_question && form.value.security_answer.trim() === "") {
		errors.value.security_answer = trans("contact.security_answer_required_error");
		valid = false;
	}
	if (contactConfig.value?.consent_text && !form.value.consent_agreed) {
		errors.value.consent_agreed = trans("contact.consent_required_error");
		valid = false;
	}

	return valid;
}

function submit(): void {
	if (!validate() || !contactConfig.value) {
		return;
	}

	loading.value = true;
	globalError.value = "";

	const payload = {
		name: form.value.name,
		email: form.value.email,
		message: form.value.message,
		...(contactConfig.value.security_question ? { security_answer: form.value.security_answer } : {}),
		...(contactConfig.value.is_consent_required ? { consent_agreed: form.value.consent_agreed } : {}),
	};

	ContactService.submit(payload)
		.then(() => {
			submitted.value = true;
		})
		.catch((error) => {
			if (error.response?.status === 429) {
				globalError.value = trans("contact.rate_limit_error");
			} else if (error.response?.status === 422) {
				globalError.value = error.response.data.message ?? trans("contact.validation_error");
			} else {
				globalError.value = trans("contact.submit_error");
			}
		})
		.finally(() => {
			loading.value = false;
		});
}

function reset(): void {
	form.value = { name: "", email: "", message: "", security_answer: "", consent_agreed: false };
	errors.value = { name: "", email: "", message: "", security_answer: "", consent_agreed: "" };
	globalError.value = "";
	submitted.value = false;
}

onMounted(() => {
	loadConfig();
});
</script>
