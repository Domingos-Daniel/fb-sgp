<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Beneficiario extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nome',
        'bi',
        'nif',
        'data_nascimento',
        'genero',
        'imagem',
        'email',
        'tipo_beneficiario',
        'telemovel',
        'telemovel_alternativo',
        'endereco',
        'pais',
        'provincia',
        'coordenadas_bancarias',
        'ano_frequencia',
        'curso',
        'universidade_ou_escola',
        'observacoes',
        'id_criador',
    ];

    protected $casts = [
        'provincia' => 'array',
    ];

    public function patrocinios()
    {
        return $this->hasMany(Patrocinio::class, 'id_beneficiario');
    }

    // Em App\Models\Beneficiario.php

    public function pagamentos()
    {
        return $this->hasManyThrough(
            Pagamento::class,
            Patrocinio::class,
            'id_beneficiario', // Chave estrangeira em Patrocinio
            'id_patrocinio',   // Chave estrangeira em Pagamento
            'id',              // Chave local em Beneficiario
            'id'               // Chave local em Patrocinio
        );
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function criador()
    {
        return $this->belongsTo(User::class, 'id_criador');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nome', 'imagem', 'bi', 'nif', 'data_nascimento', 'genero', 'email', 'telemovel', 'telemovel_alternativo', 'endereco', 'pais', 'provincia', 'coordenadas_bancarias', 'ano_frequencia', 'curso', 'universidade_ou_escola', 'observacoes', 'id_criador']);
        // Chain fluent methods for configuration options
    }
}
