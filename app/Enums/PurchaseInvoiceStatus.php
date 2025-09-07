<?php

namespace App\Enums;

enum PurchaseInvoiceStatus: string
{
    case DRAFT = 'draft';
    case CLOSED = 'closed';
}
