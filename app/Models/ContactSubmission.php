<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'is_read',
        'is_archived',
        'ip_address',
        'locale',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function archive(): void
    {
        $this->update(['is_archived' => true]);
    }
}
