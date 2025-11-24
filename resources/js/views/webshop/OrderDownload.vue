<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>

		<template #center>
			{{ "Order " + props.orderId }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel :pt:header:class="'hidden'" class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-8 mx-auto w-full">
		<!-- If we are loading, wait. -->
		<div v-if="loading" class="flex justify-center items-center p-8">
			<ProgressSpinner style="width: 50px; height: 50px" strokeWidth="4" />
		</div>

		<!-- If order is undefined. This means that we did not have access, ask for the transaction ID. -->
		<div v-else-if="order === undefined" class="p-6">
			<div class="text-center mb-6">
				<h2 class="text-xl font-semibold mb-2">Order Access Required</h2>
				<p class="text-muted-color">Please provide the transaction ID to access your order details.</p>
			</div>
			<div class="max-w-md mx-auto">
				<div class="flex flex-col gap-4">
					<InputText v-model="transactionId" placeholder="Enter transaction ID" class="w-full" />
					<Button @click="loadOrder" label="Load Order" :disabled="!transactionId || transactionId.trim() === ''" class="w-full" />
				</div>
			</div>
		</div>

		<!-- If order is not undefined, display it. -->
		<div v-else class="p-6">
			<div class="mb-6">
				<h2 class="text-2xl font-bold mb-2">Order Details</h2>
				<p class="text-muted-color">
					Transaction ID: {{ order.transaction_id }}
					<i
						v-if="order.status === 'closed'"
						class="pi pi-copy cursor-pointer hover:text-primary-400 ltr:ml-2 rtl:mr-2"
						@click="copyToClipboard"
					/>
				</p>
			</div>

			<div class="grid gap-6">
				<!-- Order Summary -->
				<div class="border rounded-lg p-4 border-surface-50/20">
					<h3 class="text-lg font-semibold mb-3">Order Summary</h3>
					<div class="space-y-2">
						<div class="flex justify-between">
							<span>For:</span>
							<span class="font-medium"><UsernameEmail :username="order.username" :email="order.email" /></span>
						</div>
						<div class="flex justify-between">
							<span>Status:</span>
							<OrderStatus :status="order.status" />
						</div>
						<div class="flex justify-between">
							<span>Total:</span>
							<span class="font-medium">{{ order.amount }}</span>
						</div>
						<div class="flex justify-between">
							<span>Paid:</span>
							<span>{{ order.paid_at ? new Date(order.paid_at).toLocaleDateString() : "not paid" }}</span>
						</div>
						<div class="flex justify-between">
							<span>Last update:</span>
							<span>{{ order.updated_at ? new Date(order.updated_at).toLocaleDateString() : "N/A" }}</span>
						</div>
					</div>
				</div>

				<!-- Order Items -->
				<div class="border rounded-lg p-4 border-surface-50/20">
					<h3 class="text-lg font-medium mb-3">Items</h3>
					<div class="space-y-3">
						<div v-for="item in order.items" :key="item.id" class="flex justify-between items-center p-3 bg-surface-50/5 rounded">
							<div class="flex gap-4">
								<div>
									<div class="font-medium">
										<RouterLink :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }">{{
											item.title
										}}</RouterLink>
									</div>
									<div class="text-sm text-muted-color">{{ item.size_variant_type }} - {{ item.license_type }}</div>
								</div>
								<div v-if="item.content_url" class="mt-1">
									<Button
										@click="downloadItem(item.content_url)"
										icon="pi pi-cloud-download"
										label="Download"
										size="small"
										class="border-0"
										severity="primary"
									/>
								</div>
							</div>
							<div class="text-right">
								<div class="font-medium">{{ item.price }}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</Panel>
</template>
<script setup lang="ts">
import InputText from "@/components/forms/basic/InputText.vue";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import OrderStatus from "@/components/webshop/OrderStatus.vue";
import Constants from "@/services/constants";
import WebshopService from "@/services/webshop-service";
import Button from "primevue/button";
import Panel from "primevue/panel";
import ProgressSpinner from "primevue/progressspinner";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import UsernameEmail from "./UsernameEmail.vue";

const props = defineProps<{
	orderId: string;
	transactionId?: string;
}>();

const toast = useToast();
const router = useRouter();
const orderId = ref(props.orderId);
const transactionId = ref<string | undefined>(props.transactionId);
const order = ref<App.Http.Resources.Shop.OrderResource | undefined>(undefined);
const loading = ref(true);

function loadOrder() {
	WebshopService.Order.get(parseInt(orderId.value, 10), transactionId.value)
		.then((response) => {
			order.value = response.data;
			loading.value = false;
		})
		.catch((error) => {
			console.log(error);
			order.value = undefined;
			loading.value = false;
		});
}

function downloadItem(contentUrl: string) {
	// Create a temporary anchor element to trigger download
	const link = document.createElement("a");
	link.href = contentUrl;
	link.target = "_blank";
	link.download = ""; // This will use the filename from the server
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
}

// Load transaction Id from the # value of the url
function loadTransactionId() {
	const hash = window.location.hash;
	if (hash && hash.length > 1) {
		transactionId.value = hash.substring(1);
	}
}

function copyToClipboard() {
	toast.add({ severity: "success", summary: "Copied to clipboard", detail: "Order link copied to clipboard", life: 3000 });
	navigator.clipboard.writeText(
		Constants.BASE_URL + router.resolve({ name: "order", params: { orderId: order.value?.id, transactionId: order.value?.transaction_id } }).href,
	);
}

onMounted(() => {
	loadTransactionId();
	loadOrder();
});
</script>
