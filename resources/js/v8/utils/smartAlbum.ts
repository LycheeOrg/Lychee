const SMART_ALBUM_IDS = new Set<string>([
	"unsorted",
	"highlighted",
	"recent",
	"on_this_day",
	"untagged",
	"unrated",
	"one_star",
	"two_stars",
	"three_stars",
	"four_stars",
	"five_stars",
	"best_pictures",
	"my_rated_pictures",
	"my_best_pictures",
]);

export function isSmartAlbumId(id: string): boolean {
	return SMART_ALBUM_IDS.has(id);
}
