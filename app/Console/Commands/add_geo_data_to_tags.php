<?php

namespace App\Console\Commands;

use App\Metadata\Extractor;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use App\Configs;
use App\Locale\Lang;
use Storage;
use Geocoder\Query\ReverseQuery;
use Illuminate\Console\Command;
use Illuminate\Cache\ArrayStore;

class add_geo_data_to_tags extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:add_geo_data_to_tags';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Decodes the GPS location data and adds street, city, country, etc. to the tags';

	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * @var Extractor
	 */
	private $metadataExtractor;

	/**
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions $photoFunctions
	 *
	 * @return void
	 */
	public function __construct(PhotoFunctions $photoFunctions, Extractor $metadataExtractor)
	{
		parent::__construct();

		$this->photoFunctions = $photoFunctions;
		$this->metadataExtractor = $metadataExtractor;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$photos = Photo::whereNotNull('latitude')->whereNotNull('longitude')
			->get()
		;

		if (count($photos) == 0) {
			$this->line('No photos or videos require processing.');

			return 0;
		}


		$stack = \GuzzleHttp\HandlerStack::create();
		$stack->push(\Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware::perSecond(1));

		$httpClient = new \GuzzleHttp\Client([
		    'handler' => $stack,
		    'timeout' => 30.0,
		]);

		$httpAdapter = new \Http\Adapter\Guzzle6\Client($httpClient);

		// Use filesystem adapter to cache data
		$filesystemAdapter = new \League\Flysystem\Adapter\Local(Storage::path(''));
		$filesystem = new \League\Flysystem\Filesystem($filesystemAdapter);
		$psr6Cache = new \Cache\Adapter\Filesystem\FilesystemCachePool($filesystem);

		$provider = new \Geocoder\Provider\Nominatim\Nominatim($httpAdapter, 'https://nominatim.openstreetmap.org', 'lychee laravel');
		$formatter = new \Geocoder\Formatter\StringFormatter();

		$cachedProvider = new \Geocoder\Provider\Cache\ProviderCache(
			$provider, // Provider to cache
			$psr6Cache // PSR-6 compatible cache
		);

		$geocoder = new \Geocoder\StatefulGeocoder($cachedProvider, Configs::get_value('lang'));

		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');
			//$existing_tags = explode(',', $photo->tags);

			try {
				$result_list = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($photo->latitude, $photo->longitude));
			} catch (\Exception $e) {
				$this->line(__METHOD__ . __LINE__ . $e->getMessage());
				continue;
			}

			if ($result_list->count() == 0) {
				$this->line('Location (' . $photo->latitude . ', ' . $photo->longitude . ') could not be decoded.');
				continue;
			}
			//$result = $result_list->first();
			$photo->location = $result_list->first()->getDisplayName();
			// remove empty entries
			/*$tags = array_filter($tags);

			// Only add new tag if its not yet in the list of tags
			foreach ($tags as $new_tag) {
				if (!in_array($new_tag, $existing_tags)) {
					array_push($existing_tags, $new_tag);
				}
			}

			$photo->tags = implode(',', array_filter($existing_tags));*/
			$photo->save();
		}
	}
}
