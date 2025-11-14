<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model as MongoModel;

class Response extends MongoModel
{
    protected $connection = 'mongodb';
    protected $collection = 'responses';

    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'wha_id',
        'questionnaire_id',
        'answer',
        'response_date',
        'anxiety_score',
        'depression_score',
        'stress_score',
        'total_score',
    ];

    protected $casts = [
        '_id'              => 'string',
        'wha_id'           => 'string',
        'questionnaire_id' => 'string',
        'answer'           => 'array',

        'anxiety_score'    => 'integer',
        'stress_score'     => 'integer',
        'depression_score' => 'integer',
        'total_score'      => 'integer',

        'response_date'    => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public $timestamps = true;

    public function getDassAnswersAttribute()
    {
        return collect($this->answer ?? [])
            ->filter(fn ($v, $k) => str_starts_with($k, 'dass_q'));
    }

    public function getExtraAnswersAttribute()
    {
        return collect($this->answer ?? [])
            ->filter(fn ($v, $k) => str_starts_with($k, 'extra_q'));
    }
}
