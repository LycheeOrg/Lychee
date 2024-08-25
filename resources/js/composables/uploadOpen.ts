import { ref } from "vue";

export function useUploadOpen(value: boolean) {
	const isUploadOpen = ref(value);

	function toggleUpload() {
		isUploadOpen.value = !isUploadOpen.value;
	}

	return {
		isUploadOpen,
		toggleUpload,
	};
}
