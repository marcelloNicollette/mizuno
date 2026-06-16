<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Segmentacao;

class SegmentacaoCliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'segmentacao_cliente';

    protected $fillable = [
        'nome',
        'descricao',
        'slug',
        'active',
        'produtos_segmentos',
        'linha',
    ];

    protected $casts = [
        'active' => 'boolean',
        'produtos_segmentos' => 'integer',
    ];

    /**
     * Relacionamento many-to-many com usuários
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_segmentacao_cliente')
                    ->withTimestamps();
    }

    /**
     * Relacionamento many-to-many com cores
     */
    public function colors()
    {
        return $this->belongsToMany(Color::class, 'color_segmentacao_cliente')
                    ->withTimestamps();
    }

    public function segmentoProduto()
    {
        return $this->belongsTo(Segmentacao::class, 'produtos_segmentos');
    }

    /**
     * Gerar slug automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nome);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('nome')) {
                $model->slug = Str::slug($model->nome);
            }
        });
    }
}
