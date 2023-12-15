<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leavebank extends Model
{
    use HasFactory;
    protected $connection = 'mysql_second';
    protected $table = 'leave_bank';
    protected $primaryKey = 'id';

    protected $fillable = [
        'employeeid',
        'approverid',
        'leave_bank',
        'email',
        'is_active',
        'is_deleted'
    ];
    
}
