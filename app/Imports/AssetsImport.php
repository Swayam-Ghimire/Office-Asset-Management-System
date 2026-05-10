<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ShouldQueueWithoutChain;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Override;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class AssetsImport implements ShouldQueueWithoutChain, ToModel, WithChunkReading, WithHeadingRow, WithMapping, WithValidation, SkipsOnError, SkipsOnFailure
{
    /**
     * Determine the number of rows to read per chunk.
     */
    public function chunkSize(): int
    {
        return 10; // This tells Laravel to process 10 rows per background job
    }

    public function map($row): array
    {
        // 1. Handle Dates
        if (isset($row['purchase_date']) && is_numeric($row['purchase_date'])) {
            $row['purchase_date'] = Date::excelToDateTimeObject($row['purchase_date']);
        }

        // 2. We keep the original 'category' name for validation
        // but we find the ID and store it in a new key for the model
        $category = Category::where('name', trim($row['category']))->first();
        $row['category_id_internal'] = $category?->id;

        return $row;
    }

    public function model(array $row)
    {
        return new Asset([
            'model_name' => $row['model_name'],
            'brand' => $row['brand'],
            'purchase_date' => $row['purchase_date'],
            // Use strtolower/trim to ensure database consistency
            'condition' => strtolower(trim($row['condition'])),
            'status' => strtolower(trim($row['status'])),
            'category_id' => $row['category_id_internal'],
        ]);
    }

    public function rules(): array
    {
        return [
            'model_name' => 'required|string|max:255|unique:assets,model_name',
            // VALIDATE BY NAME: This allows the user to see "The category 'Toaster' is invalid"
            'category' => 'required|exists:categories,name',
            'brand' => 'nullable|string',
            'purchase_date' => 'nullable',
            'condition' => ['required', Rule::in(['new', 'good', 'damaged'])],
            'status' => ['required', Rule::in(['available', 'not_available', 'under_maintenance'])],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'category.exists' => 'The category ":input" does not exist. Please use an existing category name.',
            'model_name.unique' => '":input" record is already present in the database!!'
        ];
    }

    #[Override]
    public function onError(Throwable $e)
    {
       Log::error('Asset Import Error: ' . $e->getMessage(), [
            'exception' => $e
        ]);
    }

    #[Override]
    public function onFailure(Failure ...$failures)
    {
         foreach ($failures as $failure) {
            Log::warning('Asset Import Validation Failure:', [
                'row' => $failure->row(),
                'attribute' => $failure->attribute(),
                'errors' => $failure->errors(),
                'values' => $failure->values(),
            ]);
        }
    }
}
