<template>
	<UModal v-model:open="localVisible" :dismissible="false" :close="false">
		<template #header>
			<div class="flex items-center justify-between w-full">
				<div class="flex items-center gap-2 text-orange-400 font-bold text-base">
					<UIcon name="lucide:triangle-alert" class="text-xl" />
					<span>{{ $t("dialogs.security_advisories.title") }}</span>
				</div>
				<button class="text-muted hover:text-primary-400 transition-colors" @click="dismiss">
					<UIcon name="lucide:x" />
				</button>
			</div>
		</template>
		<template #body>
			<p class="mb-4 text-muted">{{ $t("dialogs.security_advisories.description") }}</p>
			<ul class="space-y-3">
				<li v-for="advisory in advisories" :key="advisory.ghsa_id" class="flex flex-col gap-1">
					<div class="flex items-center gap-2 font-semibold">
						<span class="text-orange-400">•</span>
						<a
							:href="`https://github.com/LycheeOrg/Lychee/security/advisories/${advisory.ghsa_id}`"
							target="_blank"
							rel="noopener noreferrer"
							class="text-primary-400 hover:text-primary-300 underline"
						>
							{{ advisory.cve_id ?? advisory.ghsa_id }}
						</a>
						<span class="text-muted text-xs">
							{{ advisory.cvss_score !== null ? `CVSS ${advisory.cvss_score.toFixed(1)}` : $t("dialogs.security_advisories.no_cvss") }}
						</span>
					</div>
					<p class="ltr:ml-4 rtl:mr-4 text-muted text-xs">{{ advisory.summary }}</p>
				</li>
			</ul>
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton color="neutral" variant="soft" class="flex-1 justify-center font-bold" @click="goToDiagnostics">
					{{ $t("dialogs.security_advisories.go_to_diagnostics") }}
				</UButton>
				<UButton color="neutral" class="flex-1 justify-center font-bold" @click="dismiss">
					{{ $t("dialogs.button.close") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useRouter } from "vue-router";

const props = defineProps<{
	advisories: App.Http.Resources.Models.SecurityAdvisoryResource[];
	visible: boolean;
}>();

const emits = defineEmits<{
	"update:visible": [value: boolean];
}>();

const router = useRouter();
const localVisible = ref(props.visible);

watch(
	() => props.visible,
	(v) => {
		localVisible.value = v;
	},
);

function dismiss() {
	emits("update:visible", false);
}

function goToDiagnostics() {
	dismiss();
	router.push("/diagnostics");
}
</script>
