<template>
	<Toolbar class="w-full border-0 h-14 rounded-none">
		<template #start>
			<OpenLeftMenu />
		</template>
		<template #center>
			{{ $t("moderation.title") }}
		</template>
		<template #end>
			<Button
				icon="pi pi-check-square"
				class="border-none"
				size="small"
				:label="$t('moderation.approve_selected')"
				:disabled="selectedPhotos.length === 0"
				@click="approveSelected"
			/>
		</template>
	</Toolbar>

	<Panel class="border-0 max-w-5xl mx-auto mt-4">
		<p class="text-muted-color mb-6 text-center">{{ $t("moderation.description") }}</p>

		<!-- Loading -->
		<div v-if="loading" class="flex justify-center py-12">
			<ProgressSpinner />
		</div>

		<!-- Empty state -->
		<div v-else-if="photos.length === 0" class="text-center py-12">
			<div class="text-muted-color mb-4">
				<i class="pi pi-check-circle text-4xl"></i>
			</div>
			<p class="text-muted-color">{{ $t("moderation.no_pending") }}</p>
		</div>

		<!-- Photos table -->
		<DataTable v-else v-model:selection="selectedPhotos" :value="photos" data-key="photo_id" class="w-full">
			<Column selection-mode="multiple" header-class="w-12" />
			<Column :header="$t('moderation.col_thumbnail')">
				<template #body="slotProps">
					<img
						v-if="slotProps.data.thumb_url"
						:src="slotProps.data.thumb_url"
						class="w-16 h-16 object-cover rounded"
						:alt="slotProps.data.title"
					/>
					<i v-else class="pi pi-image text-2xl text-muted-color" />
				</template>
			</Column>
			<Column field="title" :header="$t('moderation.col_title')" />
			<Column field="owner_username" :header="$t('moderation.col_owner')" />
			<Column field="album_title" :header="$t('moderation.col_album')" />
			<Column :header="$t('moderation.col_uploaded')">
				<template #body="slotProps">
					{{ new Date(slotProps.data.created_at).toLocaleDateString() }}
				</template>
			</Column>
		</DataTable>

		<!-- Pagination -->
		<div v-if="lastPage > 1" class="flex justify-center mt-4">
			<Paginator :first="(currentPage - 1) * perPage" :rows="perPage" :total-records="total" @page="onPageChange" />
		</div>
	</Panel>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Panel from "primevue/panel";
import Toolbar from "primevue/toolbar";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import ProgressSpinner from "primevue/progressspinner";
import { useToast } from "primevue/usetoast";
import OpenLeftMenu from "@/components/headers/OpenLeftMenu.vue";
import ModerationService from "@/services/moderation-service";
import { trans } from "laravel-vue-i18n";

const toast = useToast();

const loading = ref(false);
const photos = ref<App.Http.Resources.Models.ModerationResource[]>([]);
const selectedPhotos = ref<App.Http.Resources.Models.ModerationResource[]>([]);
const currentPage = ref(1);
const lastPage = ref(1);
const perPage = ref(30);
const total = ref(0);

function load(page: number = 1) {
	loading.value = true;
	ModerationService.list(page, perPage.value)
		.then((response) => {
			photos.value = response.data.photos;
			currentPage.value = response.data.current_page;
			lastPage.value = response.data.last_page;
			perPage.value = response.data.per_page;
			total.value = response.data.total;
			selectedPhotos.value = [];
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_pending"), life: 3000 });
		})
		.finally(() => {
			loading.value = false;
		});
}

function approveSelected() {
	const ids = selectedPhotos.value.map((p) => p.photo_id);
	ModerationService.approve(ids)
		.then(() => {
			toast.add({ severity: "success", summary: trans("toasts.success"), detail: trans("moderation.approved"), life: 3000 });
			load(currentPage.value);
		})
		.catch(() => {
			toast.add({ severity: "error", summary: trans("toasts.error"), detail: trans("moderation.no_pending"), life: 3000 });
		});
}

function onPageChange(event: { page: number }) {
	load(event.page + 1);
}

load();
</script>
