<?php

namespace App\Console\Commands;

use App\Configs;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Illuminate\Console\Command;

class generate_thumbs extends Command
{
	/**
	 * @var array
	 */
	const THUMB_TYPES = [
		'small',
		'small2x',
		'medium',
		'medium2x',
	];

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:generate_thumbs {type : thumb name} {amount=100 : amount of photos to process} {timeout=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate intermediate thumbs if missing';

	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions $photoFunctions
	 *
	 * @return void
	 */
	public function __construct(PhotoFunctions $photoFunctions)
	{
		parent::__construct();

		$this->photoFunctions = $photoFunctions;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$type = $this->argument('type');

		if (!in_array($type, self::THUMB_TYPES)) {
			$this->error(sprintf('Type %s is not one of %s', $type, implode(', ', self::THUMB_TYPES)));

			return 1;
		}

		set_time_limit($this->argument('timeout'));

		$multiplier = 1;
		$basicType = $type;
		if (($split = strpos($basicType, '2')) !== false) {
			$basicType = substr($basicType, 0, $split);
			$multiplier = 2;
		}

		$maxWidth = intval(Configs::get_value($basicType . '_max_width')) * $multiplier;
		$maxHeight = intval(Configs::get_value($basicType . '_max_height')) * $multiplier;

		$this->line(
			sprintf(
				'Will attempt to generate up to %s %s (%dx%d) images with a timeout of %d seconds...',
				$this->argument('amount'),
				$type,
				$maxWidth,
				$maxHeight,
				$this->argument('timeout')
			)
		);

		$photos = Photo::where($type, '=', '')
			->where('type', 'like', 'image/%')
			->take($this->argument('amount'))
			->get();

		if (count($photos) == 0) {
			$this->line('No picture requires ' . $type . '.');

			return 0;
		}

		$bar = $this->output->createProgressBar(count($photos));
		$bar->start();

		foreach ($photos as $photo) {
			if ($this->photoFunctions->resizePhoto(
				$photo,
				$type,
				$maxWidth,
				$maxHeight)
			) {
				$photo->save();
				$this->line('   ' . $type . ' (' . $photo->{$type} . ') for ' . $photo->title . ' created.');
			} else {
				$this->line('   Could not create ' . $type . ' for ' . $photo->title . ' (' . $photo->width . 'x' . $photo->height . ').');
			}
			$bar->advance();
		}

		$bar->finish();
		$this->line('  ');
	}
}
