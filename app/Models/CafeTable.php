<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    use HasFactory;

    protected $table = 'tables';

    protected $fillable = [
        'table_number',
        'qr_token',
        'status',
    ];

    public function getQrUrlAttribute(): string
    {
        return url('/customer/order?table=' . urlencode($this->table_number) . '&token=' . urlencode($this->qr_token));
    }
}
