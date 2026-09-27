<template>
	<UCard v-if="rights?.modules.is_face_recognition_warning_enabled" class="max-w-6xl mx-auto">
		<h2 class="text-xl font-bold mb-4 flex items-center gap-2">
			<UIcon name="lucide:triangle-alert" class="text-warning-600" />
			<span>{{ $t("people.face_recognition_warning.title") }}</span>
		</h2>

		<p class="text-muted mb-3">{{ $t("people.face_recognition_warning.legal_notice") }}</p>

		<p class="text-highlighted mb-1">
			<strong>{{ $t("people.face_recognition_warning.example_title") }}</strong>
		</p>
		<p class="text-muted mb-3 text-sm" v-html="$t('people.face_recognition_warning.example_body')" />

		<p class="text-muted mb-3">{{ $t("people.face_recognition_warning.similar_rules") }}</p>

		<p class="text-muted mb-4" v-html="$t('people.face_recognition_warning.no_liability')"></p>

		<div v-if="rights?.settings.can_edit" class="flex flex-row justify-between gap-3 border-t border-default pt-4">
			<UCheckbox v-model="acknowledged" :label="$t('people.face_recognition_warning.acknowledge')" />
			<div class="flex ltr:justify-end rtl:justify-start">
				<UButton
					:label="$t('people.face_recognition_warning.accept')"
					icon="lucide:check"
					color="primary"
					variant="solid"
					:disabled="!acknowledged"
					@click="accept"
				/>
			</div>
		</div>
	</UCard>
</template>

<script setup lang="ts">
import { ref } from "vue";
import SettingsService from "@/services/settings-service";
import { useGlobalRights } from "@/composables/useGlobalRights";

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
