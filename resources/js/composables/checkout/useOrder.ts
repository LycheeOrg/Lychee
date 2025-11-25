import MaintenanceService from "@/services/maintenance-service";
import WebshopService from "@/services/webshop-service";
import { ToastServiceMethods } from "primevue/toastservice";
import { ref } from "vue";
import { Router } from "vue-router";

const orders = ref<App.Http.Resources.Shop.OrderResource[] | undefined>(undefined);
const numOldOrders = ref<number>(0);

export function useOrder(toast: ToastServiceMethods, router: Router) {
	function load() {
		return Promise.all([
			WebshopService.Order.list()
				.then((response) => {
					orders.value = response.data;
				})
				.catch((err) => {
					if (err.status === 401 || err.status === 403) {
						router.push({ name: "login" });
					}
				}),
			MaintenanceService.oldOrdersCheck().then((response) => {
				numOldOrders.value = response.data;
			}),
		]);
	}

	function markAsPaid(orderId: number) {
		WebshopService.Order.markAsPaid(orderId).then(() => {
			load();
		});
	}

	function markAsDelivered(orderId: number) {
		WebshopService.Order.markAsDelivered(orderId, []).then(async () => {
			await load();
			if (orders.value?.find((order) => order.id === orderId)) {
			}
		});
	}

	function clean() {
		MaintenanceService.oldOrdersDo().then(() => {
			load();
		});
	}

	function canOpen(order: App.Http.Resources.Shop.OrderResource): boolean {
		return !["pending", "cancelled", "failed", "refunded"].includes(order.status);
	}

	function openOrder(order: App.Http.Resources.Shop.OrderResource) {
		if (!canOpen(order)) {
			return;
		}
		router.push({ name: "order", params: { orderId: order.id, transactionId: order.transaction_id } });
	}

	// Return true if the date is older than 2 weeks
	function isStale(order: App.Http.Resources.Shop.OrderResource): boolean {
		if (order.created_at === null) {
			return true;
		}
		if (order.status !== "pending" || order.username !== null || (order.items?.length ?? 0) > 0) {
			return false;
		}
		const twoWeeksAgo = new Date();
		twoWeeksAgo.setDate(twoWeeksAgo.getDate() - 14);
		return new Date(order.created_at) < twoWeeksAgo;
	}

	function isZero(string: string): boolean {
		return string.substring(1) === "0.00";
	}

	function copyTransactionIdToClipboard(transactionId: string) {
		toast.add({ severity: "info", summary: "Copied to clipboard", detail: "Transaction ID copied to clipboard", life: 3000 });
		navigator.clipboard.writeText(transactionId);
	}

	function requireAttention(order: App.Http.Resources.Shop.OrderResource): boolean {
		if (!canOpen(order)) {
			return false;
		}

		if (order.status === "offline") {
			return false;
		}

		return (
			order.items?.some((item) => {
				return item.content_url === null || item.content_url === "";
			}) ?? false
		);
	}

	return {
		isStale,
		canOpen,
		openOrder,
		isZero,
		copyTransactionIdToClipboard,
		load,
		markAsPaid,
		markAsDelivered,
		requireAttention,
		clean,
		orders,
		numOldOrders,
	};
}
