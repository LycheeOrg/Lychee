<template>
	<div class="flex justify-end">
		<i
			:class="{
				'cursor-pointer pi pi-question-circle text-lg': true,
				'text-muted-color-emphasis': !isVisible,
				'text-primary-500': isVisible,
			}"
			@click="toggle"
			v-tooltip.left="$t('webshop.orderLegend.needHelp')"
		/>
	</div>
	<Transition name="fade">
		<div v-if="isVisible" class="flex flex-row-reverse flex-wrap justify-center mb-8">
			<div class="flex flex-col justify-start items-start gap-1 w-full lg:w-1/3 mb-4">
				<div class="text-lg font-bold mb-2">{{ $t("webshop.orderLegend.legend") }}</div>
				<div><OrderStatus size="small" status="pending" /> : {{ $t("webshop.orderLegend.pendingDesc") }}</div>
				<div><OrderStatus size="small" status="processing" /> : {{ $t("webshop.orderLegend.processingDesc") }}</div>
				<div><OrderStatus size="small" status="offline" /> : {{ $t("webshop.orderLegend.offlineDesc") }}</div>
				<div><OrderStatus size="small" status="completed" /> : {{ $t("webshop.orderLegend.completedDesc") }}</div>
				<div><OrderStatus size="small" status="closed" /> : {{ $t("webshop.orderLegend.closedDesc") }}</div>
				<div><OrderStatus size="small" status="cancelled" /> : {{ $t("webshop.orderLegend.cancelledDesc") }}</div>
				<div><OrderStatus size="small" status="failed" /> : {{ $t("webshop.orderLegend.failedDesc") }}</div>
			</div>
			<div class="flex flex-col justify-start items-start gap-2 w-full lg:w-2/3">
				<div class="mb-2">{{ $t("webshop.orderLegend.flowsIntro") }}</div>
				<div class="flex justify-center gap-2 items-center">
					<OrderStatus size="small" status="pending" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="processing" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="completed" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="closed" />
				</div>
				<div class="flex justify-center gap-2 items-center">
					<OrderStatus size="small" status="pending" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="processing" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="cancelled" />
				</div>
				<div class="flex justify-center gap-2 items-center">
					<OrderStatus size="small" status="pending" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="processing" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="failed" />
				</div>
				<div class="flex justify-center gap-2 items-center">
					<OrderStatus size="small" status="pending" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="offline" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="completed" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="closed" />
				</div>
				<div class="flex justify-center gap-2 items-center">
					<OrderStatus size="small" status="cancelled" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="processing" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="completed" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="closed" />
				</div>
				<div class="flex justify-center gap-2 items-center">
					<OrderStatus size="small" status="failed" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="processing" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="completed" />
					<i class="pi pi-arrow-right" />
					<OrderStatus size="small" status="closed" />
				</div>
			</div>
			<div class="mb-4 text-muted-color flex flex-col gap-2">
				<p>
					{{ $t("webshop.orderLegend.offlineExplanationPart1") }}
					<OrderStatus size="small" status="offline" />
					{{ $t("webshop.orderLegend.offlineExplanationPart2") }}
					<OrderStatus size="small" status="completed" />
					{{ $t("webshop.orderLegend.offlineExplanationPart3") }}
				</p>
				<p>
					{{ $t("webshop.orderLegend.closedExplanationPart1") }}
					<OrderStatus size="small" status="closed" />
					{{ $t("webshop.orderLegend.closedExplanationPart2") }}
				</p>
			</div>
		</div>
	</Transition>
</template>
<script setup lang="ts">
import OrderStatus from "@/components/webshop/OrderStatus.vue";
import { ref } from "vue";

const isVisible = ref(false);

function toggle() {
	isVisible.value = !isVisible.value;
}
</script>
<style scoped>
.fade-enter-active,
.fade-leave-active {
	transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
	opacity: 0;
}
</style>
