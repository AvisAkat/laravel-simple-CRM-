<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'company',
        'status',
        'assign_To',
        'notes',
        'converted',
        'created_by',
        'updated_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function assigned()
    {
        return $this->belongsTo(User::class, 'assign_To');
    }
}
