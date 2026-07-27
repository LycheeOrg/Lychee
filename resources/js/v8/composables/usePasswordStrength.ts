import init, { zxcvbn, ZxcvbnResult } from "@lychee-org/zxcvbn-wasm";
import { computed, ref, Ref } from "vue";

let readyPromise: Promise<unknown> | undefined;
const ready = ref(false);

export function initZxcvbn(): Promise<unknown> {
	readyPromise ??= init().then(() => {
		ready.value = true;
	});
	return readyPromise;
}

export function usePasswordStrength(modelValue: Ref<string | null | undefined>) {
	initZxcvbn();

	const text = computed(() => {
		if (!result.value) return "Enter a password";
		if (result.value.score === 0) return "Enter a password";
		if (result.value.score <= 2) return "Weak password";
		if (result.value.score === 3) return "Medium password";
		return "Strong password";
	});

	const result = computed<ZxcvbnResult | undefined>(() => (ready.value && modelValue.value ? zxcvbn(modelValue.value) : undefined));

	const color = computed(() => {
		if (!result.value) return "neutral";
		if (result.value.score <= 1) return "error";
		if (result.value.score <= 2) return "warning";
		if (result.value.score === 3) return "warning";
		return "success";
	});
	const strength = computed(() => (result.value ? result.value.score + 1 : 0));

	return { color, text, strength, result, initZxcvbn };
}
