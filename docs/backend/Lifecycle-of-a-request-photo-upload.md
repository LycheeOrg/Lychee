# Lifecycle of a Request in Lychee: Photo Upload

This document traces the complete lifecycle of photo upload in Lychee, including the chunked upload process, file processing, and metadata extraction. Understanding this flow will help developers navigate the upload mechanism and related processing systems.

## Upload Flow Overview

```
1. Frontend (Upload UI) → 2. File Chunking → 3. Route → 4. Middleware → 5. Request Validation → 6. Controller → 7. Chunk Assembly → 8. Processing (Job/Sync) → 9. Action Pipeline → 10. Size Variants → 11. Database → 12. Response → 13. Frontend Update
```

## Example: Uploading a Photo

Let's trace a photo upload from the user selecting files to the final processed photo being available in the gallery.

### 1. Frontend Upload Initiation (Vue.js)

The lifecycle begins when a user selects files in the upload dialog:

```vue
<!-- File: resources/js/components/modals/UploadPanel.vue -->
<input v-on:change="upload" type="file" id="myFiles" multiple class="hidden" />
```

When files are selected, they are added to the upload queue:

```typescript
function upload(event: Event) {
  const target = event.target as HTMLInputElement;
  if (target.files === null) return;

  for (let i = 0; i < target.files.length; i++) {
    list_upload_files.value.push({ 
      file: target.files[i], 
      status: "waiting" 
    });
  }
  
  // Start processing uploads with configured limit
  uploadNext(0, setup.value?.upload_processing_limit);
}
```

### 2. File Chunking Process

Large files are split into smaller chunks for reliable upload:

```typescript
// File: resources/js/components/forms/upload/UploadingLine.vue
const meta = ref({
  file_name: file.value.name,
  extension: null,
  uuid_name: null,
  stage: "uploading",
  chunk_number: 0,
  total_chunks: Math.ceil(size.value / props.chunkSize),
} as App.Http.Resources.Editable.UploadMetaResource);

function process() {
  meta.value.chunk_number = meta.value.chunk_number + 1;
  const chunkEnd = Math.min(chunkStart.value + props.chunkSize, size.value);
  const chunk = file.value.slice(chunkStart.value, chunkEnd);
  
  const data: UploadData = {
    album_id: props.albumId,
    file: chunk,
    file_last_modified_time: file.value.lastModified,
    meta: meta.value,
    onUploadProgress: (progressEvent) => {
      // Update progress bar
      const percent = progressEvent.loaded / (progressEvent.total ?? 1);
      progress.value = Math.round(((chunkStart.value + percent * (chunkEnd - chunkStart.value)) / size.value) * 100);
    },
  };

  UploadService.upload(data, controller.value)
    .then((response) => {
      meta.value = response.data;
      if (response.data.chunk_number === response.data.total_chunks) {
        // Upload complete
        progress.value = 100;
        status.value = "done";
      } else {
        // Continue with next chunk
        chunkStart.value += props.chunkSize;
        process();
      }
    });
}
```

**Key Chunking Features:**
- **Chunk Size**: Configured via `upload_chunk_size` setting
- **Progress Tracking**: Real-time progress updates per chunk
- **Error Recovery**: Failed chunks can be retried
- **Parallel Processing**: Multiple files upload simultaneously (up to `upload_processing_limit`)

### 3. Upload Service (UploadService.upload)

The frontend uses a dedicated service to handle the HTTP request:

```typescript
// File: resources/js/services/upload-service.ts
upload(info: UploadData, abortController: AbortController): Promise<AxiosResponse<UploadMetaResource>> {
  const formData = new FormData();

  formData.append("file", info.file, info.meta.file_name);
  formData.append("file_name", info.meta.file_name);
  formData.append("album_id", info.album_id ?? "");
  formData.append("file_last_modified_time", info.file_last_modified_time?.toString() ?? "");
  formData.append("uuid_name", info.meta.uuid_name ?? "");
  formData.append("extension", info.meta.extension ?? "");
  formData.append("chunk_number", info.meta.chunk_number?.toString() ?? "");
  formData.append("total_chunks", info.meta.total_chunks?.toString() ?? "");

  const config: AxiosRequestConfig<FormData> = {
    onUploadProgress: info.onUploadProgress,
    headers: { "Content-Type": "application/json" },
    signal: abortController.signal,
    transformRequest: [(data) => data],
  };

  return axios.post(`${Constants.getApiUrl()}Photo`, formData, config);
}
```

### 4. Route Resolution

Laravel resolves the upload request:

```php
// File: routes/api_v2.php
Route::post('/Photo', [Gallery\PhotoController::class, 'upload'])
  ->middleware(['throttle:upload']);
```

### 5. Request Validation & Authorization

The request is validated using a dedicated Request class:

```php
// File: app/Http/Requests/Photo/UploadPhotoRequest.php
class UploadPhotoRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $this->album]);
    }

    public function rules(): array
    {
        return [
            RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new AlbumIDRule(true)],
            RequestAttribute::FILE_LAST_MODIFIED_TIME => 'sometimes|nullable|numeric',
            RequestAttribute::FILE_ATTRIBUTE => ['required', 'file'],
            'file_name' => 'required|string',
            'uuid_name' => ['present', new FileUuidRule()],
            'extension' => ['present', new ExtensionRule()],
            'chunk_number' => 'required|integer|min:1',
            'total_chunks' => 'required|integer|gte:chunk_number',
        ];
    }

    protected function processValidatedValues(array $values, array $files): void
    {
        $this->album = $this->album_factory->findNullalbleAbstractAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
        $this->file_last_modified_time = $values[RequestAttribute::FILE_LAST_MODIFIED_TIME] ?? null;
        $this->file_chunk = $files[RequestAttribute::FILE_ATTRIBUTE];
        $this->meta = new UploadMetaResource(
            file_name: $values['file_name'],
            extension: $values['extension'] ?? null,
            uuid_name: $values['uuid_name'] ?? null,
            stage: FileStatus::UPLOADING,
            chunk_number: $values['chunk_number'],
            total_chunks: $values['total_chunks'],
        );
    }
}
```

**Validation Steps:**
1. **Authorization**: Check if user can upload to target album
2. **File Validation**: Ensure uploaded chunk is valid
3. **Extension Validation**: Verify file extension is supported
4. **Chunk Validation**: Ensure chunk number and total chunks are valid
5. **UUID Validation**: Verify unique file identifier

Important considerations: During the first upload, the `uuid_name` should be empty.
It is generated by the server and used to know to which block of photo data we are appending the current chunk. Read more here: [Lychee Discussions #3518](https://github.com/LycheeOrg/Lychee/discussions/3518)

### 6. Controller Processing

The PhotoController handles the upload request:

```php
// File: app/Http/Controllers/Gallery/PhotoController.php
public function upload(UploadPhotoRequest $request): UploadMetaResource
{
    $meta = $request->meta();
    $file = new UploadedFile($request->uploaded_file_chunk());

    // Set up metadata if not already present
    $meta->extension ??= '.' . pathinfo($meta->file_name, PATHINFO_EXTENSION);
    $meta->uuid_name ??= strtr(base64_encode(random_bytes(12)), '+/', '-_') . $meta->extension;

    // Append chunk to final file
    $final = new NativeLocalFile(Storage::disk(FileSystem::IMAGE_UPLOAD)->path($meta->uuid_name));
    $final->append($file->read());

    if ($meta->chunk_number < $meta->total_chunks) {
        // Not the last chunk - return current status
        return $meta;
    }

    // Last chunk - proceed to processing
    $meta->stage = FileStatus::PROCESSING;
    return $this->process($final, $request->album(), $request->file_last_modified_time(), $meta);
}
```

### 7. Chunk Assembly Process

**For Intermediate Chunks (1 to n-1):**
1. **File Creation**: Create or open the target file using UUID name
2. **Chunk Append**: Append the current chunk to the file
3. **Progress Update**: Return current upload status
4. **Memory Management**: Release chunk data immediately

**For Final Chunk (n):**
1. **File Completion**: Append final chunk to complete the file
2. **Validation**: Verify file integrity
3. **Processing Initiation**: Begin image processing pipeline

### 8. Processing Decision (Queue vs Synchronous)

Based on configuration, processing occurs either immediately or via job queue:

```php
private function process(
    NativeLocalFile $final,
    ?AbstractAlbum $album,
    ?int $file_last_modified_time,
    UploadMetaResource $meta
): UploadMetaResource {
    $processable_file = new ProcessableJobFile(
        $final->getOriginalExtension(),
        $meta->file_name
    );
    $processable_file->write($final->read());

    ProcessImageJob::dispatch($processable_file, $album, $file_last_modified_time);
    $meta->stage = config('queue.default') === 'sync' ? FileStatus::DONE : FileStatus::READY;
    return $meta;
}
```

### 9. Image Processing Job

The ProcessImageJob handles the actual photo creation and processing:

```php
// File: app/Jobs/ProcessImageJob.php
public function handle(AlbumFactory $album_factory): void
{
    try {
        $this->history->status = JobStatus::STARTED;
        $this->history->save();

        // Convert to TemporaryJobFile for processing
        $temp_file = new TemporaryJobFile(
            $this->file_path,
            $this->original_base_name
        );

        // Create the photo using Action pattern
        $create = new Create(
            new ImportMode(
                skip_duplicates: false,
                import_via_symlink: false,
                delete_imported: true,
                force_duplicate_check: false
            ),
            $this->user_id
        );

        $album = $album_factory->findNullalbleAbstractAlbumOrFail($this->album_id);
        $photo = $create->add($temp_file, $album, $this->file_last_modified_time);

        $this->history->status = JobStatus::SUCCESS;
        $this->history->save();

    } catch (Exception $e) {
        $this->history->status = JobStatus::FAILURE;
        $this->history->save();
        throw $e;
    }
}
```

### 10. Photo Creation Action Pipeline

The Create action orchestrates the photo processing through a pipeline system:

```php
// File: app/Actions/Photo/Create.php
public function add(NativeLocalFile $source_file, ?AbstractAlbum $album, ?int $file_last_modified_time): Photo
{
    // Pre-processing pipeline
    $pre_pipes = [
        Init\CreateInitialDTO::class,
        Init\SetTakenAt::class,
        Init\VerifyChecksum::class,
        Init\FindDuplicate::class,
    ];

    $init_dto = app(Pipeline::class)
        ->send($init_dto)
        ->through($pre_pipes)
        ->thenReturn();

    if ($init_dto->duplicate !== null) {
        return $this->handleDuplicate($init_dto);
    }

    // Post-processing pipeline
    $post_pipes = [
        Init\InitParentAlbum::class,
        Init\LoadFileMetadata::class,
        Init\FindLivePartner::class,
    ];

    $init_dto = app(Pipeline::class)
        ->send($init_dto)
        ->through($post_pipes)
        ->thenReturn();

    // Handle different photo types
    if ($init_dto->live_partner === null) {
        return $this->handleStandalone($init_dto);
    }

    // Handle Live Photos (if applicable)
    // ...
}
```

For a comprehensive understanding of the photo creation pipeline, including all pipe interfaces, DTOs, and processing stages, see the **[Photo Actions Documentation](../../app/Actions/Photo/README.md)** which provides detailed technical documentation about the Action Pattern implementation and pipeline architecture used in photo processing.

### 11. Metadata Extraction

During processing, comprehensive metadata is extracted:

```php
// File: app/Actions/Photo/Pipes/Standalone/ExtractMetadata.php
class ExtractMetadata implements Pipe
{
    public function handle(UploadDTO $upload_dto, Closure $next): UploadDTO
    {
        $source_file = $upload_dto->source_file;
        
        // Extract EXIF data
        $exif_reader = new ExifReader($source_file);
        $upload_dto->exif_dto = $exif_reader->read();
        
        // Extract GPS coordinates
        if ($upload_dto->exif_dto->gps !== null) {
            $upload_dto->coordinates = $this->extractCoordinates($upload_dto->exif_dto->gps);
        }
        
        // Extract camera information
        $upload_dto->camera_info = $this->extractCameraInfo($upload_dto->exif_dto);
        
        return $next($upload_dto);
    }
}
```

**Extracted Metadata:**
- **EXIF Data**: Camera settings, exposure, focal length, etc.
- **GPS Coordinates**: Location information (if available)
- **Timestamps**: When photo was taken vs. uploaded
- **Camera Information**: Make, model, lens information
- **Technical Details**: Dimensions, file size, format
- **Color Profile**: ICC profile information

### 12. Size Variant Generation

For standalone photos, multiple size variants are created:

```php
private function handleStandalone(UploadDTO $init_dto): Photo
{
    $pipes = [
        Standalone\Init::class,
        Standalone\ExtractMetadata::class,
        Standalone\GenerateSizeVariants::class,
        Shared\CreatePhoto::class,
        Shared\CreateSizeVariant::class,
        Shared\Save::class,
        Shared\SaveStatistics::class,
    ];

    return $this->executePipeOnDTO($pipes, $init_dto)->getPhoto();
}
```

**Size Variants Created:**
- **Original**: Full-resolution uploaded image
- **Medium**: Web-optimized version (typically max 1080px)
- **Medium2x**: Higher resolution for retina displays (2x medium size)
- **Small**: Thumbnail version (typically max 320px)
- **Small2z**: Higher resolution for retina displays (typically 2x small size)
- **Thumb**: Small thumbnail for icons views.
- **Thumb2x**: Higher resolution thumbnail


### 13. Database Operations

Multiple database operations occur during photo creation:

```sql
-- 1. Insert photo record
INSERT INTO photos (
    id, title, description, owner_id, album_id, 
    taken_at, created_at, updated_at, filesize,
    original_checksum, live_photo_checksum,
    latitude, longitude, camera_make, camera_model,
    iso, aperture, focal, shutter, lens
) VALUES (...);

-- 2. Insert size variants
INSERT INTO size_variants (
    photo_id, type, url, width, height, filesize
) VALUES 
    ('photo-uuid', 'ORIGINAL', 'path/to/original.jpg', 4000, 3000, 2048576),
    ('photo-uuid', 'MEDIUM', 'path/to/medium.jpg', 1920, 1440, 512000),
    ('photo-uuid', 'SMALL', 'path/to/small.jpg', 540, 405, 128000),
    ('photo-uuid', 'THUMB', 'path/to/thumb.jpg', 200, 150, 32000);

-- 3. Create statistics record
INSERT INTO photo_statistics (
    photo_id, visit_count, download_count, 
    favourite_count, shared_count
) VALUES ('photo-uuid', 0, 0, 0, 0);
```

### 14. Response Formation

The response varies based on processing mode:

**Asynchronous Processing (Queue Enabled):**
```json
{
  "file_name": "IMG_1234.jpg",
  "extension": ".jpg", 
  "uuid_name": "AbC123DeF456.jpg",
  "stage": "ready",
  "chunk_number": 5,
  "total_chunks": 5
}
```

In synchronous processing, the response indicates completion by changing the stage to "done":

```json
{
  "file_name": "IMG_1234.jpg",
  "extension": ".jpg",
  "uuid_name": "AbC123DeF456.jpg", 
  "stage": "done",
  "chunk_number": 5,
  "total_chunks": 5
}
```


### 15. Frontend Completion

The frontend handles upload completion:

```typescript
UploadService.upload(data, controller.value)
  .then((response) => {
    meta.value = response.data;
    if (response.data.chunk_number === response.data.total_chunks) {
      progress.value = 100;
      status.value = "done";
      emits("upload:completed", props.index, "done");
    }
  })
  .catch((error) => {
    // Handle specific error cases
    switch (error.response.status) {
      case 413: errorMessage.value = "File too large"; break;
      case 422: errorMessage.value = "Invalid file format"; break;
      case 500: errorMessage.value = "Server error occurred"; break;
    }
    status.value = "error";
    emits("upload:completed", props.index, "error");
  });
```

**On Upload Completion:**
1. **Cache Invalidation**: Clear album cache to show new photos
2. **UI Updates**: Refresh gallery view to display new photos
3. **Progress Cleanup**: Remove upload progress indicators
4. **Notification**: Show success/error notifications to user

## Summary

The photo upload lifecycle in Lychee demonstrates a sophisticated, chunked upload system with comprehensive processing capabilities:

1. **Reliable Upload**: Chunked uploads with progress tracking and error recovery
2. **Flexible Processing**: Synchronous or asynchronous processing modes
3. **Rich Metadata**: Comprehensive EXIF and GPS data extraction
4. **Multiple Formats**: Support for various image and video formats
5. **Performance Optimization**: Parallel processing and efficient resource usage
6. **Security Focus**: Multiple validation layers and secure file handling
7. **User Experience**: Real-time progress updates and clear error messaging

This architecture ensures reliable photo uploads while maintaining performance and security.

---

*Last updated: August 14, 2025*
