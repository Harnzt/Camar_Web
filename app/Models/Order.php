<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'order_number',
        'quantity',
        'subtotal',
        'tax',
        'total_price',
        'payment_method',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'status',
        'status_updated_by',
        'status_updated_at',
        'admin_notes',
        'transaction_id', 
        'order_code', 
        'payment_status', 
        'total_amount'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusUpdater()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }
}
