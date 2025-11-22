import WebshopService from "@/services/webshop-service";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { Ref } from "vue";

export function useStepOffline(email: Ref<undefined | string>, step: Ref<number>, orderManagement: OrderManagementStateStore) {
	function markAsOffline(): Promise<void> {
		return WebshopService.Checkout.offline({
			email: email.value,
		})
			.then(() => {
				step.value = 2; // Move to confirmed step
				orderManagement.reset();
			})
			.catch((error) => {
				if (error.response.status === 400) {
					// Validation error
					if (error.response.data.errors.email) {
						email.value = undefined;
					}
				}
			});
	}

	return {
		markAsOffline,
	};
}
