<template>
	<!-- Heavily inspired from https://github.com/muhammed/interactive-card -- MIT License -- Copyright (c) 2019 Muhammed Erdem -->
	<div class="card-item max-w-sm h-60 w-full mx-auto relative z-2" :class="{ '-active': props.isCardFlipped }">
		<div class="card-item__side -front rounded-2xl overflow-hidden h-full">
			<div class="card-item__cover h-full absolute left-0 top-0 w-full overflow-hidden" />
			<div class="px-3 py-5 relative z-4 h-full select-none font-mono">
				<div class="flex items-start justify-between mb-8 px-2.5 py-0">
					<img src="https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/chip.png" class="w-[60px]" />
					<div class="h-[45px] relative flex justify-end max-w-[100px] ml-auto w-full">
						<transition name="slide-fade-up">
							<img
								:src="
									'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/' + cardType + '.png'
								"
								v-if="cardType"
								:key="cardType"
								class="max-w-full object-contain max-h-full object-top-right"
							/>
						</transition>
					</div>
				</div>
				<label for="cardNumber" class="font-bold text-xl mb-4 inline-block cursor-pointer px-2.5 py-2">
					<span v-for="(n, idx) in currentPlaceholder" :key="`num-${idx}`">
						<transition name="slide-fade-up">
							<div class="inline-block w-3.5" v-if="getIsNumberMasked(idx, n)">*</div>
							<div class="inline-block w-3.5" v-else-if="labels.cardNumber.length > idx">
								{{ labels.cardNumber[idx] }}
							</div>
							<div class="inline-block w-3.5" v-else>
								{{ n }}
							</div>
						</transition>
					</span>
				</label>
				<div class="text-white flex items-start">
					<label for="cardName" class="block cursor-pointer w-full max-w-[calc(100%-85px)] px-2.5 py-4">
						<div class="opacity-70 text-xs mb-1">{{ $t("webshop.card.cardHolder") }}</div>
						<transition name="slide-fade-up">
							<div class="overflow-hidden max-w-full text-ellipsis uppercase text-base text-nowrap" v-if="labels.cardName.length">
								<transition-group name="slide-fade-right">
									<span
										class="inline-block min-w-2 relative"
										v-for="(n, $index) in labels.cardName.replace(/\s\s+/g, ' ')"
										:key="$index + 1"
										>{{ n }}</span
									>
								</transition-group>
							</div>
							<div class="overflow-hidden max-w-full text-ellipsis uppercase text-base text-nowrap" v-else key="2">
								{{ $t("webshop.card.fullName") }}
							</div>
						</transition>
					</label>
					<div class="cursor-pointer w-[80px] flex-wrap ml-auto p-2.5 inline-flex shrink-0 whitespace-nowrap" ref="cardDate">
						<label for="cardYear" class="opacity-70 text-xs mb-1">{{ $t("webshop.card.expires") }}</label>
						<label for="cardMonth" class="relative">
							<transition name="slide-fade-up">
								<span class="w-5 inline-block" v-if="labels.cardMonth" :key="labels.cardMonth">{{ labels.cardMonth }}</span>
								<span class="w-5 inline-block" v-else key="2">{{ $t("webshop.card.MM") }}</span>
							</transition>
						</label>
						/
						<label for="cardYear" class="relative">
							<transition name="slide-fade-up">
								<span class="w-5 inline-block" v-if="labels.cardYear" :key="labels.cardYear">{{
									String(labels.cardYear).slice(2, 4)
								}}</span>
								<span class="w-5 inline-block" v-else key="2">{{ $t("webshop.card.YY") }}</span>
							</transition>
						</label>
					</div>
				</div>
			</div>
		</div>
		<div class="card-item__side -back rounded-2xl overflow-hidden h-full absolute top-0 left-0 w-full p-0">
			<div class="card-item__cover" />
			<div class="w-full h-12 mt-8 relative z-2 bg-gray-950/80"></div>
			<div class="text-right relative z-2 pb-3.5 px-3.5 pt-5">
				<div class="pr-2.5 text-xs font-semibold text-white mb-3">CVV</div>
				<div class="bg-white mb-6 flex items-center pr-3.5 text-sm rounded-sm h-10 text-right justify-end text-blue-950">
					<span v-for="(n, $index) in labels.cardCvv" :key="$index">*</span>
				</div>
				<div class="h-[30px] relative flex justify-end max-w-[100px] ml-auto w-full opacity-70">
					<img
						:src="'https://raw.githubusercontent.com/muhammederdem/credit-card-form/master/src/assets/images/' + cardType + '.png'"
						v-if="cardType"
						class="max-w-full object-contain max-h-full object-top-right"
					/>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
	labels: {
		cardNumber: string;
		cardName: string;
		cardMonth: undefined | number;
		cardYear: undefined | number;
		cardCvv: string;
	};
	isCardFlipped: boolean;
}>();

const amexCardPlaceholder = "#### ###### #####";
const dinersCardPlaceholder = "#### ###### ####";
const defaultCardPlaceholder = "#### #### #### ####";

const cardType = computed(() => {
	const number = props.labels.cardNumber;
	let re = new RegExp("^4");
	if (number.match(re) != null) {
		return "visa";
	}

	re = new RegExp("^(34|37)");
	if (number.match(re) != null) {
		return "amex";
	}

	re = new RegExp("^5[1-5]");
	if (number.match(re) != null) {
		return "mastercard";
	}

	re = new RegExp("^6011");
	if (number.match(re) != null) {
		return "discover";
	}

	re = new RegExp("^62");
	if (number.match(re) != null) {
		return "unionpay";
	}

	re = new RegExp("^9792");
	if (number.match(re) != null) {
		return "troy";
	}

	re = new RegExp("^3(?:0([0-5]|9)|[689]\\d?)\\d{0,11}");
	if (number.match(re) != null) {
		return "dinersclub";
	}

	re = new RegExp("^35(2[89]|[3-8])");
	if (number.match(re) != null) {
		return "jcb";
	}

	return ""; // default type
});

const currentPlaceholder = computed(() => {
	if (cardType.value === "amex") {
		return amexCardPlaceholder;
	} else if (cardType.value === "dinersclub") {
		return dinersCardPlaceholder;
	} else {
		return defaultCardPlaceholder;
	}
});

function getIsNumberMasked(index: number, n: string) {
	return index > 4 && index < 14 && props.labels.cardNumber.length > index && n.trim() !== "";
}
</script>

<style scoped lang="scss">
.card-item {
	&.-active {
		.card-item__side.-front {
			transform: perspective(1000px) rotateY(180deg) rotateX(0deg) rotateZ(0deg);
		}
		.card-item__side.-back {
			transform: perspective(1000px) rotateY(0) rotateX(0deg) rotateZ(0deg);
		}
	}

	&__side {
		transform: perspective(2000px) rotateY(0deg) rotateX(0deg) rotate(0deg);
		transform-style: preserve-3d;
		transition: all 0.8s cubic-bezier(0.71, 0.03, 0.56, 0.85);
		backface-visibility: hidden;

		&.-back {
			transform: perspective(2000px) rotateY(-180deg) rotateX(0deg) rotate(0deg);
			z-index: 2;
			background-color: #2364d2;
			background-image: linear-gradient(43deg, #283784 0%, #491493 46%, #0c296b 100%);

			.card-item__cover {
				transform: rotateY(-180deg);
			}
		}
	}
	&__cover {
		background-color: #1c1d27;
		background-image: linear-gradient(147deg, #354fce 0%, #0c296b 74%);
		overflow: hidden;
		&:after {
			content: "";
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			background: rgba(6, 2, 29, 0.45);
		}
	}
}

.slide-fade-up-enter-active {
	transition: all 0.25s ease-in-out;
	transition-delay: 0.1s;
	position: relative;
}
.slide-fade-up-leave-active {
	transition: all 0.25s ease-in-out;
	position: absolute;
}
.slide-fade-up-enter {
	opacity: 0;
	transform: translateY(15px);
	pointer-events: none;
}
.slide-fade-up-leave-to {
	opacity: 0;
	transform: translateY(-15px);
	pointer-events: none;
}

.slide-fade-right-enter-active {
	transition: all 0.25s ease-in-out;
	transition-delay: 0.1s;
	position: relative;
}
.slide-fade-right-leave-active {
	transition: all 0.25s ease-in-out;
	position: absolute;
}
.slide-fade-right-enter {
	opacity: 0;
	transform: translateX(10px) rotate(45deg);
	pointer-events: none;
}
.slide-fade-right-leave-to {
	opacity: 0;
	transform: translateX(-10px) rotate(45deg);
	pointer-events: none;
}
</style>
