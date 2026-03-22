<template>
	<Dialog v-model:visible="is_camera_capture_visible" modal :dismissable-mask="true" pt:root:class="border-none">
		<template #container>
			<div class="flex flex-col w-screen max-w-lg max-h-screen overflow-y-auto">
				<div class="flex flex-col items-center gap-3 p-4">
					<h3 class="text-lg font-semibold">{{ $t("dialogs.camera.title") }}</h3>

					<div v-if="errorMessage" class="text-red-500 text-sm text-center">{{ errorMessage }}</div>

					<div v-if="!capturedBlob" class="relative w-full">
						<video ref="videoEl" autoplay playsinline class="w-full max-h-[60vh] rounded-xl object-contain" />
						<div v-if="!cameraReady" class="absolute inset-0 flex items-center justify-center rounded-xl bg-surface-900/50">
							<i class="pi pi-spin pi-spinner text-3xl text-white" />
						</div>
					</div>

					<div v-else class="w-full">
						<img :src="capturedDataUrl" class="w-full max-h-[60vh] rounded-xl object-contain" />
					</div>

					<canvas ref="canvasEl" class="hidden" />

					<div v-if="!capturedBlob" class="flex gap-3 pb-2">
						<Button
							icon="pi pi-times"
							:label="$t('dialogs.button.cancel')"
							severity="secondary"
							@click="is_camera_capture_visible = false"
						/>
						<Button
							:disabled="!cameraReady"
							icon="pi pi-camera"
							:label="$t('dialogs.camera.capture')"
							severity="primary"
							@click="capture"
						/>
					</div>
					<div v-else class="flex gap-3 pb-2">
						<Button
							icon="pi pi-times"
							:label="$t('dialogs.button.cancel')"
							severity="secondary"
							@click="is_camera_capture_visible = false"
						/>
						<Button icon="pi pi-refresh" :label="$t('dialogs.camera.retake')" severity="secondary" @click="retake" />
						<Button icon="pi pi-upload" :label="$t('dialogs.camera.upload')" severity="primary" @click="upload" />
					</div>
				</div>
			</div>
		</template>
	</Dialog>
</template>
<script setup lang="ts">
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import { ref, watch, onUnmounted } from "vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const togglableStore = useTogglablesStateStore();
const { is_camera_capture_visible, is_upload_visible, list_upload_files } = storeToRefs(togglableStore);

const videoEl = ref<HTMLVideoElement | null>(null);
const canvasEl = ref<HTMLCanvasElement | null>(null);
const cameraReady = ref(false);
const capturedBlob = ref<Blob | null>(null);
const capturedDataUrl = ref<string>("");
const errorMessage = ref<string>("");

let stream: MediaStream | null = null;
let cameraToken = 0;

function startCamera() {
	errorMessage.value = "";
	cameraReady.value = false;
	capturedBlob.value = null;
	capturedDataUrl.value = "";

	if (!navigator.mediaDevices?.getUserMedia) {
		errorMessage.value = t("dialogs.camera.secure_connection_required");
		return;
	}

	const token = ++cameraToken;

	navigator.mediaDevices
		.getUserMedia({ video: { facingMode: "environment" }, audio: false })
		.then(function (s) {
			if (token !== cameraToken) {
				s.getTracks().forEach(function (t) {
					t.stop();
				});
				return;
			}
			stream = s;
			if (videoEl.value) {
				videoEl.value.srcObject = s;
				videoEl.value.onloadedmetadata = function () {
					cameraReady.value = true;
				};
			}
		})
		.catch(function (e: Error) {
			if (token !== cameraToken) return;
			errorMessage.value = e.message ?? t("dialogs.camera.secure_connection_required");
		});
}

function stopStream() {
	if (stream) {
		stream.getTracks().forEach(function (t) {
			t.stop();
		});
		stream = null;
	}
	cameraReady.value = false;
}

function stopCamera() {
	stopStream();
	capturedBlob.value = null;
	capturedDataUrl.value = "";
	errorMessage.value = "";
}

function capture() {
	if (!videoEl.value || !canvasEl.value) return;

	const video = videoEl.value;
	const canvas = canvasEl.value;
	canvas.width = video.videoWidth;
	canvas.height = video.videoHeight;
	canvas.getContext("2d")?.drawImage(video, 0, 0);

	canvas.toBlob(
		function (blob) {
			if (!blob) return;
			capturedBlob.value = blob;
			capturedDataUrl.value = canvas.toDataURL("image/jpeg");
			stopStream();
		},
		"image/jpeg",
		0.92,
	);
}

function retake() {
	capturedBlob.value = null;
	capturedDataUrl.value = "";
	startCamera();
}

function upload() {
	if (!capturedBlob.value) return;

	const filename = `photo_${new Date().toISOString().replace(/[:.]/g, "-")}.jpg`;
	const file = new File([capturedBlob.value], filename, { type: "image/jpeg" });

	list_upload_files.value.push({ file, status: "waiting" });
	is_upload_visible.value = true;
	is_camera_capture_visible.value = false;
}

watch(is_camera_capture_visible, function (visible) {
	if (visible) {
		startCamera();
	} else {
		stopCamera();
	}
});

onUnmounted(stopCamera);
</script>
