<template>
	<UCard v-if="initData?.modules.is_face_recognition_warning_enabled" class="max-w-6xl mx-auto">
		<h2 class="text-xl font-bold mb-4 flex items-center gap-2">
			<UIcon name="prime:exclamation-triangle" class="text-warning-600" />
			<span>{{ $t("people.face_recognition_warning.title") }}</span>
		</h2>

		<p class="text-muted mb-3">{{ $t("people.face_recognition_warning.legal_notice") }}</p>

		<p class="text-highlighted mb-1">
			<strong>{{ $t("people.face_recognition_warning.example_title") }}</strong>
		</p>
		<p class="text-muted mb-3 text-sm" v-html="$t('people.face_recognition_warning.example_body')" />

		<p class="text-muted mb-3">{{ $t("people.face_recognition_warning.similar_rules") }}</p>

		<p class="text-muted mb-4" v-html="$t('people.face_recognition_warning.no_liability')"></p>

		<div v-if="initData?.settings.can_edit" class="flex flex-row justify-between gap-3 border-t border-default pt-4">
			<div class="flex items-center gap-2">
				<UCheckbox v-model="acknowledged" id="face-warning-ack" />
				<label for="face-warning-ack" class="text-sm cursor-pointer">{{ $t("people.face_recognition_warning.acknowledge") }}</label>
			</div>
			<div class="flex ltr:justify-end rtl:justify-start">
				<UButton
					:label="$t('people.face_recognition_warning.accept')"
					icon="prime:check"
					color="primary"
					:disabled="!acknowledged"
					@click="accept"
				/>
			</div>
		</div>
	</UCard>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { storeToRefs } from "pinia";
import InitService from "@/services/init-service";
import SettingsService from "@/services/settings-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";

const leftMenuStore = useLeftMenuStateStore();
const { initData } = storeToRefs(leftMenuStore);

const acknowledged = ref(false);

async function load(): Promise<void> {
	return InitService.fetchGlobalRights().then((data) => {
		initData.value = data.data;
	});
}

function accept() {
	SettingsService.setConfigs({
		configs: [
			{
				key: "ai_vision_face_recognition_warning",
				value: "0",
			},
		],
	}).then(() => {
		if (initData.value) {
			initData.value.modules.is_face_recognition_warning_enabled = false;
		}
	});
}

onMounted(() => {
	if (initData.value === undefined) {
		load();
	}
});
</script>
