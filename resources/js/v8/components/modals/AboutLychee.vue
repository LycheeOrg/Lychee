<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<div v-if="version">
				<h1 class="mb-6 text-center text-xl font-bold">
					Lychee
					<span class="version-number">{{ version.version ?? "" }}</span>
					<span v-if="is_se_enabled" class="text-primary"> SE</span>
					<span v-if="version.is_new_release_available" class="text-sm font-normal text-center">
						–
						<a
							target="_blank"
							class="border-b-neutral-200 border-b-[1px] border-dashed"
							rel="noopener"
							href="https://github.com/LycheeOrg/Lychee/releases"
						>
							{{ $t("dialogs.about.update_available") }}
						</a>
					</span>
					<span v-if="!version.is_new_release_available && version.is_git_update_available" class="text-sm font-normal text-center">
						–
						<a
							target="_blank"
							class="border-b-neutral-200 border-b-[1px] border-dashed"
							rel="noopener"
							href="https://github.com/LycheeOrg/Lychee"
						>
							{{ $t("dialogs.about.update_available") }}
						</a>
					</span>
				</h1>
				<h2 class="my-6 font-bold text-center">
					{{ $t("dialogs.about.subtitle") }}<br />
					<a href="https://lycheeorg.dev" target="_blank" class="text-center text-primary underline">https://lycheeorg.dev</a>
				</h2>

				<p class="text-muted text-center">{{ $t("dialogs.about.description") }}</p>
				<p v-if="is_se_enabled" class="text-muted text-center font-bold mt-8">{{ $t("dialogs.about.thank_you") }}</p>
				<p v-if="!is_se_enabled && !is_se_info_hidden" class="text-muted text-center font-bold mt-8">
					<span v-html="supporter" />
					<a class="text-primary underline cursor-pointer ltr:ml-1 rtl:mr-1" @click="toggleRegistration"> {{ $t("dialogs.about.here") }} </a
					>.<br />
				</p>
			</div>
		</template>
		<template #footer>
			<UButton class="w-full justify-center" @click="closeCallback">
				{{ $t("dialogs.button.close") }}
			</UButton>
		</template>
	</UModal>
	<RegisterLychee v-model:open="registerLycheeVisible" />
</template>
<script setup lang="ts">
import { Ref, ref } from "vue";
import InitService from "@/services/init-service";
import { trans } from "laravel-vue-i18n";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";
import RegisterLychee from "./RegisterLychee.vue";

const visible = defineModel("open", { default: false }) as Ref<boolean>;
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
