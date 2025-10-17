import WebshopService from "@/services/webshop-service";
import { Ref } from "vue";

export function useStepOffline(
	email: Ref<undefined | string>,
	step: Ref<number>,
) {

	function markAsOffline(): Promise<void> {
		return WebshopService.Checkout.offline({
			email: email.value,
		}).then(() => {
			step.value = 2; // Move to confirmed step
			// Display order summary directly
			// reset order state
		}).catch((error) => {
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
	}
}
