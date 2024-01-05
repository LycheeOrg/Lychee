<?php

namespace Opcodes\LogViewer\Http\Controllers;

use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;
use Opcodes\LogViewer\Http\Middleware\ForwardRequestToHostMiddleware;
use Opcodes\LogViewer\Http\Middleware\JsonResourceWithoutWrappingMiddleware;

Route::get('hosts', [HostsController::class, 'index'])->name('log-viewer.hosts');

Route::middleware([
	ForwardRequestToHostMiddleware::class,
	JsonResourceWithoutWrappingMiddleware::class,
])->group(function () {
	Route::get('folders', [FoldersController::class, 'index'])->name('log-viewer.folders');
	Route::get('folders/{folderIdentifier}/download/request', [FoldersController::class, 'requestDownload'])->name('log-viewer.folders.request-download');
	Route::post('folders/{folderIdentifier}/clear-cache', [FoldersController::class, 'clearCache'])->name('log-viewer.folders.clear-cache');
	Route::delete('folders/{folderIdentifier}', [FoldersController::class, 'delete'])->name('log-viewer.folders.delete');

	Route::get('files', [FilesController::class, 'index'])->name('log-viewer.files');
	Route::get('files/{fileIdentifier}/download/request', [FilesController::class, 'requestDownload'])->name('log-viewer.files.request-download');
	Route::post('files/{fileIdentifier}/clear-cache', [FilesController::class, 'clearCache'])->name('log-viewer.files.clear-cache');
	Route::delete('files/{fileIdentifier}', [FilesController::class, 'delete'])->name('log-viewer.files.delete');

	Route::post('clear-cache-all', [FilesController::class, 'clearCacheAll'])->name('log-viewer.files.clear-cache-all');
	Route::post('delete-multiple-files', [FilesController::class, 'deleteMultipleFiles'])->name('log-viewer.files.delete-multiple-files');

	Route::get('logs', [LogsController::class, 'index'])->name('log-viewer.logs');
});

Route::get('folders/{folderIdentifier}/download', [FoldersController::class, 'download'])
	->middleware(ValidateSignature::class)
	->name('log-viewer.folders.download');

Route::get('files/{fileIdentifier}/download', [FilesController::class, 'download'])
	->middleware(ValidateSignature::class)
	->name('log-viewer.files.download');