<?php

declare(strict_types=1);

namespace App\Http\Helper;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use JsonSerializable;

final readonly class ResponseJsend implements JsonSerializable
{
    public const string STATUS_SUCCESS = 'success';

    public const string STATUS_FAIL = 'fail';

    public const string STATUS_ERROR = 'error';

    private const array VALID_STATUSES = [
        self::STATUS_SUCCESS,
        self::STATUS_FAIL,
        self::STATUS_ERROR,
    ];

    public function __construct(
        private mixed $data = null,
        private string $status = self::STATUS_SUCCESS,
        private ?string $message = null,
        private ?int $code = null
    ) {
        if (! in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid JSend status [{$this->status}].");
        }

        if ($this->status === self::STATUS_ERROR && $this->message === null) {
            throw new InvalidArgumentException('JSend error responses require a message.');
        }
    }

    public static function success(mixed $data = null): self
    {
        return new self(data: $data);
    }

    public static function fail(mixed $data = null): self
    {
        return new self(data: $data, status: self::STATUS_FAIL);
    }

    public static function error(string $message, ?int $code = null, mixed $data = null): self
    {
        return new self(data: $data, status: self::STATUS_ERROR, message: $message, code: $code);
    }

    /**
     * @return array{status: string, data: mixed, message?: string, code?: int}
     */
    public function toArray(): array
    {
        $response = [
            'status' => $this->status,
            'data' => $this->normalizeData($this->data),
        ];

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        if ($this->code !== null) {
            $response['code'] = $this->code;
        }

        return $response;
    }

    /**
     * @return array{status: string, data: mixed, message?: string, code?: int}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param  array<string, string|string[]>  $headers
     */
    public function toJsonResponse(int $httpStatus = 200, array $headers = [], int $options = 0): JsonResponse
    {
        return response()->json($this->toArray(), $httpStatus, $headers, $options);
    }

    private function normalizeData(mixed $data): mixed
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        return $data;
    }
}
