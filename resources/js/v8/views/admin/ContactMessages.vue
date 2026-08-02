<template>
	<ContactMessageDeleteDialog
		v-if="deletingMessage !== undefined"
		v-model:open="isDeleteDialogVisible"
		:message="deletingMessage"
		@deleted="
			deletingMessage = undefined;
			load(pagination.current_page);
		"
	/>

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
					variant="solid"
					:label="$t('contact.admin.filter_all')"
					:color="readFilter === null ? 'primary' : 'neutral'"
					size="sm"
					@click="setReadFilter(null)"
				/>
				<UButton
					variant="solid"
					:label="$t('contact.admin.filter_unread')"
					:color="readFilter === false ? 'primary' : 'neutral'"
					size="sm"
					@click="setReadFilter(false)"
				/>
				<UButton
					variant="solid"
					:label="$t('contact.admin.filter_read')"
					:color="readFilter === true ? 'primary' : 'neutral'"
					size="sm"
					@click="setReadFilter(true)"
				/>
			</div>
		</div>

		<!-- Loading -->
		<div v-if="loading" class="flex justify-center py-12">
			<LycheeLoadingIcon fast class="text-3xl" />
		</div>

		<!-- Empty state -->
		<div v-else-if="messages.length === 0" class="text-center py-12 text-muted">
			{{ $t("contact.admin.no_messages") }}
		</div>

		<!-- Messages list -->
		<template v-else>
			<div class="overflow-x-auto">
				<UTable
					v-model:expanded="expandedRows"
					:data="messages"
					:columns="columns"
					:on-select="(_e: Event, row: TableRow<Message>) => row.toggleExpanded()"
					class="w-full"
					:ui="{ td: 'px-2 py-1', th: 'px-2' }"
				>
					<template #name-cell="{ row }">
						<span :class="row.original.is_read ? '' : 'font-bold text-primary'">{{ row.original.name }}</span>
					</template>
					<template #email-cell="{ row }">
						<a :href="`mailto:${row.original.email}`" class="underline text-muted">{{ row.original.email }}</a>
					</template>
					<template #message-cell="{ row }">
						<span class="text-muted line-clamp-1">{{ row.original.message }}</span>
					</template>
					<template #created_at-cell="{ row }">
						<span class="text-muted text-sm">{{ formatDate(row.original.created_at) }}</span>
					</template>
					<template #is_read-cell="{ row }">
						<UCheckbox :model-value="row.original.is_read" @update:model-value="() => toggleRead(row.original)" />
					</template>
					<template #actions-cell="{ row }">
						<UButton icon="lucide:trash" color="error" variant="ghost" size="sm" @click="openDeleteModal(row.original)" />
					</template>
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
			</div>

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
import { onMounted, ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import { trans } from "laravel-vue-i18n";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import LycheeLoadingIcon from "@/v8/components/LycheeLoadingIcon.vue";
import ContactMessageDeleteDialog from "@/v8/components/forms/contact/ContactMessageDeleteDialog.vue";
import ContactService from "@/services/contact-service";
import { useLycheeStateStore } from "@/stores/LycheeState";
import type { TableColumn, TableRow } from "@nuxt/ui";
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
const deletingMessage = ref<Message | undefined>(undefined);
const isDeleteDialogVisible = ref(false);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const toast = useAppToast();

function formatDate(iso: string): string {
	return new Date(iso).toLocaleDateString(undefined, { year: "2-digit", month: "short", day: "numeric" });
}

const columns: TableColumn<Message>[] = [
	{ accessorKey: "name", header: trans("contact.admin.name_column") },
	{ accessorKey: "email", header: trans("contact.admin.email_column") },
	{ accessorKey: "message", header: trans("contact.admin.message_column") },
	{ accessorKey: "created_at", header: trans("contact.admin.date_column") },
	{ id: "is_read", header: trans("contact.admin.read_column") },
	{ id: "actions" },
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

function openDeleteModal(message: Message): void {
	deletingMessage.value = message;
	isDeleteDialogVisible.value = true;
}

onMounted(() => {
	load();
});
</script>
