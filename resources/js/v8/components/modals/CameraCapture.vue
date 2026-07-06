<template>
	<UModal v-model:open="is_camera_capture_visible" :dismissible="true">
		<template #body>
			<div class="flex flex-col items-center gap-3">
				<h3 class="text-lg font-semibold">{{ $t("dialogs.camera.title") }}</h3>

				<div v-if="errorMessage" class="text-red-500 text-sm text-center">{{ errorMessage }}</div>

				<div v-if="!capturedBlob" class="relative w-full">
					<video ref="videoEl" autoplay playsinline class="w-full max-h-[60vh] rounded-xl object-contain" />
					<div v-if="cameraLoading" class="absolute inset-0 flex items-center justify-center rounded-xl bg-neutral-900/50">
						<Spinner class="text-3xl text-white" />
					</div>
				</div>

				<div v-else class="w-full">
					<img :src="capturedDataUrl" class="w-full max-h-[60vh] rounded-xl object-contain" />
				</div>

				<canvas ref="canvasEl" class="hidden" />
			</div>
		</template>
		<template #footer>
			<div v-if="!capturedBlob" class="flex w-full gap-2">
				<UButton
					icon="prime:times"
					:label="$t('dialogs.button.cancel')"
					color="neutral"
					variant="soft"
					class="flex-1 justify-center"
					@click="
						() => {
							is_camera_capture_visible = false;
						}
					"
				/>
				<UButton
					:disabled="!cameraReady"
					icon="prime:camera"
					:label="$t('dialogs.camera.capture')"
					color="primary"
					class="flex-1 justify-center"
					@click="capture"
				/>
			</div>
			<div v-else class="flex w-full gap-2">
				<UButton
					icon="prime:times"
					:label="$t('dialogs.button.cancel')"
					color="neutral"
					variant="soft"
					class="flex-1 justify-center"
					@click="
						() => {
							is_camera_capture_visible = false;
						}
					"
				/>
				<UButton
					icon="prime:refresh"
					:label="$t('dialogs.camera.retake')"
					color="neutral"
					variant="soft"
					class="flex-1 justify-center"
					@click="retake"
				/>
				<UButton icon="prime:upload" :label="$t('dialogs.camera.upload')" color="primary" class="flex-1 justify-center" @click="upload" />
			</div>
		</template>
	</UModal>
</template>
<script setup lang="ts">
import Spinner from "@/v8/components/Spinner.vue";
import { ref, watch, onUnmounted } from "vue";
import { storeToRefs } from "pinia";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useRandomId } from "@/composables/useRandomId";
import { trans } from "laravel-vue-i18n";
const togglableStore = useTogglablesStateStore();
const generateId = useRandomId();
const { is_camera_capture_visible, is_upload_visible, list_upload_files } = storeToRefs(togglableStore);

const videoEl = ref<HTMLVideoElement | null>(null);
const canvasEl = ref<HTMLCanvasElement | null>(null);
const cameraReady = ref(false);
const cameraLoading = ref(false);
const capturedBlob = ref<Blob | null>(null);
const capturedDataUrl = ref<string>("");
const errorMessage = ref<string>("");

let stream: MediaStream | null = null;
let cameraToken = 0;

function startCamera() {
	errorMessage.value = "";
	cameraReady.value = false;
	cameraLoading.value = true;
	capturedBlob.value = null;
	capturedDataUrl.value = "";

	if (!navigator.mediaDevices?.getUserMedia) {
		cameraLoading.value = false;
		errorMessage.value = trans("dialogs.camera.secure_connection_required");
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
					cameraLoading.value = false;
					cameraReady.value = true;
				};
			}
		})
		.catch(function (e: Error) {
			if (token !== cameraToken) return;
			cameraLoading.value = false;
			errorMessage.value = e.message ?? trans("dialogs.camera.secure_connection_required");
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
	cameraLoading.value = false;
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

	list_upload_files.value.push({ uid: generateId(), file, status: "waiting" });
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
