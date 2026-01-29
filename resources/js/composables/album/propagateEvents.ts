export function usePropagateAlbumEvents(
	emits: ((evt: "clicked", event: MouseEvent, id: string) => void) & ((evt: "contexted", event: MouseEvent, id: string) => void),
) {
	function propagateClicked(event: MouseEvent, id: string) {
		emits("clicked", event, id);
	}

	function propagateContexted(event: MouseEvent, id: string) {
		emits("contexted", event, id);
	}

	return {
		propagateClicked,
		propagateContexted,
	};
}
