import { LocationQuery } from "vue-router";

/**
 * Frame overlays (Feature 088): URL overrides merged over the admin values
 * (FR-088-15, ADR-088-01), box colours, corner layout and info box lines.
 * Pure functions, no DOM access.
 */

export type FrameClock = App.Http.Resources.Frame.FrameClockData;
export type FrameInfo = App.Http.Resources.Frame.FrameInfoData;
export type FramePhoto = App.Http.Resources.Frame.FramePhotoData;
export type FrameCorner = Exclude<App.Enum.FrameBoxPosition, "center">;
export type FrameBoxKind = "clock" | "info";
export type FrameInfoLineKind = "title" | "albums" | "exposure" | "camera" | "taken_at";
export type FrameInfoLine = { kind: FrameInfoLineKind; text: string };

export const FRAME_CORNERS: readonly FrameCorner[] = ["top_left", "top_right", "bottom_left", "bottom_right"];
const CLOCK_POSITIONS: readonly App.Enum.FrameBoxPosition[] = [...FRAME_CORNERS, "center"];
const TIME_FORMATS: readonly App.Enum.FrameTimeFormat[] = ["24h", "12h"];

/** The first value of a query parameter, undefined when absent. */
function param(query: LocationQuery, key: string): string | undefined {
	const value = query[key];
	const first = Array.isArray(value) ? value[0] : value;
	return first ?? undefined;
}

/** `1` → true, `0` → false, anything else undefined. */
export function parseSwitch(value: string | undefined): boolean | undefined {
	return value === "1" ? true : value === "0" ? false : undefined;
}

/** An integer 0–100, undefined otherwise. */
export function parseOpacity(value: string | undefined): number | undefined {
	return value !== undefined && /^\d{1,3}$/.test(value) && Number(value) <= 100 ? Number(value) : undefined;
}

/** Six hex digits with or without a leading `#`, returned as `#rrggbb`; undefined otherwise. */
export function parseColor(value: string | undefined): string | undefined {
	return value !== undefined && /^#?[0-9a-fA-F]{6}$/.test(value) ? "#" + value.replace("#", "") : undefined;
}

/** The value when it is one of `allowed`, undefined otherwise. */
export function parseOneOf<T extends string>(value: string | undefined, allowed: readonly T[]): T | undefined {
	return allowed.find((candidate) => candidate === value);
}

/** Clock box settings: each valid URL parameter overrides its admin value. */
export function resolveClock(admin: FrameClock, query: LocationQuery): FrameClock {
	return {
		is_time_enabled: parseSwitch(param(query, "clock_time_enabled")) ?? admin.is_time_enabled,
		time_format: parseOneOf(param(query, "clock_time_format"), TIME_FORMATS) ?? admin.time_format,
		is_date_enabled: parseSwitch(param(query, "clock_date_enabled")) ?? admin.is_date_enabled,
		position: parseOneOf(param(query, "clock_position"), CLOCK_POSITIONS) ?? admin.position,
		background_color: parseColor(param(query, "clock_background_color")) ?? admin.background_color,
		background_opacity: parseOpacity(param(query, "clock_background_opacity")) ?? admin.background_opacity,
		opacity: parseOpacity(param(query, "clock_opacity")) ?? admin.opacity,
	};
}

/** Info box settings: each valid URL parameter overrides its admin value; `center` is not a valid position. */
export function resolveInfo(admin: FrameInfo, query: LocationQuery): FrameInfo {
	return {
		is_title_enabled: parseSwitch(param(query, "info_title_enabled")) ?? admin.is_title_enabled,
		is_albums_enabled: parseSwitch(param(query, "info_albums_enabled")) ?? admin.is_albums_enabled,
		is_exif_enabled: parseSwitch(param(query, "info_exif_enabled")) ?? admin.is_exif_enabled,
		is_exif_exposure_enabled: parseSwitch(param(query, "info_exif_exposure_enabled")) ?? admin.is_exif_exposure_enabled,
		is_exif_camera_enabled: parseSwitch(param(query, "info_exif_camera_enabled")) ?? admin.is_exif_camera_enabled,
		is_exif_taken_at_enabled: parseSwitch(param(query, "info_exif_taken_at_enabled")) ?? admin.is_exif_taken_at_enabled,
		position: parseOneOf(param(query, "info_position"), FRAME_CORNERS) ?? admin.position,
		background_color: parseColor(param(query, "info_background_color")) ?? admin.background_color,
		background_opacity: parseOpacity(param(query, "info_background_opacity")) ?? admin.background_opacity,
		opacity: parseOpacity(param(query, "info_opacity")) ?? admin.opacity,
	};
}

/** `rgba()` of a `#rrggbb` colour at an opacity in percent; an empty colour is black (FR-088-04). */
export function boxBackground(color: string, opacityPercent: number): string {
	const hex = color === "" ? "000000" : color.replace("#", "");
	const [r, g, b] = [0, 2, 4].map((i) => parseInt(hex.slice(i, i + 2), 16));
	return `rgba(${r}, ${g}, ${b}, ${opacityPercent / 100})`;
}

/**
 * The boxes of each corner, clock box first (FR-088-12). A clock box in the
 * center, or none at all (`null`), belongs to no corner.
 */
export function boxesByCorner(
	clockPosition: App.Enum.FrameBoxPosition | null,
	infoPosition: App.Enum.FrameBoxPosition,
): Record<FrameCorner, FrameBoxKind[]> {
	return Object.fromEntries(
		FRAME_CORNERS.map((corner) => [corner, [...(clockPosition === corner ? ["clock"] : []), ...(infoPosition === corner ? ["info"] : [])]]),
	) as Record<FrameCorner, FrameBoxKind[]>;
}

/** Make and model, without the make when the model already starts with it. */
export function cameraName(make: string | null, model: string | null): string | null {
	const name =
		model !== null && make !== null && model.toLowerCase().startsWith(make.toLowerCase())
			? model
			: [make, model].filter((v) => v !== null).join(" ");
	return name === "" ? null : name;
}

/** Non-null parts joined by `separator`; null when every part is null. */
function joined(parts: (string | null)[], separator: string): string | null {
	const present = parts.filter((part): part is string => part !== null);
	return present.length === 0 ? null : present.join(separator);
}

/** Lines of the info box for the enabled parts that have a value (FR-088-05 … FR-088-07, FR-088-09). */
export function infoLines(info: FrameInfo, photo: FramePhoto | null): FrameInfoLine[] {
	if (photo === null) {
		return [];
	}
	const exif = info.is_exif_enabled;
	const candidates: [FrameInfoLineKind, boolean, string | null][] = [
		["title", info.is_title_enabled, photo.title],
		["albums", info.is_albums_enabled, photo.albums === null ? null : joined(photo.albums, " · ")],
		["exposure", exif && info.is_exif_exposure_enabled, joined([photo.focal, photo.aperture, photo.shutter, photo.iso], "  ")],
		["camera", exif && info.is_exif_camera_enabled, joined([cameraName(photo.make, photo.model), photo.lens], " · ")],
		["taken_at", exif && info.is_exif_taken_at_enabled, photo.taken_at],
	];
	return candidates.filter(([, enabled, text]) => enabled && text !== null).map(([kind, , text]) => ({ kind, text: text as string }));
}
