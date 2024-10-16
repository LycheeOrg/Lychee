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
		let data = [] as App.Http.Resources.Statistics.Album[];

		return AlbumService.getTargetListAlbums(null).then((response) => {
			for (let i = 0; i < response.data.length; i++) {
				let album = response.data[i];
				data.push({
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

	return {
		getSizeVariantSizeData,
		getAlbumSizeData,
	};
}
