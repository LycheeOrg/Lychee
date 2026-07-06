<template>
	<!-- Heavily inspired from https://github.com/muhammed/interactive-card -- MIT License -- Copyright (c) 2019 Muhammed Erdem -->
	<div class="mx-auto w-full max-w-sm">
		<div class="-mb-36">
			<Card :labels="formData" :is-card-flipped="isCardFlipped" />
		</div>
		<div class="flex flex-col pt-48 gap-8">
			<UFormField :label="$t('webshop.cardForm.cardNumber')">
				<UInput
					id="cardNumber"
					v-model="formData.cardNumber"
					v-number-only
					:maxlength="cardNumberMaxLength"
					class="w-full"
					@focus="onFocus('cardNumber')"
					@value-change="changeNumber"
				/>
			</UFormField>
			<UFormField :label="$t('webshop.cardForm.cardName')">
				<UInput id="cardName" v-model="formData.cardName" v-letter-only class="w-full" @focus="onFocus('cardName')" />
			</UFormField>
			<div class="flex gap-4">
				<div class="flex flex-col w-full">
					<label for="cardMonth" class="text-muted text-sm mb-1">{{ $t("webshop.cardForm.expirationDate") }}</label>
					<div class="flex gap-2">
						<USelectMenu
							v-model="formData.cardMonth"
							:items="months"
							class="w-full"
							placeholder="Month"
							@focus="onFocus('cardMonth')"
							@update:model-value="updated"
						/>
						<USelectMenu
							v-model="formData.cardYear"
							:items="years"
							class="w-full"
							placeholder="Year"
							@focus="onFocus('cardYear')"
							@update:model-value="updated"
						/>
					</div>
				</div>
				<UFormField :label="$t('webshop.cardForm.CVV')" class="mt-1">
					<UInput
						id="cardCvv"
						v-model="formData.cardCvv"
						v-number-only
						maxlength="4"
						autocomplete="off"
						class="w-full"
						@focus="onFocus('cardCvv')"
						@value-change="updated"
					/>
				</UFormField>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Card from "./Card.vue";
import { CardDetails } from "@/services/webshop-service";

const emits = defineEmits<{
	updated: [details: CardDetails];
}>();

const formData = ref({
	cardName: "",
	cardNumber: "",
	cardMonth: undefined as undefined | string,
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
