<?php
namespace App\Services;

use App\Imports\AssetsImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class AssetImportService
{
    /**
     * Executes the business logic for importing assets.
     */
    public function importFromExcel(UploadedFile $file): void
    {
    
        Excel::import(new AssetsImport, $file);

    }
}