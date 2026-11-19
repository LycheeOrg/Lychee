<template>
	<Panel v-if="rights?.modules.is_face_recognition_warning_enabled" class="max-w-6xl mx-auto p-6 border-none">
		<h2 class="text-xl font-bold mb-4">
			<span class="pi pi-exclamation-triangle text-warning-600 ltr:mr-2 rtl:ml-2"></span>
			<span>{{ $t("people.face_recognition_warning.title") }}</span>
		</h2>

		<p class="text-muted-color mb-3">{{ $t("people.face_recognition_warning.legal_notice") }}</p>

		<p class="text-muted-color-emphasis mb-1">
			<strong>{{ $t("people.face_recognition_warning.example_title") }}</strong>
		</p>
		<p class="text-muted-color mb-3 text-sm" v-html="$t('people.face_recognition_warning.example_body')" />

		<p class="text-muted-color mb-3">{{ $t("people.face_recognition_warning.similar_rules") }}</p>

		<p class="text-muted-color mb-4" v-html="$t('people.face_recognition_warning.no_liability')"></p>

		<div v-if="rights?.settings.can_edit" class="flex flex-row justify-between gap-3 border-t border-surface pt-4">
			<div class="flex items-center gap-2">
				<Checkbox v-model="acknowledged" binary inputId="face-warning-ack" />
				<label for="face-warning-ack" class="text-sm cursor-pointer">{{ $t("people.face_recognition_warning.acknowledge") }}</label>
			</div>
			<div class="flex ltr:justify-end rtl:justify-start">
				<Button
					:label="$t('people.face_recognition_warning.accept')"
					icon="pi pi-check"
					class="border-none"
					severity="primary"
					:disabled="!acknowledged"
					@click="accept"
				/>
			</div>
		</div>
	</Panel>
</template>

<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Checkbox from "primevue/checkbox";
import SettingsService from "@/services/settings-service";
import { useGlobalRights } from "@/composables/useGlobalRights";
import Panel from "primevue/panel";

const { rights } = useGlobalRights();

const acknowledged = ref(false);

function accept() {
	SettingsService.setConfigs({
		configs: [
			{
				key: "ai_vision_face_recognition_warning",
				value: "0",
			},
		],
	}).then(() => {
		if (rights.value) {
			rights.value.modules.is_face_recognition_warning_enabled = false;
		}
	});
}
</script>
