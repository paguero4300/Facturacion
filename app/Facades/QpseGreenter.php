<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array sendInvoice(array $invoiceData)
 * @method static array sendCreditNote(array $creditData)
 * @method static array sendDebitNote(array $debitData)
 * @method static array sendDespatch(array $despatchData)
 * @method static bool isConfigured()
 */
class QpseGreenter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'qpse.greenter';
    }
}