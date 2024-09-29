<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none" pt:mask:style="backdrop-filter: blur(2px)" @hide="closeCallback">
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative w-[500px] text-sm rounded-md text-muted-color">
				<div class="p-9 text-muted-color-emphasis" v-if="version">
					<h1 class="mb-6 text-center text-xl font-bold">
						Lychee
						<span class="text-primary-500" v-if="is_se_enabled">SE</span>
						<span class="version-number">{{ version.version ?? "" }}</span>
						<span class="text-sm font-normal up-to-date-release text-center" v-if="version.is_new_release_available">
							–
							<a
								target="_blank"
								class="border-b-neutral-200 border-b-[1px] border-dashed"
								rel="noopener"
								href="https://github.com/LycheeOrg/Lychee/releases"
								data-tabindex="-1"
								>{{ $t("lychee.UPDATE_AVAILABLE") }}</a
							>
						</span>
						<span class="text-sm font-normal up-to-date-git" v-if="!version.is_new_release_available && version.is_git_update_available">
							–
							<a
								target="_blank"
								class="border-b-neutral-200 border-b-[1px] border-dashed"
								rel="noopener"
								href="https://github.com/LycheeOrg/Lychee"
								data-tabindex="-1"
								>{{ $t("lychee.UPDATE_AVAILABLE") }}</a
							>
						</span>
					</h1>
					<h2 class="my-6 font-bold text-center">
						{{ $t("lychee.ABOUT_SUBTITLE") }}<br />
						<a href="https://LycheeOrg.github.io" target="_blank" class="text-center text-primary-500 underline"
							>https://LycheeOrg.github.io</a
						>
					</h2>

					<p class="text-muted-color text-center" v-html="description"></p>
					<!-- <p class="text-muted-color text-center font-bold mt-8" v-if="!is_se_enabled">Thank you for your support!</p> -->
					<p class="text-muted-color text-center font-bold mt-8" v-if="!is_se_enabled && !is_se_info_hidden">
						Get exclusive features and support the development of Lychee.<br />
						Unlock the
						<a href="https://lycheeorg.github.io/get-supporter-edition/" class="text-primary-500 underline">Supporter Edition</a>.<br />
					</p>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" severity="info" class="w-full font-bold border-none rounded-none rounded-bl-xl rounded-br-xl">
						{{ $t("lychee.CLOSE") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { Ref, ref } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InitService from "@/services/init-service";
import { trans } from "laravel-vue-i18n";
import { sprintf } from "sprintf-js";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { storeToRefs } from "pinia";

const visible = defineModel("visible") as Ref<boolean>;
const description = ref(sprintf(trans("lychee.ABOUT_DESCRIPTION"), "https://LycheeOrg.github.io"));
const version = ref(undefined) as Ref<undefined | App.Http.Resources.Root.VersionResource>;
const lycheeStore = useLycheeStateStore();

const { is_se_enabled, is_se_preview_enabled, is_se_info_hidden } = storeToRefs(lycheeStore);

InitService.fetchVersion()
	.then((data) => {
		version.value = data.data;
	})
	.catch((error) => {
		console.error(error);
	});

function closeCallback() {
	visible.value = false;
}
</script>
