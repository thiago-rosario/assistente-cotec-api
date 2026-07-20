<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Helper\ResponseJsend;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeleteTravelReportRequest extends FormRequest
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
            'id' => $this->route('travelReportId'),
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
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
