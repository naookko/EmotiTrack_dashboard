<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model as MongoModel;

class Dass21WeekResult extends MongoModel
{
    /**
     * Use the MongoDB connection.
     */
    protected $connection = 'mongodb';

    /**
     * MongoDB collection name.
     */
    protected $collection = 'dass21weekresults';

    /**
     * Mongo primary key (_id as ObjectId stored as string).
     */
    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Fields that can be mass assigned.
     */
    protected $fillable = [
        'optimal_k',
        'centroids',
        'is_active_week',
        'week_start_date',
        'week_end_date',
        'run_date',
        'clusters',
        'sse_values',
        'outputs',
    ];

    /**
     * Cast fields to proper types.
     */
    protected $casts = [
        '_id'             => 'string',

        'optimal_k'       => 'integer',

        // Arrays / objects
        'centroids'       => 'array',
        'clusters'        => 'array',
        'sse_values'      => 'array',
        'outputs'         => 'array',

        // Boolean
        'is_active_week'  => 'boolean',

        // Dates
        'week_start_date' => 'datetime',
        'week_end_date'   => 'datetime',
        'run_date'        => 'datetime',
    ];

    /**
     * Disable timestamps unless your documents have
     * created_at and updated_at values.
     */
    public $timestamps = false;
}
