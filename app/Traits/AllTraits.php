<?php

namespace App\Traits;

trait AllTraits
{
    use ApiResponse, ChunkFileUpload, DatabaseExportable, FileManager, HasFilter, ImagePathTrait, Searchable, TableAction;
}
