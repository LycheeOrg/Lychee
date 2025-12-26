<template>
	<!-- Heavily inspired from https://github.com/muhammed/interactive-card -- MIT License -- Copyright (c) 2019 Muhammed Erdem -->
	<div class="mx-auto w-full max-w-sm">
		<div class="-mb-36">
			<Card :labels="formData" :is-card-flipped />
		</div>
		<div class="flex flex-col pt-48 gap-8">
			<FloatLabel variant="over">
				<InputText
					id="cardNumber"
					v-model="formData.cardNumber"
					:maxlength="cardNumberMaxLength"
					v-number-only
					@focus="onFocus('cardNumber')"
					@value-change="changeNumber"
				/>
				<label for="cardNumber">{{ $t("webshop.cardForm.cardNumber") }}</label>
			</FloatLabel>
			<FloatLabel variant="over">
				<InputText id="cardName" v-model="formData.cardName" v-letter-only @focus="onFocus('cardName')" />
				<label for="cardName">{{ $t("webshop.cardForm.cardName") }}</label>
			</FloatLabel>
			<div class="flex gap-4">
				<div class="flex flex-col w-full">
					<label for="cardMonth" class="text-gray-500">{{ $t("webshop.cardForm.expirationDate") }}</label>
					<div class="flex gap-2 mt-1">
						<Select
							class="w-full border-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
							label-id="cardMonth"
							:options="months"
							v-model="formData.cardMonth"
							@focus="onFocus('cardMonth')"
							@value-change="updated"
							placeholder="Month"
						/>
						<Select
							class="w-full border-0 border-b hover:border-b-primary-400 focus:border-b-primary-400"
							label-id="cardYear"
							:options="years"
							v-model="formData.cardYear"
							@focus="onFocus('cardYear')"
							@value-change="updated"
							placeholder="Year"
						/>
					</div>
				</div>
				<FloatLabel variant="over" class="mt-7">
					<InputText
						id="cardCvv"
						v-model="formData.cardCvv"
						v-number-only
						@focus="onFocus('cardCvv')"
						@value-change="updated"
						maxlength="4"
						autocomplete="off"
					/>
					<label for="cardCvv">{{ $t("webshop.cardForm.CVV") }}</label>
				</FloatLabel>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Card from "./Card.vue";
// import { trans } from "laravel-vue-i18n";
import FloatLabel from "primevue/floatlabel";
import InputText from "@/components/forms/basic/InputText.vue";
import Select from "primevue/select";
import { CardDetails } from "@/services/webshop-service";

const emits = defineEmits<{
	updated: [details: CardDetails];
}>();

const formData = ref({
	cardName: "",
	cardNumber: "",
	cardMonth: undefined as undefined | number,
	cardYear: undefined as undefined | number,
	cardCvv: "",
});

const isCardFlipped = ref(false);

const minCardYear = ref(new Date().getFullYear());
const cardNumberMaxLength = ref(19);

const months = Array.from({ length: 12 }, (_, i) => generateMonthValue(i + 1));
const years = Array.from({ length: 12 }, (_, i) => i + minCardYear.value);

const vNumberOnly = {
	mounted: (el: HTMLElement) => {
		function checkValue(event: KeyboardEvent) {
			(<HTMLInputElement>event.target).value = (<HTMLInputElement>event.target).value.replace(/[^0-9]/g, "");
			if (!["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"].includes(event.key)) {
				event.preventDefault();
			}
		}
		el.addEventListener("keypress", checkValue);
	},
};

const vLetterOnly = {
	mounted: (el: HTMLElement) => {
		function checkValue(event: KeyboardEvent) {
			if (["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"].includes(event.key)) {
				event.preventDefault();
			}
			return true;
		}
		el.addEventListener("keypress", checkValue);
	},
};

function generateMonthValue(n: number): string {
	return n < 10 ? `0${n}` : `${n}`;
}

function onFocus(field: string) {
	if (field === "cardCvv") {
		isCardFlipped.value = true;
	} else {
		isCardFlipped.value = false;
	}
}

function changeNumber(input: string) {
	const value = input.replace(/\D/g, "");
	// american express, 15 digits
	if (/^3[47]\d{0,13}$/.test(value)) {
		formData.value.cardNumber = value
			.replace(/(\d{4})/, "$1 ")
			.replace(/(\d{4}) (\d{6})/, "$1 $2 ")
			.trim();
		cardNumberMaxLength.value = 17;
		updated();
		return;
	}
	if (/^3(?:0[0-5]|[68]\d)\d{0,11}$/.test(value)) {
		// diner's club, 14 digits
		formData.value.cardNumber = value
			.replace(/(\d{4})/, "$1 ")
			.replace(/(\d{4}) (\d{6})/, "$1 $2 ")
			.trim();
		cardNumberMaxLength.value = 16;
		updated();
		return;
	}
	// regular cc number, 16 digits
	formData.value.cardNumber = value
		.replace(/(\d{4})/, "$1 ")
		.replace(/(\d{4}) (\d{4})/, "$1 $2 ")
		.replace(/(\d{4}) (\d{4}) (\d{4})/, "$1 $2 $3 ")
		.trim();
	cardNumberMaxLength.value = 19;
	updated();
}

function updated() {
	emits("updated", {
		number: formData.value.cardNumber.replace(/ /g, ""),
		expiryMonth: formData.value.cardMonth?.toString() ?? "",
		expiryYear: formData.value.cardYear?.toString() ?? "",
		cvv: formData.value.cardCvv,
	});
}
</script>
