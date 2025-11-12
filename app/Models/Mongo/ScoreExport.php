<?php
// app/Models/Mongo/ScoreExport.php
namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class ScoreExport extends Model
{
    // Conexión y colección explícitas
    protected $connection = 'mongodb';
    protected $collection = 'scores_export';

    // Por compatibilidad con Eloquent: deja también la “tabla”
    protected $table = 'scores_export';

    // PK de Mongo
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';   // evita problemas al serializar
    public $timestamps = false;

    // Si te da igual ver el ObjectId como string:
    protected $casts = [
        '_id' => 'string',
        'student_id' => 'string',
        'stress_score' => 'integer',
        'anxiety_score' => 'integer',
        'depression_score' => 'integer',
        'total_score' => 'integer',
    ];
}
