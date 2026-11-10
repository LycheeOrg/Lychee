import AlbumService from "@/services/album-service";

export function usePreviewData() {
	function getSizeVariantSizeData(): App.Http.Resources.Statistics.Sizes[] {
		return [
			{
				type: 0,
				label: "Original",
				size: Math.floor(Math.random() * 1000_000_000_000),
			},
			{
				type: 1,
				label: "Medium HiDPI",
				size: Math.floor(Math.random() * 1_000_000_000),
			},
			{
				type: 2,
				label: "Medium",
				size: Math.floor(Math.random() * 100_000_000_000),
			},
			{
				type: 4,
				label: "Thumb",
				size: Math.floor(Math.random() * 10_000_000_000),
			},
			{
				type: 5,
				label: "Square thumb HiDPI",
				size: Math.floor(Math.random() * 1_000_000_000),
			},
			{
				type: 6,
				label: "Square thumb",
				size: Math.floor(Math.random() * 1_000_000_000),
			},
		];
	}

	function getAlbumSizeData(): Promise<App.Http.Resources.Statistics.Album[]> {
		const data = [] as App.Http.Resources.Statistics.Album[];

		return AlbumService.getTargetListAlbums(null).then((response) => {
			for (let i = 0; i < response.data.length; i++) {
				const album = response.data[i];
				data.push({
					id: album.id ?? `preview-${i}`,
					username: "demo",
					title: album.original,
					is_nsfw: false,
					left: 2 * i + 1,
					right: 2 * i + 2,
					num_photos: Math.floor(Math.random() * 100),
					num_descendants: Math.floor(Math.random() * 100),
					size: Math.floor(Math.random() * 1000000),
				});
			}

			return data;
		});
	}

	/**
	 * Random Insights for the SE preview (Feature 085, FR-085-01): one year of
	 * photos, no API call.
	 */
	function getInsightsData(): App.Http.Resources.Insights.InsightsResource {
		const random = (max: number) => Math.floor(Math.random() * max);
		const year = new Date().getFullYear();
		const dates: string[] = [];
		const counts: number[] = [];
		for (let day = 0; day < 365; day += 1 + random(4)) {
			dates.push(new Date(Date.UTC(year, 0, 1 + day)).toISOString().slice(0, 10));
			counts.push(1 + random(60));
		}
		const total = counts.reduce((sum, count) => sum + count, 0);
		const weekHour = Array.from({ length: 7 }, () => Array.from({ length: 24 }, (_, hour) => (hour > 7 && hour < 22 ? random(40) : random(3))));
		const distribution = (values: number[]): App.Http.Resources.Insights.DistributionData => {
			const valueCounts = values.map(() => 1 + random(300));
			return {
				values,
				counts: valueCounts,
				total: valueCounts.reduce((sum, count) => sum + count, 0),
				excluded: random(50),
				min: values[0],
				max: values[values.length - 1],
				median: values[Math.floor(values.length / 2)],
				mean: values[Math.floor(values.length / 2)],
				mode: values[random(values.length)],
			};
		};
		const device = (name: string, category: App.Enum.DeviceCategory): App.Http.Resources.Insights.DeviceEntryData => {
			const all = 50 + random(2000);
			return { name, category, all, photos: all, videos: 0, highlighted: random(50), located: random(all), with_people: random(all) };
		};

		return {
			years: [year],
			overview: {
				total,
				photos: total - 40,
				videos: 40,
				others: 0,
				highlighted: random(100),
				albums: 10 + random(50),
				photos_without_album: random(200),
			},
			storage: { total_size: total * 5_000_000, size_unknown: 0, average_photo_size: 5_000_000, average_video_size: 80_000_000 },
			people: { has_faces: true, photos_with_people: random(total), people: 5 + random(40), faces: total, faces_per_photo: 1.8 },
			places: { located: random(total), share: Math.random() },
			time_span: {
				first: null,
				last: null,
				busiest_day: null,
				days_with_photos: dates.length,
				calendar_days: 365,
				undated: random(30),
				longest_break: { length: 4, from: dates[0], to: dates[1] },
				longest_daily_streak: { length: 3, from: dates[0], to: dates[0] },
				longest_weekly_streak: { length: 12, from: dates[0], to: dates[0] },
			},
			calendar: { dates, counts, low: 10, medium: 30, high: 50 },
			rhythm: {
				week_hour: weekHour,
				months: Array.from({ length: 12 }, () => random(500)),
				weekdays: weekHour.map((row) => row.reduce((sum, count) => sum + count, 0)),
				hours: Array.from({ length: 24 }, (_, hour) => weekHour.reduce((sum, row) => sum + row[hour], 0)),
			},
			devices: {
				devices: [device("Fujifilm X-T4", "camera"), device("Apple iPhone 15", "mobile"), device("Nikon Z6", "camera")],
				manufacturers: [device("Fujifilm", "camera"), device("Apple", "mobile"), device("Nikon", "camera")],
				lenses: [device("XF16-55mmF2.8 R LM WR", "camera"), device("iPhone 15 back camera", "mobile")],
				focal_lengths: [
					{
						name: "Fujifilm X-T4",
						category: "camera",
						values: [16, 23, 35, 55],
						counts: [40 + random(200), 20 + random(80), 60 + random(300), 10 + random(50)],
					},
					{ name: "Apple iPhone 15", category: "mobile", values: [6, 9], counts: [100 + random(400), 20 + random(60)] },
				],
			},
			exposure: {
				iso: distribution([100, 200, 400, 800, 1600, 3200]),
				focal: distribution([16, 23, 35, 50, 85]),
				shutter: distribution([1 / 2000, 1 / 500, 1 / 250, 1 / 60, 1 / 15]),
				aperture: distribution([1.4, 2, 2.8, 4, 5.6, 8]),
				video_length: distribution([5, 12, 30, 60]),
				total_video_duration: 3600,
			},
			formats: {
				all: { portrait: random(300), landscape: 300 + random(900), square: random(40), unknown: random(10) },
				photos: { portrait: random(300), landscape: 300 + random(800), square: random(40), unknown: 0 },
				videos: { portrait: random(20), landscape: random(40), square: 0, unknown: random(10) },
				aspect_ratios: [
					{ group: "1:1", count: random(40) },
					{ group: "5:4", count: random(10) },
					{ group: "4:3", count: random(400) },
					{ group: "3:2", count: 200 + random(800) },
					{ group: "16:9", count: random(200) },
					{ group: "2:1", count: random(10) },
					{ group: "panorama", count: random(10) },
					{ group: "other", count: random(30) },
				],
				dimensions: {
					widths: [6240, 4160, 4032, 3024, 1920, 4000],
					heights: [4160, 6240, 3024, 4032, 1080, 4000],
					counts: [500 + random(500), 100 + random(300), 100 + random(300), random(200), random(100), random(40)],
					formats: 6,
					with_dimensions: total,
				},
			},
			timeline: [
				{ kind: "first_capture", category: "first_last", date: dates[0], subject: null, value: null, photo_id: null },
				{ kind: "device_first", category: "device", date: dates[2], subject: "Fujifilm X-T4", value: null, photo_id: null },
				{ kind: "milestone", category: "milestone", date: dates[Math.floor(dates.length / 3)], subject: null, value: 100, photo_id: null },
				{ kind: "longest_video", category: "record", date: dates[Math.floor(dates.length / 2)], subject: null, value: 754, photo_id: null },
				{
					kind: "device_first",
					category: "device",
					date: dates[Math.floor((dates.length * 2) / 3)],
					subject: "Apple iPhone 15",
					value: null,
					photo_id: null,
				},
				{ kind: "last_capture", category: "first_last", date: dates[dates.length - 1], subject: null, value: null, photo_id: null },
			],
		};
	}

	return {
		getSizeVariantSizeData,
		getAlbumSizeData,
		getInsightsData,
	};
}
