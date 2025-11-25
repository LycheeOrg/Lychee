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
				<div class="border rounded-lg p-4 border-surface-50/20 w-full lg:w-1/3">
					<h3 class="text-lg font-semibold mb-3">Order Summary</h3>
					<div class="space-y-1">
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
					<div class="flex justify-between mb-3">
						<h3 class="text-xl font-bold mt-3">Items</h3>
						<Button
							v-if="initData?.settings.can_edit && itemsToUpdate.length > 0"
							@click="markAsDelivered"
							label="Deliver"
							icon="pi pi-save"
							size="small"
							class="border-0"
						/>
						<Button
							text
							v-else-if="initData?.settings.can_edit && !edit"
							@click="edit = !edit"
							severity="danger"
							label="Edit"
							icon="pi pi-pencil"
							size="small"
							class="border-0"
						/>
						<Button
							text
							v-else-if="initData?.settings.can_edit && edit"
							@click="edit = !edit"
							severity="secondary"
							label="View"
							icon="pi pi-eye"
							size="small"
							class="border-0"
						/>
					</div>
					<div class="space-y-3">
						<div v-for="item in order.items" :key="item.id" class="flex justify-between items-center p-3 gap-8 bg-surface-50/5 rounded">
							<div class="flex gap-4 items-center w-full">
								<div class="">
									<div class="font-medium">
										<RouterLink :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }">{{
											item.title
										}}</RouterLink>
									</div>
									<div class="text-sm text-muted-color">{{ item.size_variant_type }} - {{ item.license_type }}</div>
								</div>
								<div v-if="showInput(item)" class="grow max-w-1/2">
									<InputText
										placeholder="Enter content URL here."
										class="w-full text-left"
										@update:modelValue="(v) => updateItemLink({ id: item.id, download_link: v ?? '' })"
									/>
								</div>
								<div v-else-if="item.content_url">
									<Button
										@click="downloadItem(item.content_url)"
										icon="pi pi-cloud-download"
										label="Download"
										size="small"
										class="border-0"
										severity="primary"
									/>
								</div>
								<div v-else class="mt-1 text-sm text-muted-color">Download not available (yet)</div>
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
import UsernameEmail from "@/components/webshop/UsernameEmail.vue";
import Constants from "@/services/constants";
import InitService from "@/services/init-service";
import WebshopService, { ItemLink } from "@/services/webshop-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";
import Button from "primevue/button";
import Panel from "primevue/panel";
import ProgressSpinner from "primevue/progressspinner";
import Toolbar from "primevue/toolbar";
import { useToast } from "primevue/usetoast";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";

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
const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);
const edit = ref(false);

function loadOrder() {
	router.push({ name: "order", params: { orderId: orderId.value, transactionId: transactionId.value } });
	WebshopService.Order.get(parseInt(orderId.value, 10), transactionId.value)
		.then((response) => {
			order.value = response.data;
			loading.value = false;
		})
		.catch(() => {
			order.value = undefined;
			loading.value = false;
		});
}

async function load(): Promise<void> {
	return InitService.fetchGlobalRights().then((data) => {
		initData.value = data.data;
	});
}

function showInput(item: App.Http.Resources.Shop.OrderItemResource): boolean {
	return (initData.value?.settings.can_edit ?? false) && (!item.content_url || edit.value);
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

function copyToClipboard() {
	toast.add({ severity: "success", summary: "Copied to clipboard", detail: "Order link copied to clipboard", life: 3000 });
	navigator.clipboard.writeText(
		Constants.BASE_URL + router.resolve({ name: "order", params: { orderId: order.value?.id, transactionId: order.value?.transaction_id } }).href,
	);
}

const itemsToUpdate = ref<ItemLink[]>([]);

function updateItemLink(link: ItemLink) {
	const index = itemsToUpdate.value.findIndex((item) => item.id === link.id);
	// Empty value = remove
	if (index !== -1 && link.download_link.trim() === "") {
		itemsToUpdate.value.splice(index, 1);
		return;
	}

	// Found = update
	if (index !== -1) {
		itemsToUpdate.value[index] = link;
		// Not found = add
	} else {
		itemsToUpdate.value.push(link);
	}
}

function markAsDelivered() {
	if (order.value) {
		WebshopService.Order.markAsDelivered(order.value.id, itemsToUpdate.value).then(() => {
			itemsToUpdate.value = [];
			loadOrder();
		});
	}
}

onMounted(() => {
	loadOrder();
	load();
});
</script>
