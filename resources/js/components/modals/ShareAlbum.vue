<template>
	<Dialog v-model:visible="visible" modal pt:root:class="border-none">
		<template #container="{ closeCallback }">
			<div class="flex flex-wrap p-9 gap-5 justify-center align-top text-muted-color" v-show="!qrCodeOpen">
				<MiniIcon class="w-10 h-10 ionicons cursor-pointer" icon="twitter" v-on:click="openTwitter" />
				<MiniIcon class="w-10 h-10 ionicons cursor-pointer" icon="facebook" v-on:click="openFacebook" />
				<MiniIcon class="w-10 h-10 cursor-pointer" icon="envelope-closed" v-on:click="openMailto" />
				<a class="cursor-pointer" v-on:click="copyToClipboard()">
					<MiniIcon class="w-10 h-10" icon="link-intact" />
				</a>
				<MiniIcon class="w-10 h-10" icon="grid-two-up" v-on:click="openQrCode" />
			</div>
			<div class="flex flex-wrap p-9 gap-5 justify-center align-top text-muted-color" v-show="qrCodeOpen">
				<canvas id="canvas"></canvas>
			</div>
			<Button
				@click="
					qrCodeOpen = false;
					closeCallback();
				"
				severity="secondary"
				:label="trans('dialogs.button.close')"
				class="font-bold border-none w-full select-none border-t border-t-black/20 rounded-none rounded-bl-xl rounded-br-xl"
				>{{ trans("dialogs.button.close") }}</Button
			>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import { ref } from "vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import QRCode from "qrcode";
import { trans } from "laravel-vue-i18n";
import MiniIcon from "@/components/icons/MiniIcon.vue";

const props = defineProps<{
	title: string;
}>();
const toast = useToast();

const qrCodeOpen = ref(false);

const visible = defineModel<boolean>("visible", { default: false });
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
			// fill: '#000000',
			// background: '#FFFFFF',
			// size: 300,
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
