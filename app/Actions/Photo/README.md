# Photo Actions Documentation

This document provides an overview of the Photo Actions in Lychee, with special focus on the `Create.php` action and its pipeline architecture. This will help developers understand how photo processing works and how to extend or modify the system.

## Overview

Photo Actions handle all operations related to photo management in Lychee. They follow the **Action Pattern** combined with a **Pipeline Pattern** for complex operations like photo creation, which involves multiple processing steps.

## Design Pattern: Action + Pipeline

### Action Pattern
The Action pattern encapsulates business logic operations in dedicated classes. Each action:
- Has a single responsibility
- Can be easily tested in isolation
- Provides a clean interface between controllers and domain logic
- Returns consistent response formats

### Pipeline Pattern
For complex operations like photo creation, we use the Pipeline pattern where:
- Data flows through a series of processing steps (pipes)
- Each pipe performs a specific transformation or validation
- Pipes can be easily added, removed, or reordered
- Each pipe receives the data, processes it, and passes it to the next pipe

## Photo Creation Pipeline (Create.php)

The `Create.php` action is the most complex photo action, implementing a multi-stage pipeline for processing uploaded photos. Here's how it works:

### Entry Point
```php
public function do(UploadedFile $uploaded_file, ?Album $album = null): Photo
{
    $photoFile = new PhotoFile($uploaded_file);
    
    return $this->pipe($photoFile, [
        Init::class,
        ExtractMetadata::class,
        // ... other pipes
    ]);
}
```

## Key Classes and Their Roles

In this section we will detail the key pipes and DTO used in the photo creation pipeline.

### DTOs (Data Transfer Objects)

The photo creation pipeline uses several specialized DTOs to carry state through different stages:

#### PhotoDTO Interface
```php
interface PhotoDTO
{
    public function getPhoto(): Photo;
}
```
Base interface for all photo-related DTOs that eventually produce a Photo model.

#### InitDTO
Used in the initial preprocessing phase:
- Contains import mode and parameters
- Holds intended owner information
- Stores extracted EXIF metadata (Extractor object)
- Manages file paths and basic photo information
- Used by pipes implementing `InitPipe` interface

#### StandaloneDTO (implements PhotoDTO)
For processing individual photo files:
- Contains image handler for processing
- Manages size variant naming strategy
- Handles temporary video files for processing
- Stores target and backup file references
- Includes stream statistics for performance monitoring

#### DuplicateDTO (implements PhotoDTO) 
For handling duplicate photo detection and processing:
- Manages duplicate detection logic
- Contains reference to original photo if duplicate found
- Handles duplicate resolution strategies

#### PhotoPartnerDTO & VideoPartnerDTO
For processing paired files (e.g., Live Photos):
- PhotoPartnerDTO: Handles photo file partnerships
- VideoPartnerDTO: Manages video components of Live Photos
- Both coordinate processing of related file pairs

### Pipe Interfaces

The pipeline uses different pipe interfaces for different processing stages:

#### InitPipe
```php
interface InitPipe
{
    public function handle(InitDTO $state, \Closure $next): InitDTO;
}
```
- **Purpose**: Initial preprocessing steps before main photo processing
- **Input/Output**: InitDTO containing basic file and import information
- **Use Cases**: File validation, metadata extraction, initial setup

#### PhotoPipe
```php
interface PhotoPipe
{
    public function handle(PhotoDTO $state, \Closure $next): PhotoDTO;
}
```
- **Purpose**: General photo processing operations
- **Input/Output**: Any PhotoDTO implementation
- **Use Cases**: Image manipulation, size variant generation, final processing

#### StandalonePipe
```php
interface StandalonePipe
{
    public function handle(StandaloneDTO $state, \Closure $next): StandaloneDTO;
}
```
- **Purpose**: Processing individual standalone photos
- **Input/Output**: StandaloneDTO with image processing context
- **Use Cases**: Individual photo operations, standard image processing

#### DuplicatePipe
```php
interface DuplicatePipe
{
    public function handle(DuplicateDTO $state, \Closure $next): DuplicateDTO;
}
```
- **Purpose**: Handle duplicate photo detection and resolution
- **Input/Output**: DuplicateDTO with duplicate detection context
- **Use Cases**: Duplicate checking, merge operations, conflict resolution

#### SharedPipe
```php
interface SharedPipe
{
    public function handle(StandaloneDTO|DuplicateDTO $state, \Closure $next): StandaloneDTO|DuplicateDTO;
}
```
- **Purpose**: Operations that work on both standalone and duplicate photos
- **Input/Output**: Union type supporting both StandaloneDTO and DuplicateDTO
- **Use Cases**: Common processing steps, shared validation logic

#### PhotoPartnerPipe & VideoPartnerPipe
```php
interface PhotoPartnerPipe
{
    public function handle(PhotoPartnerDTO $state, \Closure $next): PhotoPartnerDTO;
}

interface VideoPartnerPipe  
{
    public function handle(VideoPartnerDTO $state, \Closure $next): VideoPartnerDTO;
}
```
- **Purpose**: Handle paired file processing (Live Photos)
- **Input/Output**: Respective partner DTOs
- **Use Cases**: Paired file coordination, Live Photo processing, RAW workflow

### Pipeline Flow by Type

The actual pipeline varies based on the type of photo being processed:

1. **Standard Photos**: InitPipe → StandalonePipe → PhotoPipe
2. **Duplicate Photos**: InitPipe → DuplicatePipe → PhotoPipe
3. **Paired Files**: It depends whether the file is a photo or video:
   - Video (photo already in Database): InitPipe → VideoPartnerPipe → PhotoPipe
   - Photo (video already in Database): InitPipe → StandalonePipe → VideoPartnerPipe → PhotoPartnerPipe → PhotoPipe

During the pipeline execution, when an operation is shared between the different Pipes, the `SharedPipe` can be used at any stage for common logic.


## Adding New Pipes

To add a new processing step:

1. **Create the pipe class**:
```php
<?php

namespace App\Actions\Photo\Pipes;

use App\Actions\Photo\PhotoFile;
use Closure;

class YourNewPipe
{
    public function handle(PhotoFile $photoFile, Closure $next): PhotoFile
    {
        // Your processing logic here
        
        return $next($photoFile);
    }
}
```

2. **Add to the pipeline** in `Create.php`:
```php
return $this->pipe($photoFile, [
    Init::class,
    ExtractMetadata::class,
    YourNewPipe::class, // Add your pipe here
    // ... other pipes
]);
```
