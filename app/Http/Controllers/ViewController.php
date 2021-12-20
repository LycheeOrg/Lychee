<?php

namespace App\Http\Controllers;

use App\Http\Requests\Photo\GetPhotoRequest;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ViewController extends Controller
{
	/**
	 * Just the constructor
	 * This also shows how to apply a middleware directly in a controller.
	 *
	 * ViewController constructor.
	 */
	public function __construct()
	{
		$this->middleware([]);
	}

	/**
	 * View is only used when sharing a single picture.
	 *
	 * @param GetPhotoRequest $request
	 *
	 * @return View
	 *
	 * @throws ModelNotFoundException
	 * @throws BindingResolutionException
	 */
	public function view(GetPhotoRequest $request): View
	{
		/** @var Photo $photo */
		$photo = Photo::query()->findOrFail($request->photoID());

		$sizeVariant = $photo->size_variants->getSizeVariant(SizeVariant::MEDIUM);
		if ($sizeVariant === null) {
			$sizeVariant = $photo->size_variants->getSizeVariant(SizeVariant::ORIGINAL);
		}

		$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$rss_enable = Configs::get_value('rss_enable', '0') == '1';

		try {
			$url = config('app.url') . $request->server->get('REQUEST_URI');
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
		}
		$picture = $sizeVariant->url;

		return view('view', [
			'url' => $url,
			'photo' => $photo,
			'picture' => $picture,
			'title' => $title,
			'rss_enable' => $rss_enable,
		]);
	}
}
