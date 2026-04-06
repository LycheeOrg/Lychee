<template>
	<Dialog v-model:visible="localVisible" modal :closable="false" pt:root:class="border-none m-3" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container>
			<div class="flex flex-col gap-4 relative max-w-lg w-full text-sm rounded-md">
				<!-- Header -->
				<div class="flex items-center justify-between px-6 pt-6">
					<div class="flex items-center gap-2 text-orange-400 font-bold text-base">
						<i class="pi pi-exclamation-triangle text-xl" />
						<span>{{ $t("dialogs.security_advisories.title") }}</span>
					</div>
					<button class="text-muted-color hover:text-primary-400 transition-colors" @click="dismiss">
						<i class="pi pi-times" />
					</button>
				</div>

				<!-- Body -->
				<div class="px-6">
					<p class="mb-4 text-muted-color">{{ $t("dialogs.security_advisories.description") }}</p>
					<ul class="space-y-3">
						<li v-for="advisory in advisories" :key="advisory.ghsa_id" class="flex flex-col gap-1">
							<div class="flex items-center gap-2 font-semibold">
								<span class="text-orange-400">•</span>
								<a
									:href="`https://github.com/advisories/${advisory.ghsa_id}`"
									target="_blank"
									rel="noopener noreferrer"
									class="text-primary-400 hover:text-primary-300 underline"
								>
									{{ advisory.cve_id ?? advisory.ghsa_id }}
								</a>
								<span class="text-muted-color text-xs">
									{{
										advisory.cvss_score !== null
											? `CVSS ${advisory.cvss_score.toFixed(1)}`
											: $t("dialogs.security_advisories.no_cvss")
									}}
								</span>
							</div>
							<p class="ltr:ml-4 rtl:mr-4 text-muted-color text-xs">{{ advisory.summary }}</p>
						</li>
					</ul>
				</div>

				<!-- Footer -->
				<div class="flex items-center">
					<Button
						severity="secondary"
						class="w-full font-bold border-none rounded-none ltr:rounded-bl-xl rtl:rounded-br-xl shrink"
						@click="goToDiagnostics"
					>
						{{ $t("dialogs.security_advisories.go_to_diagnostics") }}
					</Button>
					<Button
						severity="contrast"
						class="w-full font-bold border-none rounded-none ltr:rounded-br-xl rtl:rounded-bl-xl shrink"
						@click="dismiss"
					>
						{{ $t("dialogs.button.close") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { useRouter } from "vue-router";
import Button from "primevue/button";
import Dialog from "primevue/dialog";

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
	sessionStorage.setItem("advisory_dismissed", "1");
	emits("update:visible", false);
}

function goToDiagnostics() {
	dismiss();
	router.push("/diagnostics");
}
</script>
