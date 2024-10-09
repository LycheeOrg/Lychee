<template>
	<Toolbar class="w-full border-0 h-14">
		<template #start>
			<router-link :to="{ name: 'gallery' }">
				<Button icon="pi pi-angle-left" class="mr-2" severity="secondary" text />
			</router-link>
		</template>

		<template #center>
			{{ "Statistics" }}
		</template>

		<template #end> </template>
	</Toolbar>
	<Panel v-if="sizeVariantSpaceMeter" class="max-w-5xl mx-auto border-0">
		<MeterGroup :value="sizeVariantSpaceMeter">
			<template #label="{ value }">
				<div class="flex flex-wrap gap-4">
					<template v-for="val of value" :key="val.label">
						<Card class="flex-1 border border-surface shadow-none">
							<template #content>
								<div class="flex justify-between gap-8">
									<div class="flex gap-1 flex-col">
										<span class="text-sm"
											><span class="rounded-full h-3 w-3 inline-block mr-2" :style="'background-color: ' + val.color"></span
											>{{ val.label }}</span
										>
										<span class="font-bold text-base">{{ val.size }}</span>
									</div>
								</div>
							</template>
						</Card>
					</template>
				</div>
			</template>
		</MeterGroup>
	</Panel>

	<Panel class="max-w-5xl mx-auto border-0">
		<div class="py-4">
			<ToggleSwitch v-model="is_collapsed" class="text-sm"></ToggleSwitch> {{ "Collapse albums sizes" }}
		</div>
		<DataTable :value="albumData" size="small" scrollable scrollHeight="600px" :loading="albumData === undefined" >
			<Column field="username" header="Owner" class="w-32">
			</Column>
			<Column field="title" sortable header="Title"></Column>
			<Column field="num_photos" sortable header="Photos" class="w-16"></Column>
			<Column field="num_descendants" sortable  header="Children" class="w-16"></Column>
			<Column field="size" header="Size" sortable  class="w-32">
				<template #body="slotProps">{{ sizeToUnit(slotProps.data.size) }}</template>
			</Column>
		</DataTable>
	</Panel>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import Panel from "primevue/panel";
import StatisticsService from "@/services/statistics-service";
import { useAuthStore } from "@/stores/Auth";
import { computed, ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import { sizeToUnit, sizeVariantToColour } from "@/utils/StatsSizeVariantToColours";
import MeterGroup from "primevue/metergroup";
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import ToggleSwitch from "primevue/toggleswitch";

const router = useRouter();
const user = ref(undefined as undefined | App.Http.Resources.Models.UserResource);
const authStore = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();

const sizeVariantSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Sizes[]);
const albumSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Album[]);
const totalAlbumSpace = ref(undefined as undefined | App.Http.Resources.Statistics.Album[]);
const sizeVariantSpaceMeter = ref();
const is_collapsed = ref(false);

const albumData = computed(() => {
	if (is_collapsed.value === false) {
		return albumSpace.value?.filter((a) => !a.is_nsfw || are_nsfw_visible.value);
	}
	return totalAlbumSpace.value?.filter((a) => !a.is_nsfw || are_nsfw_visible.value);
});

const { is_se_preview_enabled, are_nsfw_visible } = storeToRefs(lycheeStore);

authStore.getUser().then((data) => {
	user.value = data;

	console.log(user.value);
	// Not logged in. Bye.
	if (user.value.id === null) {
		router.push({ name: "gallery" });
	}

	if (is_se_preview_enabled.value === true) {
		// Input dummy data.
	} else {
		loadSizeVariantSpace();
		loadAlbumSpace();
		loadTotalAlbumSpace();
	}
});

function loadSizeVariantSpace() {
	StatisticsService.getSizeVariantSpace().then((response) => {
		sizeVariantSpace.value = response.data;
		prepSizeVariantDonut();
	});
}

function prepSizeVariantDonut() {
	if (sizeVariantSpace.value === undefined) {
		return;
	}

	const total = sizeVariantSpace.value.reduce((acc, sv) => acc + sv.size, 0);
	sizeVariantSpaceMeter.value = sizeVariantSpace.value.map((sv: App.Http.Resources.Statistics.Sizes) => {
		return {
			label: sv.label,
			value: (sv.size / total) * 100,
			size: sizeToUnit(sv.size),
			color: sizeVariantToColour(sv.type),
		};
	});
}
function loadAlbumSpace() {
	StatisticsService.getAlbumSpace().then((response) => {
		albumSpace.value = response.data;
		console.log(albumSpace.value);
	});
}

function loadTotalAlbumSpace() {
	StatisticsService.getTotalAlbumSpace().then((response) => {
		totalAlbumSpace.value = response.data;
		console.log(response.data);
	});
}
</script>
