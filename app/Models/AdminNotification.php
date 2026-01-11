<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;

    const TYPE_COMPANY_REGISTERED = 'company_registered';
    const TYPE_COMPANY_APPROVED = 'company_approved';
    const TYPE_COMPANY_REJECTED = 'company_rejected';
    const TYPE_SYSTEM_WARNING = 'system_warning';
    const TYPE_SYSTEM_INFO = 'system_info';

    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'icon',
        'color',
        'action_url',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function getCompany(): ?Company
    {
        if ($this->data && isset($this->data['company_id'])) {
            return Company::find($this->data['company_id']);
        }
        return null;
    }
}
