<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('travel_report_documents')]
#[Fillable([
    'municipality_id',
    'submitted_by_user_id',
    'file_name',
    'file_path',
    'file_size',
    'sei_process',
    'mime_type',
    'created_at',
    'updated_at',
    'deleted_at',
])]
class TravelReport extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'municipality_id' => 'integer',
            'file_size' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
