import WebshopService from "@/services/webshop-service";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { UserStore } from "@/stores/UserState";
import { computed, ref } from "vue";
import { trans } from "laravel-vue-i18n";

const email = ref<undefined | string>(undefined);
const options = ref<undefined | App.Http.Resources.Shop.CheckoutOptionResource>(undefined);
const consentGiven = ref(false);
const errors = ref<Record<string, string | undefined>>({});

export function useStepOne(userStore: UserStore, orderManagementStore: OrderManagementStateStore) {
	function loadCheckoutOptions(): Promise<void> {
		return WebshopService.Checkout.getOptions().then((response) => {
			options.value = response.data;
		});
	}

	function loadEmailForUser(): void {
		email.value = orderManagementStore.order?.email ?? userStore.user?.email ?? undefined;
	}

	const isStepOneValid = computed(() => {
		if (options.value?.allow_guest_checkout === false && userStore.isGuest) {
			return false;
		}

		if (userStore.isGuest && (!email.value || !isEmailValid())) {
			return false;
		}

		if (!consentGiven.value) {
			return false;
		}

		return true;
	});

	function isEmailValid(): boolean {
		return !(email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value));
	}

	function validate() {
		// Required for guests
		if (userStore.isGuest && !email.value) {
			errors.value.email = trans("webshop.errors.emailRequired");
			return;
		}

		// Simple email format validation: xxx@xxx.xxx
		if (!isEmailValid()) {
			errors.value.email = trans("webshop.errors.invalidEmail");
			return;
		}

		errors.value.email = undefined;
	}

	return {
		errors,
		email,
		options,
		consentGiven,
		validate,
		loadCheckoutOptions,
		loadEmailForUser,
		isStepOneValid,
	};
}
