<template>
	<Dialog
		v-model:visible="visible"
		modal
		:pt="{
			root: '!border-none',
			mask: {
				style: 'backdrop-filter: blur(2px)',
			},
		}"
		@hide="closeCallback"
	>
		<template #container="{ closeCallback }">
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative w-[500px] text-sm rounded-md text-muted-color">
				<div class="p-9 text-muted-color-emphasis" v-if="version">
					<h1 class="mb-6 text-center text-xl font-bold">
						Lychee
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
					<h2 class="my-6 font-bold">{{ $t("lychee.ABOUT_SUBTITLE") }}</h2>
					<p class="about-desc" v-html="description"></p>
				</div>
				<div class="flex justify-center">
					<Button @click="closeCallback" text class="p-3 w-full font-bold border-1 border-white-alpha-30 hover:bg-white-alpha-10">
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

const visible = defineModel("visible", { default: false }) as Ref<boolean>;

const emit = defineEmits(["close"]);
const description = ref(sprintf(trans("lychee.ABOUT_DESCRIPTION"), "https://LycheeOrg.github.io"));
const version = ref(undefined) as Ref<undefined | App.Http.Resources.Root.VersionResource>;

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
