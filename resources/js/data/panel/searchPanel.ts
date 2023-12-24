import type { Alpine, AlpineComponent } from "alpinejs";
import Selection from "@/lycheeOrg/actions/selection";

export type SearchPanel = AlpineComponent<{
	searchQuery: string;
	hideMessage: boolean;
	minLength: number;
	select: Selection;
	setMessageHidden: () => void;
	search: (newValue: string) => void;
}>;

export const searchPanel = (Alpine: Alpine) =>
	Alpine.data(
		"searchPanel",
		// @ts-expect-error
		(searchQuery: string, minLength: number, select: Selection): SearchPanel => ({
			searchQuery: searchQuery,
			hideMessage: false,
			minLength: minLength,
			select: select,

			init() {
				this.$refs.search.focus();
				this.setMessageHidden();
				this.$watch("searchQuery", (newValue) => {
					this.setMessageHidden();
					this.search(newValue);
				});
			},

			setMessageHidden() {
				this.hideMessage = this.searchQuery.length < minLength && this.searchQuery.length > 0;
			},

			search(newValue) {
				// @ts-expect-error
				this.$wire.set("searchQuery", newValue);
			},
		}),
	);
