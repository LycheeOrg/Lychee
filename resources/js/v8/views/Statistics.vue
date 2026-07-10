<template>
	<UHeader :toggle="false">
		<template #left>
			<OpenLeftMenu />
		</template>
		{{ $t("statistics.title") }}
	</UHeader>
	<UCard v-if="is_se_preview_enabled" class="text-center text-highlighted">
		<div v-html="$t('statistics.preview_text')" />
	</UCard>
	<UCard class="max-w-5xl mx-auto">
		<SizeVariantMeter v-if="userStore.isLoggedIn" :album-id="null" />
	</UCard>
	<Activity v-if="!is_se_preview_enabled" />
	<UCard class="max-w-5xl mx-auto" :ui="{ header: 'hidden' }">
		<template v-if="userStore.isLoggedIn && total !== undefined && showTotal">
			<TotalCard :total="total" />
			<USwitch v-model="is_collapsed" class="py-4" :label="$t('statistics.collapse')" :ui="{ label: 'text-sm' }" />
		</template>
		<AlbumsTable
			v-if="userStore.isLoggedIn"
			v-show="!is_collapsed"
			:show-username="true"
			:is-total="false"
			:album-id="undefined"
			@total="total = $event"
		/>
		<AlbumsTable v-if="userStore.isLoggedIn" v-show="is_collapsed" :show-username="true" :is-total="true" :album-id="undefined" />
	</UCard>
</template>
<script setup lang="ts">
import { onMounted, ref } from "vue";
import { useUserStore } from "@/stores/UserState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import { useRouter } from "vue-router";
import SizeVariantMeter from "@/v8/components/statistics/SizeVariantMeter.vue";
import TotalCard from "@/v8/components/statistics/TotalCard.vue";
import AlbumsTable from "@/v8/components/statistics/AlbumsTable.vue";
import OpenLeftMenu from "@/v8/components/headers/OpenLeftMenu.vue";
import Activity from "@/v8/components/statistics/Activity.vue";
import { computed } from "vue";
import { TotalAlbum } from "@/composables/album/albumStatistics";

const router = useRouter();
const userStore = useUserStore();
const lycheeStore = useLycheeStateStore();
lycheeStore.load();

const total = ref<TotalAlbum | undefined>(undefined);
const is_collapsed = ref(false);

const { is_se_preview_enabled, are_nsfw_visible } = storeToRefs(lycheeStore);

const showTotal = computed(() => total.value !== undefined && (total.value.num_albums > 0 || total.value.num_photos > 0 || total.value.size > 0));

onMounted(async () => {
	await userStore.load();
	if (!userStore.isLoggedIn) {
		router.push({ name: "home" });
	}
});

defineShortcuts({
	h: () => (are_nsfw_visible.value = !are_nsfw_visible.value),
});
</script>
