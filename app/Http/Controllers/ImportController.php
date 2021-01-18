<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Actions\Import\FromServer;
use App\Actions\Import\FromUrl;
use App\Http\Requests\ImportRequests\ImportServerRequest;
use App\Http\Requests\ImportRequests\ImportUrlRequest;
use Illuminate\Http\Request;
use ImagickException;

class ImportController extends Controller
{
	/**
	 * @param Request $request
	 *
	 * @return false|string
	 */
	public function url(ImportUrlRequest $request, FromUrl $fromUrl)
	{
		// Parse URLs
		$urls = $request['url'];
		$urls = str_replace(' ', '%20', $urls);
		$urls = explode(',', $urls);

		return $fromUrl->do($urls, $request['albumID']) ? 'true' : 'false';
	}

	/**
	 * @param Request $request
	 *
	 * @return bool|string
	 *
	 * @throws ImagickException
	 */
	public function server(ImportServerRequest $request, FromServer $fromServer)
	{
		$validated = $request->validated();

		return $fromServer->do($validated);
	}
}
