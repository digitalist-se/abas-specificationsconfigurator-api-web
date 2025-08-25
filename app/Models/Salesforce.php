<?php

namespace App\Models;

use App\CRM\Enums\SalesforceObjectType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @mixin IdeHelperSalesforce
 */
class Salesforce extends BaseModel implements AuditableContract
{
    use HasFactory;
    use Auditable;

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

    public static function objectIdAttributeName(SalesforceObjectType $objectType): string
    {
        return match ($objectType) {
            SalesforceObjectType::Lead    => 'lead_id',
            SalesforceObjectType::Contact => 'contact_id',
            SalesforceObjectType::Account => 'account_id',
            SalesforceObjectType::Task    => 'task_id',
        };
    }

    public function objectId(SalesforceObjectType $objectType): string
    {
        return $this->{self::objectIdAttributeName($objectType)};
    }

    public function saveObjectId(string $id, SalesforceObjectType $objectType): bool
    {
        $this->{self::objectIdAttributeName($objectType)} = $id;

        return $this->save();
    }
}
