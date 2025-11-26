import WebshopService from "@/services/webshop-service";
import { OrderManagementStateStore } from "@/stores/OrderManagement";
import { Ref } from "vue";
import { Router } from "vue-router";

export function useStepOffline(email: Ref<undefined | string>, router: Router, orderManagement: OrderManagementStateStore) {
	function markAsOffline(): Promise<void> {
		return WebshopService.Checkout.offline({
			email: email.value,
		})
			.then(() => {
				router.push({ name: "checkout", params: { step: "completed" } });
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
