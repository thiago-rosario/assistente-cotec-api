<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Helper\ResponseJsend;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PersistTravelReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'municipality_id' => ['required', 'integer', 'min:1'],
            'submitted_by_user_id' => ['required', 'string', 'max:255'],
            'file_name' => ['required', 'string', 'max:255'],
            'file_path' => ['required', 'string', 'max:255'],
            'file_size' => ['nullable', 'integer', 'min:0'],
            'mime_type' => ['nullable', 'string', 'max:255'],
            'sei_process' => ['required', 'string', 'max:255'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = new ResponseJsend(
            data: $validator->errors()->toArray(),
            status: ResponseJsend::STATUS_FAIL,
        );

        throw new HttpResponseException(
            response()->json($response->toArray(), 422),
        );
    }
}
