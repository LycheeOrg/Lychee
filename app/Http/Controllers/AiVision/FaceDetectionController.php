<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Controllers\AiVision;

use App\Enum\FaceScanStatus;
use App\Http\Requests\Face\BulkScanRequest;
use App\Http\Requests\Face\ClusterResultsRequest;
use App\Http\Requests\Face\FaceDetectionResultsRequest;
use App\Http\Requests\Face\ScanPhotosRequest;
use App\Models\Face;
use App\Models\FaceSuggestion;
use App\Models\Photo;
use App\Services\FaceDetectionService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function Safe\base64_decode;
use Safe\Exceptions\MiscException;

/**
 * Controller for AI Vision face detection scan management.
 *
 * scan()    — POST /FaceDetection/scan  — dispatches jobs to Python service
 * results() — POST /FaceDetection/results — receives callback from Python service
 * bulkScan()— POST /FaceDetection/bulk-scan — admin: enqueue all unscanned photos
 */
class FaceDetectionController extends Controller
{
	/** @var float IoU threshold below which old face associations are not preserved. */
	private const DEFAULT_IOU_THRESHOLD = 0.3;

	/**
	 * Trigger face detection for specific photos or all photos in an album.
	 *
	 * POST /FaceDetection/scan
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function scan(ScanPhotosRequest $request, FaceDetectionService $service): \Illuminate\Http\Response
	{
		$photo_ids = $request->photoIds();
		$album_id = $request->album()?->id;
		$force = $request->force();

		$service->dispatchPhotos($photo_ids, $album_id, $force);

		return response()->noContent(202);
	}

	/**
	 * Receive face detection results callback from the Python service.
	 * Authentication is exclusively via X-API-Key header; no user session required.
	 *
	 * POST /FaceDetection/results
	 *
	 * @return array<string,array<array{embedding_id:string,lychee_face_id:string}>>
	 */
	public function results(FaceDetectionResultsRequest $request): array
	{
		$photo_id = $request->photoId();

		if ($request->status() === 'error') {
			Photo::where('id', '=', $photo_id)->update(['face_scan_status' => FaceScanStatus::FAILED->value]);
			Log::info("FaceDetectionController::results — photo {$photo_id} face scan failed: " . ($request->message() ?? 'unknown error'));

			return ['faces' => []];
		}

		// Process success payload
		$mapping = $this->processFaceResults($photo_id, $request->faces());

		Photo::where('id', '=', $photo_id)->update(['face_scan_status' => FaceScanStatus::COMPLETED->value]);

		return ['faces' => $mapping];
	}

	/**
	 * Admin: enqueue all photos with face_scan_status IS NULL for face detection.
	 *
	 * POST /FaceDetection/bulk-scan
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function bulkScan(BulkScanRequest $request, FaceDetectionService $service): \Illuminate\Http\Response
	{
		$album_id = $request->album()?->id;

		$service->dispatchUnscanedPhotos($album_id);

		return response()->noContent(202);
	}

	/**
	 * Receive face clustering results callback from the Python service.
	 * Authentication is exclusively via X-API-Key header; no user session required.
	 *
	 * POST /FaceDetection/cluster-results
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function clusterResults(ClusterResultsRequest $request): \Illuminate\Http\Response
	{
		DB::transaction(function () use ($request): void {
			// Reset all existing cluster labels so stale assignments are removed.
			Face::whereNotNull('cluster_label')->update(['cluster_label' => null]);

			// Apply new cluster labels in bulk.
			foreach ($request->labels() as $item) {
				Face::where('id', '=', $item['face_id'])
					->update(['cluster_label' => $item['cluster_label']]);
			}

			// Upsert cross-cluster suggestions.
			foreach ($request->suggestions() as $sug) {
				FaceSuggestion::updateOrCreate(
					['face_id' => $sug['face_id'], 'suggested_face_id' => $sug['suggested_face_id']],
					['confidence' => $sug['confidence']],
				);
			}
		});

		Log::info('FaceDetectionController::clusterResults — updated ' . count($request->labels()) . ' cluster labels.');

		return response()->noContent(202);
	}

	/**
	 * Process incoming face detection results for a photo.
	 * IoU-matches new faces against old faces to preserve person assignments.
	 *
	 * @param string                                                                                                                                                                     $photo_id
	 * @param array<array{x:float,y:float,width:float,height:float,confidence:float,embedding_id:string,crop?:string,suggestions?:array<array{lychee_face_id:string,confidence:float}>}> $incoming_faces
	 *
	 * @return array<array{embedding_id:string,lychee_face_id:string}>
	 */
	private function processFaceResults(string $photo_id, array $incoming_faces): array
	{
		$iou_threshold = (float) config('features.ai-vision.face-rescan-iou-threshold', self::DEFAULT_IOU_THRESHOLD);

		// Load existing faces for this photo
		$old_faces = Face::where('photo_id', '=', $photo_id)->get();

		// For each incoming face, find best IoU match among old faces
		// Track matched old face indices to avoid double-matching
		$matched_old_indices = [];
		$person_ids_for_new = array_fill(0, count($incoming_faces), null);

		foreach ($incoming_faces as $new_idx => $new_face) {
			$best_iou = $iou_threshold;
			$best_old_idx = null;

			foreach ($old_faces as $old_idx => $old_face) {
				if (in_array($old_idx, $matched_old_indices, true)) {
					continue;
				}

				$iou = $this->computeIoU(
					$new_face['x'], $new_face['y'], $new_face['width'], $new_face['height'],
					$old_face->x, $old_face->y, $old_face->width, $old_face->height
				);

				if ($iou > $best_iou) {
					$best_iou = $iou;
					$best_old_idx = $old_idx;
				}
			}

			if ($best_old_idx !== null) {
				$matched_old_indices[] = $best_old_idx;
				$person_ids_for_new[$new_idx] = $old_faces[$best_old_idx]->person_id;
			}
		}

		// Delete ALL old face records (and their crops) — they will be replaced
		foreach ($old_faces as $old_face) {
			$this->deleteCropFile($old_face->crop_token);
		}
		Face::where('photo_id', '=', $photo_id)->delete();
		FaceSuggestion::whereNotExists(function ($query): void {
			$query->select(DB::raw(1))->from('faces')->whereColumn('face_suggestions.face_id', 'faces.id');
		})->delete();

		// Create new face records and return embedding_id → lychee_face_id mapping
		$mapping = [];

		foreach ($incoming_faces as $idx => $face_data) {
			$tok = Str::random(24);
			$crop_path = 'faces/' . substr($tok, 0, 2) . '/' . substr($tok, 2, 2) . '/' . $tok . '.jpg';

			// Store crop file if provided
			if (isset($face_data['crop']) && $face_data['crop'] !== '') {
				try {
					$decoded = base64_decode($face_data['crop']);
					Storage::disk('images')->put($crop_path, $decoded);
				} catch (MiscException) {
					$tok = null;
				}
			} else {
				$tok = null;
			}

			$face = new Face();
			$face->photo_id = $photo_id;
			$face->person_id = $person_ids_for_new[$idx];
			$face->x = $face_data['x'];
			$face->y = $face_data['y'];
			$face->width = $face_data['width'];
			$face->height = $face_data['height'];
			$face->confidence = $face_data['confidence'];
			$face->laplacian_variance = $face_data['laplacian_variance'] ?? 0.0;
			$face->crop_token = $tok;
			$face->is_dismissed = false;
			$face->save();

			// We filter out the id which no longer exists.
			$suggestions = $face_data['suggestions'] ?? [];
			$suggestion_ids = collect($suggestions)->map(fn ($sug) => $sug['lychee_face_id']);
			$existing_ids = Face::whereIn('id', $suggestion_ids)->pluck('id')->all();
			$valid_suggestions = collect($suggestions)->filter(fn ($sug) => in_array($sug['lychee_face_id'], $existing_ids, true));

			// Create suggestion records
			foreach ($valid_suggestions as $suggestion) {
				$sug = new FaceSuggestion();
				$sug->face_id = $face->id;
				$sug->suggested_face_id = $suggestion['lychee_face_id'];
				$sug->confidence = $suggestion['confidence'];
				$sug->save();
			}

			$mapping[] = [
				'embedding_id' => $face_data['embedding_id'],
				'lychee_face_id' => $face->id,
			];
		}

		return $mapping;
	}

	/**
	 * Compute Intersection over Union (IoU) for two bounding boxes.
	 * All coordinates in normalized [0,1] space.
	 */
	private function computeIoU(
		float $x1, float $y1, float $w1, float $h1,
		float $x2, float $y2, float $w2, float $h2,
	): float {
		$ix = max(0.0, min($x1 + $w1, $x2 + $w2) - max($x1, $x2));
		$iy = max(0.0, min($y1 + $h1, $y2 + $h2) - max($y1, $y2));
		$inter = $ix * $iy;

		if ($inter === 0.0) {
			return 0.0;
		}

		$union = $w1 * $h1 + $w2 * $h2 - $inter;

		return $union > 0.0 ? $inter / $union : 0.0;
	}

	/**
	 * Delete a face crop file from storage if the token is set.
	 */
	private function deleteCropFile(?string $crop_token): void
	{
		if ($crop_token === null) {
			return;
		}

		$path = 'faces/' . substr($crop_token, 0, 2) . '/' . substr($crop_token, 2, 2) . '/' . $crop_token . '.jpg';
		Storage::disk('images')->delete($path);
	}
}
