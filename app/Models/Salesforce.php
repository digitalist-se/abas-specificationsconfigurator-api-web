<?php

namespace App\Models;

use App\CRM\Enums\SalesforceObjectType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @mixin IdeHelperSalesforce
 */
class Salesforce extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'contact_id',
        'account_id',
        'task_id',
        'content_version_id',
        'content_document_id',
        'content_document_link_id',
    ];

    protected $hidden = [
        'lead_id',
        'contact_id',
        'account_id',
        'task_id',
        'content_version_id',
        'content_document_id',
        'content_document_link_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
