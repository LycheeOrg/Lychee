<template>
	<UModal v-model:open="visible" :dismissible="true">
		<template #body>
			<h1 class="mb-6 text-center text-2xl font-bold text-warning">
				<UIcon name="lucide:triangle-alert" class="mr-2" /> {{ $t("dialogs.session_expired.title") }}
			</h1>
			<p class="text-muted text-center" v-html="$t('dialogs.session_expired.message')" />
		</template>
		<template #footer>
			<div class="flex w-full gap-2">
				<UButton class="flex-1 justify-center" color="neutral" variant="soft" @click="closeAndReload">
					{{ $t("dialogs.session_expired.reload") }}
				</UButton>
				<UButton class="flex-1 justify-center" @click="gotoGallery">
					{{ $t("dialogs.session_expired.go_to_gallery") }}
				</UButton>
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { Ref } from "vue";
import { useRouter } from "vue-router";

const visible = defineModel("open", { default: false }) as Ref<boolean>;
const router = useRouter();

function closeAndReload() {
	location.reload();
}

function gotoGallery() {
	location.href = router.resolve({ name: "gallery" }).href;
}
</script>
