<template>
	<UModal v-model:open="open">
		<template #body>
			<div v-show="!qrCodeOpen" class="flex flex-wrap gap-5 justify-center align-top max-w-lg w-full text-muted">
				<MiniIcon class="w-10 h-10 ionicons cursor-pointer" icon="twitter" @click="openTwitter" />
				<MiniIcon class="w-10 h-10 ionicons cursor-pointer" icon="facebook" @click="openFacebook" />
				<MiniIcon class="w-10 h-10 cursor-pointer" icon="envelope-closed" @click="openMailto" />
				<a class="cursor-pointer" @click="copyToClipboard()">
					<MiniIcon class="w-10 h-10" icon="link-intact" />
				</a>
				<MiniIcon class="w-10 h-10" icon="grid-two-up" @click="openQrCode" />
			</div>
			<div v-show="qrCodeOpen" class="flex flex-wrap gap-5 justify-center align-top text-muted">
				<canvas id="canvas"></canvas>
			</div>
		</template>
		<template #footer>
			<UButton
				color="neutral"
				variant="ghost"
				class="font-bold w-full justify-center"
				:label="trans('dialogs.button.close')"
				@click="
					qrCodeOpen = false;
					open = false;
				"
			>
				{{ trans("dialogs.button.close") }}
			</UButton>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import { ref } from "vue";
import { useAppToast } from "@/v8/composables/useAppToast";
import QRCode from "qrcode";
import { trans } from "laravel-vue-i18n";
import MiniIcon from "@/v8/components/icons/MiniIcon.vue";

const props = defineProps<{
	title: string;
}>();
const toast = useAppToast();

const qrCodeOpen = ref(false);

const open = defineModel<boolean>("open", { default: false });
const url = ref(window.location.href);
const title = ref(props.title);

function copyToClipboard() {
	navigator.clipboard
		.writeText(url.value)
		.then(() => toast.add({ severity: "info", summary: "Info", detail: trans("dialogs.share_album.url_copied"), life: 3000 }));
}

function openQrCode() {
	qrCodeOpen.value = true;
	QRCode.toCanvas(
		document.getElementById("canvas"),
		url.value,
		{
			errorCorrectionLevel: "H",
		},
		function (err: Error | null | undefined) {
			if (err) throw err;
		},
	);
}

function openTwitter() {
	window.open(`https://twitter.com/share?url=${encodeURIComponent(url.value)}`);
}
function openFacebook() {
	window.open(`https://www.facebook.com/sharer.php?u=${encodeURIComponent(url.value)}?t=${encodeURIComponent(title.value)}`);
}
function openMailto() {
	window.open(`mailto:?subject=${encodeURIComponent(title.value)}&body=${encodeURIComponent(url.value)}`);
}
</script>
