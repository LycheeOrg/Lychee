<template>
	<div class="flex justify-end">
		<i
			:class="{
				'cursor-pointer pi pi-question-circle text-lg': true,
				'text-muted-color-emphasis': !isVisible,
				'text-primary-500': isVisible,
			}"
			@click="toggle"
			v-tooltip.left="'Need help?'"
		/>
	</div>
	<Transition name="fade">
		<div v-if="isVisible" class="flex flex-row-reverse flex-wrap justify-center mb-8">
			<Disclaimer />
			<div class="flex flex-col justify-start items-start gap-1 w-full lg:w-1/3 mb-4">
				<div class="text-lg font-bold mb-2">Legend:</div>
				<div><OrderStatus size="small" status="pending" /> : Order is created but not paid yet.</div>
				<div><OrderStatus size="small" status="processing" /> : Payment is being processed.</div>
				<div><OrderStatus size="small" status="offline" /> : Order is marked as to be paid manually.</div>
				<div><OrderStatus size="small" status="completed" /> : Order has been paid.</div>
				<div><OrderStatus size="small" status="closed" /> : Order has been delivered.</div>
				<div><OrderStatus size="small" status="cancelled" /> : Payment has been cancelled.</div>
				<div><OrderStatus size="small" status="failed" /> : Payment has failed.</div>
			</div>
			<div class="flex flex-col justify-start items-start gap-2 w-full lg:w-2/3">
				<div class="mb-2">There are multiple possible order control flows as described bellow:</div>
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
					An order in the <OrderStatus size="small" status="offline" /> status indicates that the payment will be handled manually, such as
					through bank transfer or cash on delivery. The admin of the webshop is responsible for updating the order status to
					<OrderStatus size="small" status="completed" /> once the payment is confirmed by clicking the "Mark as Paid" button in the order
					details.
				</p>
				<p>
					Once an order reaches the <OrderStatus size="small" status="closed" /> status, it is considered finalized and no further actions
					can be taken.
				</p>
			</div>
		</div>
	</Transition>
</template>
<script setup lang="ts">
import OrderStatus from "@/components/webshop/OrderStatus.vue";
import { ref } from "vue";
import Disclaimer from "./Disclaimer.vue";

const isVisible = ref(true);

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
