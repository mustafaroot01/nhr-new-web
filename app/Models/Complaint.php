<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Complaint extends Model
{
    protected $fillable = [
        'reference',
        'name', 'contact',
        'subject', 'details',
        'incident_location', 'incident_date',
        'status', 'severity',
        'admin_notes',
        'is_anonymous',
    ];

    /**
     * Sensitive fields are encrypted at rest (Laravel application-key encryption).
     */
    protected $casts = [
        'name' => 'encrypted',
        'contact' => 'encrypted',
        'details' => 'encrypted',
        'incident_location' => 'encrypted',
        'admin_notes' => 'encrypted',
        'incident_date' => 'date',
        'is_anonymous' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint) {
            $complaint->reference ??= 'NHR-' . strtoupper(Str::random(8));
        });
    }
}
