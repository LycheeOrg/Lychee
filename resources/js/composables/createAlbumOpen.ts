import { ref } from "vue";

export function useCreateAlbumOpen(value: boolean) {
	const isCreateAlbumOpen = ref(value);

	function toggleCreateAlbum() {
		isCreateAlbumOpen.value = !isCreateAlbumOpen.value;
	}

	return {
		isCreateAlbumOpen,
		toggleCreateAlbum,
	};
}
