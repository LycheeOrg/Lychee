<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none m-3  max-w-md w-full" pt:mask:style="backdrop-filter: blur(2px)">
		<template #container>
			<div class="flex flex-col gap-4 bg-gradient-to-b from-bg-300 to-bg-400 relative text-sm rounded-md text-muted-color">
				<div class="p-9 text-muted-color-emphasis">
					<h1 class="mb-6 text-center text-2xl font-bold text-warning-700">
						<i class="pi pi-exclamation-triangle mr-2" /> {{ $t("dialogs.session_expired.title") }}
					</h1>
					<p class="text-muted-color text-center" v-html="$t('dialogs.session_expired.message')" />
				</div>
				<div class="flex justify-center">
					<Button @click="closeAndReload" severity="info" class="w-full font-bold border-none rounded-none rounded-bl-xl">
						{{ $t("dialogs.session_expired.reload") }}
					</Button>
					<Button @click="gotoGallery" severity="contrast" class="w-full font-bold border-none rounded-none rounded-br-xl">
						{{ $t("dialogs.session_expired.go_to_gallery") }}
					</Button>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { Ref } from "vue";
import { useRouter } from "vue-router";

const visible = defineModel("visible", { default: false }) as Ref<boolean>;
const router = useRouter();

function closeAndReload() {
	location.reload();
}

function gotoGallery() {
	location.href = router.resolve({ name: "gallery" }).href;
}
</script>
