<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Helper\ResponseJsend;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchTravelItineraryRequest extends FormRequest
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
            'term' => $this->query('term', $this->query('q')),
        ];
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'process' => ['nullable', 'string'],
            'municipality' => ['nullable', 'string'],
            'force' => ['nullable', 'string'],
            'region' => ['nullable', 'string'],
            'land_status' => ['nullable', 'string'],
            'progress' => ['nullable', 'string'],
            'requester' => ['nullable', 'string'],
            'term' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();

        if (array_key_exists('term', $errors) && $this->query->has('q') && ! $this->query->has('term')) {
            $errors['q'] = $errors['term'];
            unset($errors['term']);
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
