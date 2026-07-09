<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ contactConfig?.header ? contactConfig.header : $t("contact.title") }}
	</UHeader>

	<UCard v-if="contactConfig" class="max-w-2xl mx-auto mt-6">
		<div v-if="submitted" class="text-center p-8">
			<UIcon name="prime:check-circle" class="text-success text-5xl mb-4 block" />
			<p class="text-lg">{{ contactConfig.thank_you_message ? contactConfig.thank_you_message : $t("contact.success_message") }}</p>
			<UButton class="mt-6" color="neutral" variant="soft" :label="$t('contact.clear_button')" @click="reset" />
		</div>

		<form v-else class="flex flex-col gap-5" @submit.prevent="submit">
			<p class="text-muted">{{ contactConfig.headline ? contactConfig.headline : $t("contact.description") }}</p>

			<!-- Name -->
			<UFormField>
				<template #label>{{ $t("contact.name_label") }} <span class="text-error">*</span></template>
				<UInput
					id="contact-name"
					v-model="form.name"
					:placeholder="$t('contact.name_placeholder')"
					:color="errors.name !== '' ? 'error' : undefined"
					maxlength="255"
					class="w-full"
				/>
				<small v-if="errors.name" class="text-error">{{ errors.name }}</small>
			</UFormField>

			<!-- Email -->
			<UFormField>
				<template #label
					>{{ contactConfig.contact_method ? contactConfig.contact_method : $t("contact.email_label") }}
					<span class="text-error">*</span></template
				>
				<UInput
					id="contact-email"
					v-model="form.email"
					:placeholder="$t('contact.email_placeholder')"
					:color="errors.email !== '' ? 'error' : undefined"
					maxlength="255"
					class="w-full"
				/>
				<small v-if="errors.email" class="text-error">{{ errors.email }}</small>
			</UFormField>

			<!-- Security Question (optional) -->
			<UFormField v-if="contactConfig.security_question">
				<template #label
					>{{ contactConfig.security_question ? contactConfig.security_question : $t("contact.security_question_label") }}
					<span class="text-error">*</span></template
				>
				<UInput
					id="contact-security"
					v-model="form.security_answer"
					:placeholder="$t('contact.security_answer_placeholder')"
					:color="errors.security_answer !== '' ? 'error' : undefined"
					class="w-full"
				/>
				<small v-if="errors.security_answer" class="text-error">{{ errors.security_answer }}</small>
			</UFormField>

			<!-- Message -->
			<UFormField>
				<template #label
					>{{ contactConfig.message_label ? contactConfig.message_label : $t("contact.message_label") }}
					<span class="text-error">*</span></template
				>
				<UTextarea
					id="contact-message"
					v-model="form.message"
					:placeholder="contactConfig.message_answer ? contactConfig.message_answer : $t('contact.message_placeholder')"
					:color="errors.message !== '' ? 'error' : undefined"
					:rows="5"
					maxlength="5000"
					class="w-full"
				/>
				<div class="flex justify-between">
					<small v-if="errors.message" class="text-error">{{ errors.message }}</small>
					<small class="text-muted ltr:ml-auto rtl:mr-auto">{{ form.message.length }} / 5000</small>
				</div>
			</UFormField>

			<!-- Consent (optional) -->
			<UCheckbox
				v-if="contactConfig.is_consent_required"
				v-model="form.consent_agreed"
				:color="errors.consent_agreed !== '' ? 'error' : undefined"
			>
				<template #label>
					{{ contactConfig.consent_text ? contactConfig.consent_text : $t("contact.consent_label") }} <span class="text-error">*</span
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
				</template>
			</UCheckbox>
			<small v-if="errors.consent_agreed" class="text-error">{{ errors.consent_agreed }}</small>

			<!-- Global error -->
			<div v-if="globalError" class="text-error text-sm">{{ globalError }}</div>

			<!-- Actions -->
			<div class="flex gap-3 justify-end">
				<UButton type="button" color="neutral" variant="soft" :label="$t('contact.clear_button')" @click="reset" />
				<UButton
					type="submit"
					:label="contactConfig.submit_button_text ? contactConfig.submit_button_text : $t('contact.submit_button')"
					:loading="loading"
					:disabled="loading"
				/>
			</div>
		</form>
	</UCard>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import ContactService from "@/services/contact-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
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
