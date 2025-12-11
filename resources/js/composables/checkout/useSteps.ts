import { CheckoutSteps } from "@/config/constants";
import { Ref } from "vue";
import { ref } from "vue";

const steps = ref<number>(1);

export function useSteps(options: Ref<{ is_offline: boolean } | undefined>) {
	function stepToNumber(step: CheckoutSteps | undefined): number {
		switch (step) {
			case "info":
				return 1;
			case "payment":
				return 2;
			case "completed":
			case "cancelled":
			case "failed":
				return options.value?.is_offline === true ? 2 : 3;
			default:
				return 1;
		}
	}

	return {
		steps,
		stepToNumber,
	};
}
