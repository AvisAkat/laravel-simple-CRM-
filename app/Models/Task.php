<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'due_dateTime',
        'priority',
        'customer',
        'assign_To',
        'lead',
        'completed',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_dateTime' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'customer');
    }

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
