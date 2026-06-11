<template>
	<div
		class="w-full flex flex-col p-8 bg-surface-50 dark:bg-surface-950/25 rounded border border-surface-200 dark:border-surface-700"
		v-if="options"
	>
		<div class="text-lg mb-12 font-bold text-center">{{ $t("webshop.infoSection.yourInfo") }}</div>
		<div v-if="options.allow_guest_checkout === false && userStore.isGuest">
			<div class="text-red-600 font-medium mb-4">{{ $t("webshop.infoSection.mustBeLoggedIn") }}</div>
			<Button
				:label="$t('webshop.infoSection.goToLogin')"
				icon="pi pi-sign-in"
				class="border-none"
				@click="router.push({ name: 'login', query: { redirect: 'checkout' } })"
			/>
		</div>
		<template v-else>
			<div class="text-sm text-muted-color-emphasis mb-4" v-if="userStore.isGuest">
				{{ $t("webshop.infoSection.notLoggedInMessage") }}
			</div>
			<div
				class="text-sm text-muted-color-emphasis mb-4"
				v-else-if="userStore.user?.email"
				v-html="sprintf(trans('webshop.infoSection.loggedInWithEmail'), strip(userStore.user?.username), strip(userStore.user.email))"
			></div>

			<div
				class="text-sm text-muted-color-emphasis mb-4"
				v-else
				v-html="sprintf(trans('webshop.infoSection.loggedInWithoutEmail'), strip(userStore.user?.username))"
			></div>
			<div class="flex flex-col mb-2 gap-1">
				<FloatLabel variant="on">
					<InputText id="email" v-model="email" @update:modelValue="validate" :invalid="errors.email !== undefined" />
					<label for="email">{{ $t("profile.login.email") }} <span class="text-red-500" v-if="userStore.isGuest">*</span></label>
				</FloatLabel>
				<span class="text-muted-color text-2xs"> {{ $t("webshop.infoSection.emailUsageNote") }} </span>
				<span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</span>
			</div>
			<div>
				<Checkbox v-model="consentGiven" binary inputId="consent" class="mt-4 ltr:mr-2 rtl:ml-2" />
				<label
					for="consent"
					class="text-sm text-muted-color-emphasis"
					v-html="sprintf(trans('webshop.infoSection.consentAgreement'), options.privacy_url, options.terms_url)"
				>
				</label>
			</div>

			<!-- Shipping address (required when basket contains print items) -->
			<template v-if="orderManagementStore.hasPrintItems">
				<div class="mt-6 mb-2 font-semibold text-base">{{ $t("webshop.shippingAddress.title") }}</div>
				<p class="text-sm text-muted-color mb-4">{{ $t("webshop.shippingAddress.required") }}</p>
				<div class="flex flex-col gap-3">
					<div class="flex gap-2">
						<FloatLabel variant="on" class="flex-1">
							<InputText id="shipping-street-name" v-model="shippingStreetName" class="w-full" />
							<label for="shipping-street-name">{{ $t("webshop.shippingAddress.streetName") }} *</label>
						</FloatLabel>
						<FloatLabel variant="on" class="w-28">
							<InputText id="shipping-street-number" v-model="shippingStreetNumber" class="w-full" />
							<label for="shipping-street-number">{{ $t("webshop.shippingAddress.streetNumber") }}</label>
						</FloatLabel>
					</div>
					<FloatLabel variant="on">
						<InputText id="shipping-additional" v-model="shippingAdditionalInfo" class="w-full" />
						<label for="shipping-additional">{{ $t("webshop.shippingAddress.additionalInfo") }}</label>
					</FloatLabel>
					<div class="flex gap-2">
						<FloatLabel variant="on" class="flex-1">
							<InputText id="shipping-city" v-model="shippingCity" class="w-full" />
							<label for="shipping-city">{{ $t("webshop.shippingAddress.city") }} *</label>
						</FloatLabel>
						<FloatLabel variant="on" class="w-32">
							<InputText id="shipping-post-code" v-model="shippingPostCode" class="w-full" />
							<label for="shipping-post-code">{{ $t("webshop.shippingAddress.postCode") }} *</label>
						</FloatLabel>
					</div>
					<FloatLabel variant="on">
						<InputText id="shipping-country" v-model="shippingCountry" class="w-full" />
						<label for="shipping-country">{{ $t("webshop.shippingAddress.country") }} *</label>
					</FloatLabel>
				</div>
			</template>
		</template>
	</div>
</template>
<script setup lang="ts">
import { useStepOne } from "@/composables/checkout/useStepOne";
import { useUserStore } from "@/stores/UserState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import FloatLabel from "primevue/floatlabel";
import { useRouter } from "vue-router";
import InputText from "@/components/forms/basic/InputText.vue";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";

const userStore = useUserStore();
const router = useRouter();
const orderManagementStore = useOrderManagementStore();

function strip(html: string | null | undefined): string | null | undefined {
	if (!html) {
		return html;
	}

	const doc = new DOMParser().parseFromString(html, "text/html");
	return doc.body.textContent || "";
}
const {
	email,
	options,
	errors,
	validate,
	consentGiven,
	shippingStreetName,
	shippingStreetNumber,
	shippingAdditionalInfo,
	shippingCity,
	shippingPostCode,
	shippingCountry,
} = useStepOne(userStore, orderManagementStore);
</script>
