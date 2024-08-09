import { Alpine as AlpineType } from "alpinejs";

export type Livewire = {
	dispatch: (event: string, params?: any[]) => void;
};

declare global {
	var Alpine: AlpineType;
	var Livewire: Livewire;
	var assets_url: string;
}
