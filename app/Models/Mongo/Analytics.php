<?php
// app/Models/Mongo/Analytics.php
namespace App\Models\Mongo;
use MongoDB\Laravel\Eloquent\Model;

class Analytics extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'analytics';
    public $timestamps = false;
    protected $fillable = [];
}
