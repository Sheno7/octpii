<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectorAdditionalInformation extends Model
{
    use HasFactory;

    protected $table = 'sector_additional_information';
    protected $fillable = ['sector_id', 'additional_information_id'];

    protected $casts = [
        'sector_id' => 'integer',
        'additional_information_id' => 'integer',
    ];

    public function sector()
    {
        return $this->belongsTo(Sectors::class, 'sector_id');
    }

    public function additionalInformation()
    {
        return $this->belongsTo(AdditionalInformation::class, 'additional_information_id');
    }
}
