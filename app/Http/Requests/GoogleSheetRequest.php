<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GoogleSheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function validationData(): array
    {
        return [
            ...$this->all(),
            'spreadsheet_id' => (string) config('google_sheets.cotec_spreadsheet.spreadsheet_id'),
            'sheets' => config('google_sheets.cotec_spreadsheet.sheets', []),
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'spreadsheet_id' => ['required', 'string'],
            'sheets' => ['required', 'array', 'min:1'],
        ];
    }
}
