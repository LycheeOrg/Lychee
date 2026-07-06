<template>
	<div v-if="options" class="w-full flex flex-col p-8 bg-elevated/50 rounded border border-default">
		<div class="text-lg mb-12 font-bold text-center">{{ $t("webshop.infoSection.yourInfo") }}</div>
		<div v-if="options.allow_guest_checkout === false && userStore.isGuest">
			<div class="text-error font-medium mb-4">{{ $t("webshop.infoSection.mustBeLoggedIn") }}</div>
			<UButton
				:label="$t('webshop.infoSection.goToLogin')"
				icon="prime:sign-in"
				@click="
					() => {
						router.push({ name: 'login', query: { redirect: 'checkout' } });
					}
				"
			/>
		</div>
		<template v-else>
			<div v-if="userStore.isGuest" class="text-sm text-highlighted mb-4">
				{{ $t("webshop.infoSection.notLoggedInMessage") }}
			</div>
			<div
				v-else-if="userStore.user?.email"
				class="text-sm text-highlighted mb-4"
				v-html="sprintf(trans('webshop.infoSection.loggedInWithEmail'), strip(userStore.user?.username), strip(userStore.user.email))"
			></div>

			<div
				v-else
				class="text-sm text-highlighted mb-4"
				v-html="sprintf(trans('webshop.infoSection.loggedInWithoutEmail'), strip(userStore.user?.username))"
			></div>
			<div class="flex flex-col mb-2 gap-1">
				<UFormField :label="$t('profile.login.email')">
					<UInput
						id="email"
						v-model="email"
						class="w-full"
						:color="errors.email !== undefined ? 'error' : undefined"
						@update:model-value="validate"
					/>
				</UFormField>
				<span class="text-muted text-xs"> {{ $t("webshop.infoSection.emailUsageNote") }} </span>
				<span v-if="errors.email" class="text-error text-sm">{{ errors.email }}</span>
			</div>
			<div class="flex items-center gap-2">
				<UCheckbox v-model="consentGiven" id="consent" class="mt-4" />
				<label
					for="consent"
					class="text-sm text-highlighted"
					v-html="sprintf(trans('webshop.infoSection.consentAgreement'), options.privacy_url, options.terms_url)"
				>
				</label>
			</div>

			<!-- Shipping address (required when basket contains print items) -->
			<template v-if="orderManagementStore.hasPrintItems">
				<div class="mt-6 mb-2 font-semibold text-base">{{ $t("webshop.shippingAddress.title") }}</div>
				<p class="text-sm text-muted mb-4">{{ $t("webshop.shippingAddress.required") }}</p>
				<div class="flex flex-col gap-3">
					<div class="flex gap-2">
						<UFormField :label="`${$t('webshop.shippingAddress.streetName')} *`" class="flex-1">
							<UInput id="shipping-street-name" v-model="shippingStreetName" class="w-full" />
						</UFormField>
						<UFormField :label="$t('webshop.shippingAddress.streetNumber')" class="w-28">
							<UInput id="shipping-street-number" v-model="shippingStreetNumber" class="w-full" />
						</UFormField>
					</div>
					<UFormField :label="$t('webshop.shippingAddress.additionalInfo')">
						<UInput id="shipping-additional" v-model="shippingAdditionalInfo" class="w-full" />
					</UFormField>
					<div class="flex gap-2">
						<UFormField :label="`${$t('webshop.shippingAddress.city')} *`" class="flex-1">
							<UInput id="shipping-city" v-model="shippingCity" class="w-full" />
						</UFormField>
						<UFormField :label="`${$t('webshop.shippingAddress.postCode')} *`" class="w-32">
							<UInput id="shipping-post-code" v-model="shippingPostCode" class="w-full" />
						</UFormField>
					</div>
					<UFormField :label="`${$t('webshop.shippingAddress.country')} *`">
						<UInput id="shipping-country" v-model="shippingCountry" class="w-full" />
					</UFormField>
				</div>
			</template>
		</template>
	</div>
</template>
<script setup lang="ts">
import { useStepOne } from "@/composables/checkout/useStepOne";
import { useUserStore } from "@/stores/UserState";
import { useOrderManagementStore } from "@/stores/OrderManagement";
import { useRouter } from "vue-router";
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
