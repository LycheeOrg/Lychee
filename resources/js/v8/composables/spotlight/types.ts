import type { CommandPaletteItem } from "@nuxt/ui";

export type SpotlightItem = CommandPaletteItem & {
	kind: "nav" | "album" | "remote-album" | "remote-photo";
	albumId?: string;
	photoId?: string | null;
	thumbUrl?: string | null;
};
