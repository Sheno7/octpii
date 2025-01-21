<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvidersAction extends Model
{
    use HasFactory,softDeletes;
    protected $table = 'providers_action';

    public function provider()
    {
        return $this->belongsTo(Providers::class,'provider_id','id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class,'transaction_id','id');
    }
}
