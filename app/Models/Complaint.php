<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = ['body', 'status'];

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'جديدة',
            self::STATUS_IN_REVIEW => 'قيد المراجعة',
            self::STATUS_RESOLVED => 'تمت المعالجة',
        ];
    }
}
