<template>
	<UHeader :toggle="false">
		<template #left>
			<GoBack @go-back="backToGallery" />
		</template>
		{{ sprintf($t("webshop.orderDownload.order"), props.orderId) }}
	</UHeader>
	<UCard class="border-0 md:max-w-3xl lg:max-w-5xl xl:max-w-7xl mt-8 mx-auto w-full" :ui="{ header: 'hidden' }">
		<!-- If we are loading, wait. -->
		<div v-if="loading" class="flex justify-center items-center p-8">
			<Spinner />
		</div>

		<!-- If order is undefined. This means that we did not have access, ask for the transaction ID. -->
		<div v-else-if="order === undefined" class="p-6">
			<div class="text-center mb-6">
				<h2 class="text-xl font-semibold mb-2">{{ $t("webshop.orderDownload.orderAccessRequired") }}</h2>
				<p class="text-muted">{{ $t("webshop.orderDownload.provideTransactionId") }}</p>
			</div>
			<div class="max-w-md mx-auto">
				<div class="flex flex-col gap-4">
					<UInput v-model="transactionId" :placeholder="$t('webshop.orderDownload.enterTransactionId')" class="w-full" />
					<UButton
						:label="$t('webshop.orderDownload.loadOrder')"
						:disabled="!transactionId || transactionId.trim() === ''"
						class="w-full justify-center"
						@click="loadOrder"
					/>
				</div>
			</div>
		</div>

		<!-- If order is not undefined, display it. -->
		<div v-else class="p-6">
			<div class="mb-6">
				<h2 class="text-2xl font-bold mb-2">{{ $t("webshop.orderDownload.orderDetails") }}</h2>
				<p class="text-muted">
					{{ $t("webshop.orderDownload.transactionId") }} {{ order.transaction_id }}
					<UIcon
						v-if="order.status === 'closed'"
						name="lucide:copy"
						class="cursor-pointer hover:text-primary ltr:ml-2 rtl:mr-2"
						@click="copyToClipboard"
					/>
				</p>
			</div>

			<div class="grid gap-6">
				<!-- Order Summary -->
				<div class="border rounded-lg p-4 border-default w-full lg:w-1/3">
					<h3 class="text-lg font-semibold mb-3">{{ $t("webshop.orderDownload.orderSummary") }}</h3>
					<div class="space-y-1">
						<div class="flex justify-between">
							<span>{{ $t("webshop.orderDownload.for") }}</span>
							<span class="font-medium"><UsernameEmail :username="order.username" :email="order.email" /></span>
						</div>
						<div class="flex justify-between">
							<span>{{ $t("webshop.orderDownload.status") }}</span>
							<OrderStatus :status="order.status" />
						</div>
						<div class="flex justify-between">
							<span>{{ $t("webshop.orderDownload.total") }}</span>
							<span class="font-medium">{{ order.amount }}</span>
						</div>
						<div class="flex justify-between">
							<span>{{ $t("webshop.orderDownload.paid") }}</span>
							<span>{{ order.paid_at ? new Date(order.paid_at).toLocaleDateString() : $t("webshop.orderDownload.notPaid") }}</span>
						</div>
						<div class="flex justify-between">
							<span>{{ $t("webshop.orderDownload.lastUpdate") }}</span>
							<span>{{ order.updated_at ? new Date(order.updated_at).toLocaleDateString() : "N/A" }}</span>
						</div>
					</div>
				</div>

				<!-- Order Items -->
				<div class="border rounded-lg p-4 border-default">
					<div class="flex justify-between mb-3">
						<h3 class="text-xl font-bold mt-3">{{ $t("webshop.orderDownload.items") }}</h3>
						<UButton
							v-if="initData?.settings.can_edit && itemsToUpdate.length > 0"
							:label="$t('webshop.orderDownload.deliver')"
							icon="lucide:save"
							size="sm"
							@click="markAsDelivered"
						/>
						<UButton
							v-else-if="initData?.settings.can_edit && !edit"
							variant="ghost"
							color="error"
							:label="$t('webshop.orderDownload.edit')"
							icon="lucide:pencil"
							size="sm"
							@click="
								() => {
									edit = !edit;
								}
							"
						/>
						<UButton
							v-else-if="initData?.settings.can_edit && edit"
							variant="ghost"
							color="neutral"
							:label="$t('webshop.orderDownload.view')"
							icon="lucide:eye"
							size="sm"
							@click="
								() => {
									edit = !edit;
								}
							"
						/>
					</div>
					<div class="space-y-3">
						<div v-for="item in order.items" :key="item.id" class="flex justify-between items-start p-3 gap-8 bg-elevated/30 rounded">
							<div class="flex gap-4 items-start w-full">
								<img
									v-if="item.thumb_url"
									:src="item.thumb_url"
									loading="lazy"
									class="w-12 h-12 object-cover rounded shrink-0"
									:alt="item.title"
								/>
								<UIcon v-else name="lucide:image" class="text-muted text-2xl w-12 h-12 flex items-center justify-center shrink-0" />
								<div class="">
									<div class="font-medium">
										<RouterLink :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }">{{
											item.title
										}}</RouterLink>
									</div>
									<div class="text-sm text-highlighted">
										{{ item.album_title ?? $t("webshop.orderDownload.unknownAlbum") }}
									</div>
									<div v-if="item.is_print" class="text-sm text-muted">
										{{ $t("webshop.basketList.printLabel") }}: {{ item.print_width }} × {{ item.print_height }}
										{{ item.print_unit }}, {{ $t("webshop.basketList.paperType") }}: {{ item.print_paper_type }}
									</div>
									<div v-else-if="item.pixel_size_id !== null" class="text-sm text-muted">
										{{ $t("webshop.basketList.pixelLabel") }}: {{ item.pixel_width }} × {{ item.pixel_height }} px,
										{{ $t("webshop.orderSummary.license") }} {{ item.license_type }}
									</div>
									<div v-else class="text-sm text-muted">
										{{ $t("webshop.orderSummary.size") }} {{ item.size_variant_type }}, {{ $t("webshop.orderSummary.license") }}
										{{ item.license_type }}
									</div>
								</div>
								<div v-if="showInput(item)" class="grow max-w-1/2">
									<UInput
										:placeholder="
											item.is_print ? $t('webshop.orderDownload.enterTrackingUrl') : $t('webshop.orderDownload.enterContentUrl')
										"
										class="w-full text-left"
										@update:model-value="
											(v: string | number | bigint | boolean | null | undefined) =>
												updateItemLink({ id: item.id, download_link: typeof v === 'string' ? v : '' })
										"
									/>
								</div>
								<template v-else-if="item.content_url">
									<a
										v-if="item.is_print && item.content_url.startsWith('http')"
										:href="item.content_url"
										target="_blank"
										rel="noopener noreferrer"
									>
										<UButton icon="lucide:truck" :label="$t('webshop.orderDownload.trackShipment')" size="sm" color="primary" />
									</a>
									<div v-else-if="item.is_print" class="flex items-center gap-2 text-sm text-muted">
										<UIcon name="lucide:truck" />
										<span>{{ item.content_url }}</span>
									</div>
									<UButton
										v-else
										icon="lucide:cloud-download"
										:label="$t('webshop.orderDownload.download')"
										size="sm"
										color="primary"
										@click="downloadItem(item.content_url)"
									/>
								</template>
								<div v-else class="mt-1 text-sm text-muted">
									{{
										item.is_print
											? $t("webshop.orderDownload.awaitingShipment")
											: $t("webshop.orderDownload.downloadNotAvailable")
									}}
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
	</UCard>
</template>
<script setup lang="ts">
import OrderStatus from "@/v8/components/webshop/OrderStatus.vue";
import UsernameEmail from "@/v8/components/webshop/UsernameEmail.vue";
import Spinner from "@/v8/components/Spinner.vue";
import Constants from "@/services/constants";
import InitService from "@/services/init-service";
import WebshopService, { ItemLink } from "@/services/webshop-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";
import { storeToRefs } from "pinia";
import { useAppToast } from "@/v8/composables/useAppToast";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import GoBack from "@/v8/components/headers/GoBack.vue";

const props = defineProps<{
	orderId: string;
	transactionId?: string;
}>();

const toast = useAppToast();
const router = useRouter();
const orderId = ref(props.orderId);
const transactionId = ref<string | undefined>(props.transactionId);
const order = ref<App.Http.Resources.Shop.OrderResource | undefined>(undefined);
const loading = ref(true);
const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);
const edit = ref(false);

function backToGallery() {
	router.push({ name: "gallery" });
}

function loadOrder() {
	loading.value = true;
	WebshopService.Order.get(parseInt(orderId.value, 10), transactionId.value)
		.then((response) => {
			order.value = response.data;
			loading.value = false;
			router.push({ name: "order", params: { orderId: orderId.value, transactionId: transactionId.value } });
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
	link.rel = "noopener noreferrer";
	link.target = "_blank";
	link.download = ""; // This will use the filename from the server
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
}

function copyToClipboard() {
	if (navigator.clipboard?.writeText) {
		navigator.clipboard
			.writeText(
				Constants.BASE_URL +
					router.resolve({ name: "order", params: { orderId: order.value?.id, transactionId: order.value?.transaction_id } }).href,
			)
			.then(() => {
				toast.add({
					severity: "success",
					summary: trans("webshop.orderDownload.copiedToClipboard"),
					detail: trans("webshop.orderDownload.orderLinkCopied"),
					life: 3000,
				});
			})
			.catch(() => {
				// Fallback if clipboard write fails
				toast.add({
					severity: "error",
					summary: trans("toasts.error"),
					detail: trans("webshop.orderDownload.couldNotCopy"),
					life: 3000,
				});
			});
	} else {
		toast.add({
			severity: "error",
			summary: trans("toasts.error"),
			detail: trans("webshop.orderDownload.couldNotCopy"),
			life: 3000,
		});
	}
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
		WebshopService.Order.markAsDelivered(order.value.id, itemsToUpdate.value)
			.then(() => {
				itemsToUpdate.value = [];
				loadOrder();
			})
			.catch((error) => {
				console.error("Error marking items as delivered:", error);
				toast.add({
					severity: "error",
					summary: trans("webshop.orderDownload.somethingWentWrong"),
					detail: trans("webshop.orderDownload.couldNotMarkDelivered"),
					life: 3000,
				});
			});
	}
}

onMounted(() => {
	loadOrder();
	load();
});
</script>
