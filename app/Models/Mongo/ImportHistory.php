<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class ImportHistory extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'import_histories';

    protected $fillable = [
        'started_at',
        'finished_at',
        'status',
        'total_files',
        'processed_files',
        'total_products',
        'successful_imports',
        'failed_imports',
        'error_message'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'total_files' => 'integer',
        'processed_files' => 'integer',
        'total_products' => 'integer',
        'successful_imports' => 'integer',
        'failed_imports' => 'integer'
    ];
}
