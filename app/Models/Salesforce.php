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

    public static function objectIdColumn(SalesforceObjectType $objectType): string
    {
        return match ($objectType) {
            SalesforceObjectType::Lead    => 'lead_id',
            SalesforceObjectType::Contact => 'contact_id',
            SalesforceObjectType::Account => 'account_id',
        };
    }

    public function objectId(SalesforceObjectType $objectType): string
    {
        return $this->{self::objectIdColumn($objectType)};
    }

    public function saveObjectId(string $id, SalesforceObjectType $objectType): bool
    {
        $property = match ($objectType) {
            SalesforceObjectType::Lead    => 'lead_id',
            SalesforceObjectType::Contact => 'contact_id',
            SalesforceObjectType::Account => 'account_id',
        };

        $this->{$property} = $id;

        return $this->save();
    }
}
