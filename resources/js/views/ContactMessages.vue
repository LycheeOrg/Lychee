<template>
	<ConfirmDialog />
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("contact.admin.title") }}
		</template>
		<template #end></template>
	</Toolbar>

	<Panel class="border-0 max-w-6xl mx-auto mt-4">
		<p class="text-muted-color mb-6">{{ $t("contact.admin.description") }}</p>

		<!-- Filters -->
		<div class="flex flex-wrap gap-4 mb-6 items-center">
			<InputText v-model="searchQuery" :placeholder="$t('contact.admin.search_placeholder')" class="w-64!" @input="onSearchInput" />
			<div class="flex gap-2">
				<Button
					:label="$t('contact.admin.filter_all')"
					:severity="readFilter === null ? 'primary' : 'secondary'"
					size="small"
					class="border-none"
					@click="setReadFilter(null)"
				/>
				<Button
					:label="$t('contact.admin.filter_unread')"
					:severity="readFilter === false ? 'primary' : 'secondary'"
					size="small"
					class="border-none"
					@click="setReadFilter(false)"
				/>
				<Button
					:label="$t('contact.admin.filter_read')"
					:severity="readFilter === true ? 'primary' : 'secondary'"
					size="small"
					class="border-none"
					@click="setReadFilter(true)"
				/>
			</div>
		</div>

		<!-- Loading -->
		<div v-if="loading" class="flex justify-center py-12">
			<ProgressSpinner />
		</div>

		<!-- Empty state -->
		<div v-else-if="messages.length === 0" class="text-center py-12 text-muted-color">
			{{ $t("contact.admin.no_messages") }}
		</div>

		<!-- Messages list -->
		<template v-else>
			<DataTable :value="messages" :row-class="rowClass" expandable-rows v-model:expanded-rows="expandedRows" data-key="id" class="w-full">
				<Column header-class="w-1/4" :header="$t('contact.admin.name_column')">
					<template #body="slotProps">
						<span :class="{ 'font-bold': !slotProps.data.is_read }">{{ slotProps.data.name }}</span>
					</template>
				</Column>
				<Column header-class="w-1/4" :header="$t('contact.admin.email_column')">
					<template #body="slotProps">
						<a :href="`mailto:${slotProps.data.email}`" class="underline text-muted-color">{{ slotProps.data.email }}</a>
					</template>
				</Column>
				<Column header-class="w-1/3" :header="$t('contact.admin.message_column')">
					<template #body="slotProps">
						<span class="text-muted-color line-clamp-2">{{ slotProps.data.message }}</span>
					</template>
				</Column>
				<Column header-class="w-20" :header="$t('contact.admin.date_column')">
					<template #body="slotProps">
						<span class="text-muted-color text-sm">{{ formatDate(slotProps.data.created_at) }}</span>
					</template>
				</Column>
				<Column header-class="w-16 text-center" :header="$t('contact.admin.read_column')">
					<template #body="slotProps">
						<div class="flex justify-center">
							<Checkbox :model-value="slotProps.data.is_read" binary @change="toggleRead(slotProps.data)" />
						</div>
					</template>
				</Column>
				<Column header-class="w-12">
					<template #body="slotProps">
						<Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="confirmDelete(slotProps.data)" />
					</template>
				</Column>
				<template #expansion="slotProps">
					<div class="p-4 bg-surface-50 dark:bg-surface-900 rounded-lg">
						<p class="text-sm text-muted-color mb-1">
							<strong>{{ $t("contact.admin.name_column") }}:</strong> {{ slotProps.data.name }}
							&nbsp;|&nbsp;
							<strong>{{ $t("contact.admin.email_column") }}:</strong>
							<a :href="`mailto:${slotProps.data.email}`" class="underline">{{ slotProps.data.email }}</a>
						</p>
						<p class="whitespace-pre-wrap mt-2">{{ slotProps.data.message }}</p>
					</div>
				</template>
			</DataTable>

			<!-- Pagination -->
			<div v-if="pagination.total > pagination.per_page" class="flex justify-center gap-2 mt-6">
				<Button
					icon="pi pi-chevron-left"
					:disabled="pagination.current_page <= 1"
					severity="secondary"
					text
					@click="goToPage(pagination.current_page - 1)"
				/>
				<span class="self-center text-muted-color text-sm">
					{{ pagination.current_page }} / {{ Math.ceil(pagination.total / pagination.per_page) }}
				</span>
				<Button
					icon="pi pi-chevron-right"
					:disabled="pagination.current_page >= Math.ceil(pagination.total / pagination.per_page)"
					severity="secondary"
					text
					@click="goToPage(pagination.current_page + 1)"
				/>
			</div>
		</template>
	</Panel>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import Column from "primevue/column";
import ConfirmDialog from "primevue/confirmdialog";
import DataTable from "primevue/datatable";
import Panel from "primevue/panel";
import ProgressSpinner from "primevue/progressspinner";
import Toolbar from "primevue/toolbar";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import ContactService from "@/services/contact-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import InputText from "@/components/forms/basic/InputText.vue";

const lycheeStore = useLycheeStateStore();
lycheeStore.load();

const messages = ref<App.Http.Resources.Models.ContactMessageResource[]>([]);
const loading = ref(false);
const expandedRows = ref<Record<number, boolean>>({});
const searchQuery = ref("");
const readFilter = ref<boolean | null>(null);
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const confirm = useConfirm();
const toast = useToast();

function formatDate(iso: string): string {
	return new Date(iso).toLocaleDateString(undefined, { year: "2-digit", month: "short", day: "numeric" });
}

function rowClass(data: App.Http.Resources.Models.ContactMessageResource): string {
	return data.is_read ? "opacity-60" : "";
}

function load(page = 1): void {
	loading.value = true;
	ContactService.list({
		page,
		per_page: pagination.value.per_page,
		...(readFilter.value !== null ? { is_read: readFilter.value } : {}),
		...(searchQuery.value.trim() !== "" ? { search: searchQuery.value.trim() } : {}),
	})
		.then((response) => {
			messages.value = response.data.data;
			pagination.value.current_page = response.data.current_page;
			pagination.value.total = response.data.total;
			pagination.value.per_page = response.data.per_page;
		})
		.finally(() => {
			loading.value = false;
		});
}

function setReadFilter(value: boolean | null): void {
	readFilter.value = value;
	load(1);
}

function onSearchInput(): void {
	if (searchTimeout !== null) {
		clearTimeout(searchTimeout);
	}
	searchTimeout = setTimeout(() => {
		load(1);
	}, 400);
}

function goToPage(page: number): void {
	load(page);
}

function toggleRead(message: App.Http.Resources.Models.ContactMessageResource): void {
	const newValue = !message.is_read;
	ContactService.markRead(message.id, newValue)
		.then((response) => {
			message.is_read = response.data.is_read;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("contact.admin.update_error"), life: 3000 });
		});
}

function confirmDelete(message: App.Http.Resources.Models.ContactMessageResource): void {
	confirm.require({
		message: trans("contact.admin.delete_confirm_message"),
		header: trans("contact.admin.delete_confirm_header"),
		icon: "pi pi-exclamation-triangle",
		rejectClass: "p-button-secondary p-button-outlined",
		rejectLabel: trans("contact.admin.cancel"),
		acceptLabel: trans("contact.admin.delete"),
		accept: () => {
			ContactService.deleteMessage(message.id)
				.then(() => {
					toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("contact.admin.delete_success"), life: 3000 });
					load(pagination.value.current_page);
					confirm.close();
				})
				.catch(() => {
					toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("contact.admin.delete_error"), life: 3000 });
				});
		},
	});
}

onMounted(() => {
	load();
});
</script>
