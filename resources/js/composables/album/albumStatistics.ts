import { TotalAlbum } from "@/components/statistics/TotalCard.vue";

export type DataForTable = { key: string; value: number };

export type PhotoStats = {
	iso: DataForTable[];
	focal: DataForTable[];
	lens: DataForTable[];
	model: DataForTable[];
	shutter: DataForTable[];
	aperture: DataForTable[];
	year: DataForTable[];
	month: DataForTable[];
	day: DataForTable[];
};

export function useAlbumsStatistics() {
	function getStatistics(photos: App.Http.Resources.Models.PhotoResource[]): PhotoStats {
		const stats = {
			iso: {} as Record<string, number>,
			focal: {} as Record<string, number>,
			lens: {} as Record<string, number>,
			model: {} as Record<string, number>,
			shutter: {} as Record<string, number>,
			aperture: {} as Record<string, number>,
			year: {} as Record<string, number>,
			month: {} as Record<string, number>,
			day: {} as Record<string, number>,
		};
		for (const photo of photos) {
			if (photo.precomputed.is_video || photo.precomputed.is_raw) {
				continue;
			}

			if (photo.iso) {
				stats.iso[photo.iso] = stats.iso[photo.iso] ? stats.iso[photo.iso] + 1 : 1;
			}
			if (photo.focal) {
				stats.focal[photo.focal] = stats.focal[photo.focal] ? stats.focal[photo.focal] + 1 : 1;
			}
			if (photo.preformatted.aperture) {
				stats.aperture["ƒ / " + photo.preformatted.aperture] = stats.aperture["ƒ / " + photo.preformatted.aperture]
					? stats.aperture["ƒ / " + photo.preformatted.aperture] + 1
					: 1;
			}
			if (photo.lens) {
				stats.lens[photo.lens] = stats.lens[photo.lens] ? stats.lens[photo.lens] + 1 : 1;
			}
			if (photo.model) {
				stats.model[photo.model] = stats.model[photo.model] ? stats.model[photo.model] + 1 : 1;
			}
			if (photo.preformatted.shutter) {
				stats.shutter[photo.preformatted.shutter] = stats.shutter[photo.preformatted.shutter]
					? stats.shutter[photo.preformatted.shutter] + 1
					: 1;
			}
			if (photo.taken_at) {
				const year = photo.taken_at.slice(0, 4);
				const month = photo.taken_at.slice(0, 7);
				const day = photo.taken_at.slice(0, 10);
				stats.year[year] = stats.year[year] ? stats.year[year] + 1 : 1;
				stats.month[month] = stats.month[month] ? stats.month[month] + 1 : 1;
				stats.day[day] = stats.day[day] ? stats.day[day] + 1 : 1;
			}
		}

		return {
			iso: recordToType(stats.iso),
			focal: recordToType(stats.focal),
			lens: recordToType(stats.lens),
			model: recordToType(stats.model),
			shutter: recordToType(stats.shutter),
			aperture: recordToType(stats.aperture),
			year: recordToType(stats.year),
			month: recordToType(stats.month),
			day: recordToType(stats.day),
		};
	}

	function recordToType(record: Record<string, number>): DataForTable[] {
		const data = [] as DataForTable[];
		Object.entries(record).forEach(([key, value]) => {
			data.push({ key, value });
		});
		return data.sort((a, b) => b.value - a.value);
	}

	function computeTotal(albumsStats: App.Http.Resources.Statistics.Album[]): TotalAlbum {
		const sumData: TotalAlbum = {
			size: 0,
			num_photos: 0,
			num_albums: 0,
		};

		albumsStats.reduce((acc, a) => {
			sumData.size += a.size;
			sumData.num_photos += a.num_photos;
			return acc;
		}, sumData);

		sumData.num_albums = albumsStats.length;
		return sumData;
	}

	return {
		getStatistics,
		computeTotal,
	};
}
