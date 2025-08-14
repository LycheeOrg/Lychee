<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none m-3" pt:mask:style="backdrop-filter: blur(2px)" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative max-w-md w-full text-sm rounded-md text-muted-color">
				<div v-if="version" class="p-9 text-muted-color-emphasis">
					<h1 class="mb-6 text-center text-xl font-bold">
						Lychee
						<span class="version-number">{{ version.version ?? "" }}</span>
						<span v-if="is_se_enabled" class="text-primary-500"> SE</span>
						<span v-if="version.is_new_release_available" class="text-sm font-normal up-to-date-release text-center">
							–
							<a
								target="_blank"
								class="border-b-neutral-200 border-b-[1px] border-dashed"
								rel="noopener"
								href="https://github.com/LycheeOrg/Lychee/releases"
								data-tabindex="-1"
							>
								{{ $t("dialogs.about.update_available") }}
							</a>
						</span>
						<span v-if="!version.is_new_release_available && version.is_git_update_available" class="text-sm font-normal up-to-date-git">
							–
							<a
								target="_blank"
								class="border-b-neutral-200 border-b-[1px] border-dashed"
								rel="noopener"
								href="https://github.com/LycheeOrg/Lychee"
								data-tabindex="-1"
							>
								{{ $t("dialogs.about.update_available") }}
							</a>
						</span>
					</h1>
					<h2 class="my-6 font-bold text-center">
						{{ $t("dialogs.about.subtitle") }}<br />
						<a href="https://lycheeorg.dev" target="_blank" class="text-center text-primary-500 underline">https://lycheeorg.dev</a>
					</h2>

					<p class="text-muted-color text-center">{{ $t("dialogs.about.description") }}</p>
					<p v-if="is_se_enabled" class="text-muted-color text-center font-bold mt-8">{{ $t("dialogs.about.thank_you") }}</p>
					<p v-if="!is_se_enabled && !is_se_info_hidden" class="text-muted-color text-center font-bold mt-8">
						<span v-html="supporter" />
						<a class="text-primary-500 underline cursor-pointer ltr:ml-1 rtl:mr-1" @click="toggleRegistration">
							{{ $t("dialogs.about.here") }} </a
						>.<br />
					</p>
				</div>
				<div class="flex justify-center">
					<Button severity="info" class="w-full font-bold border-none rounded-none rounded-b-xl" @click="closeCallback">
						{{ $t("dialogs.button.close") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
	<RegisterLychee v-model:visible="registerLycheeVisible" />
</template>
<script setup lang="ts">
import { Ref, ref } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InitService from "@/services/init-service";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import RegisterLychee from "./RegisterLychee.vue";

const visible = defineModel("visible") as Ref<boolean>;
const registerLycheeVisible = ref(false);
const supporter = ref(trans("dialogs.about.get_supporter_or_register"));
const version = ref<App.Http.Resources.Root.VersionResource | undefined>(undefined);
const lycheeStore = useLycheeStateStore();

const { is_se_enabled, is_se_info_hidden } = storeToRefs(lycheeStore);

InitService.fetchVersion().then((data) => {
	version.value = data.data;
});

function closeCallback() {
	visible.value = false;
}

function toggleRegistration() {
	registerLycheeVisible.value = true;
	visible.value = false;
}
</script>
