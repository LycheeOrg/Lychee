import { ref } from "vue";

export function useImportFromLinkOpen(value: boolean) {
	const isImportFromLinkOpen = ref(value);

	function toggleImportFromLink() {
		isImportFromLinkOpen.value = !isImportFromLinkOpen.value;
	}

	return {
		isImportFromLinkOpen,
		toggleImportFromLink,
	};
}
