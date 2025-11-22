import { CheckoutSteps } from "@/config/constants";
import { ref } from "vue";

const steps = ref<number>(1);

export function useSteps() {
	function stepToNumber(step: CheckoutSteps | undefined): number {
		switch (step) {
			case "info":
				return 1;
			case "payment":
				return 2;
			case "confirm":
				return 3;
			case "completed":
				return 2;
			default:
				return 1;
		}
	}

	return {
		steps,
		stepToNumber,
	};
}
