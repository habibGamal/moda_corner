<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseInvoiceItem::class);
    }
}
