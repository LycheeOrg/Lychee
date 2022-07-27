<?php

namespace App\Http\Controllers;

use App\Actions\Import\FromServer;
use App\Actions\Import\FromUrl;
use App\Exceptions\MassImportException;
use App\Http\Requests\Import\ImportFromUrlRequest;
use App\Http\Requests\Import\ImportServerRequest;
use App\Models\Photo;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ImportController.
 *
 * This controller handles requests to import photos from a sequence of
 * URLs or from a local path on the server.
 * Note, the methods follow quite different approaches which also
 * significantly affects the format of the responses.
 *
 * Both methods may take some time to finish, if the number of photos is large.
 * While {@link ImportController::server()} returns a streamed response with
 * intermediate updates, {@link ImportController::url} returns an atomic
 * response at the end.
 * The former approach appears to be more appropriate in order to avoid
 * timeout errors at the client side.
 * While {@link ImportController::url} returns a well-formed and
 * syntactically valid JSON response which is a properly serialized collection
 * of the imported photos, {@link ImportController::server()} just streams
 * the terminal output of the command line.
 * The latter cannot properly be interpreted by web-clients which
 * expect a proper JSON response.
 * Actually, both methods should be re-factored such that their return
 * types are consistent and the best of both worlds: a streamed collection.
 * In other words, the streamed response should immediately send back a
 * `[`-character to the client (the beginning of the collection), then
 * send back a JSONized {@link Photo} as soon as it has been imported
 * (element of the collection, and send a final `]`-character (the end of
 * the collection).
 *
 * TODO: Refactor this, see problem description above.
 */
class ImportController extends Controller
{
	/**
	 * @param ImportFromUrlRequest $request
	 * @param FromUrl              $fromUrl
	 *
	 * @return Collection<Photo>
	 *
	 * @throws MassImportException
	 */
	public function url(ImportFromUrlRequest $request, FromUrl $fromUrl): Collection
	{
		return $fromUrl->do($request->urls(), $request->album());
	}

	/**
	 * @param ImportServerRequest $request
	 * @param FromServer          $fromServer
	 *
	 * @return StreamedResponse
	 */
	public function server(ImportServerRequest $request, FromServer $fromServer): StreamedResponse
	{
		return $fromServer->do(
			$request->path(), $request->album(), $request->importMode()
		);
	}

	/**
	 * @return void
	 */
	public function serverCancel(): void
	{
		Session::put('cancel', true);
	}
}
