<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("contact.admin.title") }}
	</UHeader>

	<UCard class="max-w-6xl mx-auto mt-4">
		<p class="text-muted mb-6">{{ $t("contact.admin.description") }}</p>

		<!-- Filters -->
		<div class="flex flex-wrap gap-4 mb-6 items-center">
			<UInput v-model="searchQuery" :placeholder="$t('contact.admin.search_placeholder')" class="w-64" @input="onSearchInput" />
			<div class="flex gap-2">
				<UButton
					:label="$t('contact.admin.filter_all')"
					:color="readFilter === null ? 'primary' : 'neutral'"
					size="sm"
					@click="setReadFilter(null)"
				/>
				<UButton
					:label="$t('contact.admin.filter_unread')"
					:color="readFilter === false ? 'primary' : 'neutral'"
					size="sm"
					@click="setReadFilter(false)"
				/>
				<UButton
					:label="$t('contact.admin.filter_read')"
					:color="readFilter === true ? 'primary' : 'neutral'"
					size="sm"
					@click="setReadFilter(true)"
				/>
			</div>
		</div>

		<!-- Loading -->
		<div v-if="loading" class="flex justify-center py-12">
			<Spinner class="text-3xl" />
		</div>

		<!-- Empty state -->
		<div v-else-if="messages.length === 0" class="text-center py-12 text-muted">
			{{ $t("contact.admin.no_messages") }}
		</div>

		<!-- Messages list -->
		<template v-else>
			<UTable v-model:expanded="expandedRows" :data="messages" :columns="columns" class="w-full">
				<template #expanded="{ row }">
					<div class="p-4 bg-elevated rounded-lg">
						<p class="text-sm text-muted mb-1">
							<strong>{{ $t("contact.admin.name_column") }}:</strong> {{ row.original.name }}
							&nbsp;|&nbsp;
							<strong>{{ $t("contact.admin.email_column") }}:</strong>
							<a :href="`mailto:${row.original.email}`" class="underline">{{ row.original.email }}</a>
						</p>
						<p class="whitespace-pre-wrap mt-2">{{ row.original.message }}</p>
					</div>
				</template>
			</UTable>

			<!-- Pagination -->
			<div v-if="pagination.total > pagination.per_page" class="flex justify-center gap-2 mt-6 items-center">
				<UButton
					icon="lucide:chevron-left"
					:disabled="pagination.current_page <= 1"
					color="neutral"
					variant="ghost"
					@click="goToPage(pagination.current_page - 1)"
				/>
				<span class="self-center text-muted text-sm">
					{{ pagination.current_page }} / {{ Math.ceil(pagination.total / pagination.per_page) }}
				</span>
				<UButton
					icon="lucide:chevron-right"
					:disabled="pagination.current_page >= Math.ceil(pagination.total / pagination.per_page)"
					color="neutral"
					variant="ghost"
					@click="goToPage(pagination.current_page + 1)"
				/>
			</div>
		</template>
	</UCard>
</template>

<script setup lang="ts">
import { h, onMounted, ref } from "vue";
import { useConfirmDialog } from "@/v8/composables/useConfirmDialog";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Spinner from "@/v8/components/Spinner.vue";
import ContactService from "@/services/contact-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import type { TableColumn } from "@nuxt/ui";
import UButton from "@nuxt/ui/components/Button.vue";
import UCheckbox from "@nuxt/ui/components/Checkbox.vue";

const lycheeStore = useLycheeStateStore();
lycheeStore.load();

type Message = App.Http.Resources.Models.ContactMessageResource;

const messages = ref<Message[]>([]);
const loading = ref(false);
const expandedRows = ref({});
const searchQuery = ref("");
const readFilter = ref<boolean | null>(null);
const pagination = ref({ total: 0, per_page: 20, current_page: 1 });
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const { confirm } = useConfirmDialog();
const toast = useAppToast();

function formatDate(iso: string): string {
	return new Date(iso).toLocaleDateString(undefined, { year: "2-digit", month: "short", day: "numeric" });
}

const columns: TableColumn<Message>[] = [
	{
		accessorKey: "name",
		header: trans("contact.admin.name_column"),
		cell: ({ row }) => h("span", { class: row.original.is_read ? "" : "font-bold" }, row.original.name),
	},
	{
		accessorKey: "email",
		header: trans("contact.admin.email_column"),
		cell: ({ row }) => h("a", { href: `mailto:${row.original.email}`, class: "underline text-muted" }, row.original.email),
	},
	{
		accessorKey: "message",
		header: trans("contact.admin.message_column"),
		cell: ({ row }) => h("span", { class: "text-muted line-clamp-2" }, row.original.message),
	},
	{
		accessorKey: "created_at",
		header: trans("contact.admin.date_column"),
		cell: ({ row }) => h("span", { class: "text-muted text-sm" }, formatDate(row.original.created_at)),
	},
	{
		id: "is_read",
		header: trans("contact.admin.read_column"),
		cell: ({ row }) =>
			h("div", { class: "flex justify-center" }, [
				h(UCheckbox, {
					modelValue: row.original.is_read,
					"onUpdate:modelValue": () => toggleRead(row.original),
				}),
			]),
	},
	{
		id: "actions",
		cell: ({ row }) =>
			h(UButton, {
				icon: "lucide:trash",
				color: "error",
				variant: "ghost",
				size: "sm",
				onClick: () => confirmDelete(row.original),
			}),
	},
];

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

function toggleRead(message: Message): void {
	const newValue = !message.is_read;
	ContactService.markRead(message.id, newValue)
		.then((response) => {
			message.is_read = response.data.is_read;
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("contact.admin.update_error"), life: 3000 });
		});
}

function confirmDelete(message: Message): void {
	confirm({
		title: trans("contact.admin.delete_confirm_header"),
		message: trans("contact.admin.delete_confirm_message"),
		acceptLabel: trans("contact.admin.delete"),
		rejectLabel: trans("contact.admin.cancel"),
		severity: "danger",
	}).then((confirmed) => {
		if (!confirmed) {
			return;
		}
		ContactService.deleteMessage(message.id)
			.then(() => {
				toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("contact.admin.delete_success"), life: 3000 });
				load(pagination.value.current_page);
			})
			.catch(() => {
				toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("contact.admin.delete_error"), life: 3000 });
			});
	});
}

onMounted(() => {
	load();
});
</script>
