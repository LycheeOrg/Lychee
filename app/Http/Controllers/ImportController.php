<?php

/** @noinspection PhpComposerExtensionStubsInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Actions\Import\FromServer;
use App\Actions\Import\FromUrl;
use App\Http\Requests\ImportRequests\ImportServerRequest;
use App\Http\Requests\ImportRequests\ImportUrlRequest;
use Illuminate\Support\Facades\Session;
use ImagickException;

class ImportController extends Controller
{
	/**
	 * @param ImportUrlRequest $request
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
	 * @param ImportServerRequest $request
	 *
	 * @return bool|string
	 *
	 * @throws ImagickException
	 */
	public function server(ImportServerRequest $request, FromServer $fromServer)
	{
		$validated = $request->validated();
		Session::forget('cancel');

		return $fromServer->do($validated);
	}

	public function serverCancel()
	{
		Session::put('cancel', true);

		return 'true';
	}
}
