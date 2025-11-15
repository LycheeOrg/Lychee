<template>
	<div
		class="w-full flex flex-col p-8 bg-surface-50 dark:bg-surface-950/25 rounded border border-surface-200 dark:border-surface-700"
		v-if="options"
	>
		<div class="text-lg mb-12 font-bold text-center">Your info</div>
		<div v-if="options.allow_guest_checkout === false && userStore.isGuest">
			<div class="text-red-600 font-medium mb-4">You must be logged in to proceed with the checkout.</div>
			<Button
				label="Go to login"
				icon="pi pi-sign-in"
				class="border-none"
				@click="router.push({ name: 'login', query: { redirect: 'checkout' } })"
			/>
		</div>
		<template v-else>
			<div class="text-sm text-muted-color-emphasis mb-4" v-if="userStore.isGuest">
				You are not logged in! Please provide your email address to continue.
			</div>
			<div class="text-sm text-muted-color-emphasis mb-4" v-else-if="userStore.user?.email">
				You are logged in as <span class="text-primary">{{ userStore.user?.username }}</span> ({{ userStore.user.email }}). You can change
				your email address if you want to receive order-related communication at a different address.
			</div>
			<div class="text-sm text-muted-color-emphasis mb-4" v-else>
				You are logged in as <span class="text-primary">{{ userStore.user?.username }}</span
				>. You set an email address if you want to receive order-related communication.
			</div>
			<div class="flex flex-col mb-2 gap-1">
				<FloatLabel variant="on">
					<InputText id="email" v-model="email" @updated="validate" :invalid="errors.email !== undefined" />
					<label for="email">{{ $t("profile.login.email") }} <span class="text-red-500" v-if="userStore.isGuest">*</span></label>
				</FloatLabel>
				<span class="text-muted-color text-2xs"> Your email will only be used for order-related communication. </span>
				<span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</span>
			</div>
			<div>
				<Checkbox v-model="consentGiven" binary inputId="consent" class="mt-4" />
				<label for="consent" class="text-sm text-muted-color-emphasis">
					I agree to the
					<a :href="options.privacy_url" target="_blank" class="text-primary-600 hover:underline">privacy policy</a> and
					<a :href="options.terms_url" target="_blank" class="text-primary-600 hover:underline">terms of service</a>.
				</label>
			</div>
		</template>
	</div>
</template>
<script setup lang="ts">
import { useStepOne } from "@/composables/checkout/useStepOne";
import { useUserStore } from "@/stores/UserState";
import FloatLabel from "primevue/floatlabel";
import { useRouter } from "vue-router";
import InputText from "@/components/forms/basic/InputText.vue";
import Checkbox from "primevue/checkbox";
import Button from "primevue/button";

const userStore = useUserStore();
const router = useRouter();

const { email, options, errors, validate, consentGiven } = useStepOne(userStore);
</script>
