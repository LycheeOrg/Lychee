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
	<Panel v-if="is_se_preview_enabled" class="text-center">
		This is a preview of the upcoming statistics page.<br />
		The data shown here are randomly generated and do not reflect your server.
	</Panel>
	<SizeVariantMeter v-if="load" />
	<Panel class="max-w-5xl mx-auto border-0" :pt:header:class="'hidden'">
		<TotalCard v-if="total" :total="total" />
		<div class="py-4" v-if="load && total">
			<ToggleSwitch v-model="is_collapsed" class="text-sm"></ToggleSwitch> {{ "Collapse albums sizes" }}
		</div>
		<AlbumsTable v-if="load" v-show="!is_collapsed" :show-username="true" :is-total="false" :album-id="undefined" @total="total = $event" />
		<AlbumsTable v-if="load" v-show="is_collapsed" :show-username="true" :is-total="true" :album-id="undefined" />
	</Panel>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Toolbar from "primevue/toolbar";
import Panel from "primevue/panel";
import { useAuthStore } from "@/stores/Auth";
import { ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import ToggleSwitch from "primevue/toggleswitch";
import { onKeyStroke } from "@vueuse/core";
import { shouldIgnoreKeystroke } from "@/utils/keybindings-utils";
import SizeVariantMeter from "@/components/statistics/SizeVariantMeter.vue";
import TotalCard, { TotalAlbum } from "@/components/statistics/TotalCard.vue";
import AlbumsTable from "@/components/statistics/AlbumsTable.vue";

const router = useRouter();
const user = ref(undefined as undefined | App.Http.Resources.Models.UserResource);
const authStore = useAuthStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.init();

const total = ref(undefined as undefined | TotalAlbum);
const is_collapsed = ref(false);
const load = ref(false);

const { is_se_preview_enabled, are_nsfw_visible } = storeToRefs(lycheeStore);

authStore.getUser().then((data) => {
	user.value = data;

	// Not logged in. Bye.
	if (user.value.id === null) {
		router.push({ name: "gallery" });
	}

	load.value = true;
});

onKeyStroke("h", () => !shouldIgnoreKeystroke() && (are_nsfw_visible.value = !are_nsfw_visible.value));
</script>
