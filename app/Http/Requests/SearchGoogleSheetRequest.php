<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Helper\ResponseJsend;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchGoogleSheetRequest extends FormRequest
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
            ...$this->query(),
            'spreadsheet_id' => (string) config('google_sheets.cotec_spreadsheet.spreadsheet_id'),
            'sheets' => config('google_sheets.cotec_spreadsheet.sheets', []),
            'sheet_id' => $this->route('sheetId'),
            'search' => $this->query('q'),
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
            'sheet_id' => ['required', 'integer'],
            'search' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.required' => 'O parâmetro q é obrigatório para busca.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();

        if (array_key_exists('search', $errors)) {
            $errors['q'] = $errors['search'];
            unset($errors['search']);
        }

        $response = new ResponseJsend(
            data: $errors,
            status: ResponseJsend::STATUS_FAIL,
        );

        throw new HttpResponseException(
            response()->json($response->toArray(), 422),
        );
    }
}
