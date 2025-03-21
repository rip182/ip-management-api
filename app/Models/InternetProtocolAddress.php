<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use OwenIt\Auditing\Contracts\Auditable;

class InternetProtocolAddress extends Model implements Auditable
{
    /** @use HasFactory<\Database\Factories\InternetProtocolFactory> */
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $guarded = [];
    protected $auditInclude = [
        'ip_address',
        'label',
        'comment',
        'user_id'
    ];

    public function User()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Customize the data included in the audit.
     *
     * @return array
     */

    public function transformAudit(array $data): array
    {
        $data['ip_address_id'] = $this->id;

        return $data;
    }
}
