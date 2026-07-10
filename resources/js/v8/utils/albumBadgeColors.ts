/**
 * Central color palette for the small corner badges shown on album tiles
 * (grid: `ThumbBadge.vue`, list: `ListBadge.vue`) for smart-album kinds
 * (`unsorted`, `recent`, `on_this_day`, ratings, ...) and regular album flags
 * (NSFW, public/password, tag/person album, cover). One named role maps to
 * one Tailwind color+shade pair per CSS property, so a future accent/brand
 * change only needs updating here instead of the ~20 call sites that used to
 * hardcode `bg-yellow-500`/`bg-[#ff82ee]`/etc. independently.
 *
 * Classes are written out in full (not composed from parts) since Tailwind's
 * build-time scanner needs the complete utility name to appear literally in
 * source to generate the corresponding CSS.
 */
export type AlbumBadgeRole = "nsfw" | "favorite" | "danger" | "info" | "success" | "neutral" | "trophy" | "rated" | "link" | "person";

export const ALBUM_BADGE_BG: Record<AlbumBadgeRole, string> = {
	nsfw: "bg-fuchsia-500 dark:bg-fuchsia-400",
	favorite: "bg-warning-500 dark:bg-warning-400",
	danger: "bg-error-700 dark:bg-error-600",
	info: "bg-secondary-700 dark:bg-secondary-600",
	success: "bg-success-600 dark:bg-success-500",
	neutral: "bg-neutral-500 dark:bg-neutral-400",
	trophy: "bg-cyan-500 dark:bg-cyan-400",
	rated: "bg-orange-500 dark:bg-orange-400",
	link: "bg-orange-400 dark:bg-orange-300",
	person: "bg-purple-600 dark:bg-purple-500",
};

export const ALBUM_BADGE_FILL: Record<AlbumBadgeRole, string> = {
	nsfw: "fill-fuchsia-500 dark:fill-fuchsia-400",
	favorite: "fill-warning-500 dark:fill-warning-400",
	danger: "fill-error-700 dark:fill-error-600",
	info: "fill-secondary-700 dark:fill-secondary-600",
	success: "fill-success-600 dark:fill-success-500",
	neutral: "fill-neutral-500 dark:fill-neutral-400",
	trophy: "fill-cyan-500 dark:fill-cyan-400",
	rated: "fill-orange-500 dark:fill-orange-400",
	link: "fill-orange-400 dark:fill-orange-300",
	person: "fill-purple-600 dark:fill-purple-500",
};

export const ALBUM_BADGE_TEXT: Record<AlbumBadgeRole, string> = {
	nsfw: "text-fuchsia-500 dark:text-fuchsia-400",
	favorite: "text-warning-500 dark:text-warning-400",
	danger: "text-error-700 dark:text-error-600",
	info: "text-secondary-700 dark:text-secondary-600",
	success: "text-success-600 dark:text-success-500",
	neutral: "text-neutral-500 dark:text-neutral-400",
	trophy: "text-cyan-500 dark:text-cyan-400",
	rated: "text-orange-500 dark:text-orange-400",
	link: "text-orange-400 dark:text-orange-300",
	person: "text-purple-600 dark:text-purple-500",
};
