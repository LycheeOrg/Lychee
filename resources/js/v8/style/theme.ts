import { ThemeProps } from "@nuxt/ui/runtime/components/Theme.d.vue.js";

export const theme: ThemeProps = {
	props: {
		modal: {
			close: false
		},
	},
	ui: {
		slideover: {
			body: "border-b-0 pt-0 sm:pt-0",
			header: "border-b-0"
		},
		navigationMenu: {
			root: "gap-0",
			separator: "bg-border-0",
		},
	}
};
