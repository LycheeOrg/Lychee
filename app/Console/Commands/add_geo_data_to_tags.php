<?php

namespace App\Console\Commands;

use App\Metadata\Extractor;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Geocoder\Query\ReverseQuery;
use Illuminate\Console\Command;

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
			$this->line('No videos require processing');

			return 0;
		}

		$httpClient = new \Http\Adapter\Guzzle6\Client();
		$psr6Cache = new \Cache\Adapter\PHPArray\ArrayCachePool();
		$provider = new \Geocoder\Provider\Nominatim\Nominatim($httpClient, 'https://nominatim.openstreetmap.org', 'lychee laravel');
		$formatter = new \Geocoder\Formatter\StringFormatter();

		$cachedProvider = new \Geocoder\Provider\Cache\ProviderCache(
			$provider, // Provider to cache
			$psr6Cache // PSR-6 compatible cache
		);

		$geocoder = new \Geocoder\StatefulGeocoder($cachedProvider, 'en');

		foreach ($photos as $photo) {
			$this->line('Processing ' . $photo->title . '...');
			$existing_tags = explode(',', $photo->tags);

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
			$result = $result_list->first();

			$tags = [];
			$tags[] = trim($formatter->format($result, '%S %n'));
			$tags[] = trim($formatter->format($result, '%D'));
			$tags[] = trim($formatter->format($result, '%L'));
			$tags[] = trim($formatter->format($result, '%A1'));
			$tags[] = trim($formatter->format($result, '%A2'));
			$tags[] = trim($formatter->format($result, '%A3'));
			$tags[] = trim($formatter->format($result, '%A4'));
			$tags[] = trim($formatter->format($result, '%A5'));

			// Country code seems to be more reliable
			$country_code = trim($formatter->format($result, '%c'));
			$data_ISO3166 = (new \League\ISO3166\ISO3166())->alpha2($country_code);

			if (isset($data_ISO3166['name'])) {
				$tags[] = $data_ISO3166['name'];
			}
			// remove empty entries
			$tags = array_filter($tags);

			// Only add new tag if its not yet in the list of tags
			foreach ($tags as $new_tag) {
				if (!in_array($new_tag, $existing_tags)) {
					array_push($existing_tags, $new_tag);
				}
			}

			$photo->tags = implode(',', array_filter($existing_tags));
			$photo->save();
		}
	}
}
