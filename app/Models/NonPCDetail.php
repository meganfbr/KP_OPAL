<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

use App\Traits\HasActivityLog;

class NonPCDetail extends Model
{
    use HasFactory, HasActivityLog;

    protected $activityModul = 'Inventaris Non-PC';

    protected $table = 'non_pc_details';
    protected $guarded = ['id'];

    public function inventory(): MorphOne
    {
        return $this->morphOne(Inventory::class, 'inventoriable');
    }
}
