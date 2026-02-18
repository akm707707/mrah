<?php

namespace Salla\ZATCA\Test\Unit;
require_once 'lib/ZATCA/vendor/autoload.php';

use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;
/*
$generatedString = GenerateQrCode::fromArray([
    new Seller('Salla'), // seller name        
    new TaxNumber('1234567891'), // seller tax number
    new InvoiceDate('2021-07-12T14:25:09Z'), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
    new InvoiceTotalAmount('100.00'), // invoice total amount
    new InvoiceTaxAmount('15.00') // invoice tax amount
    // .....
])->toTLV();
*/
// Render A QR Code Image
// data:image/png;base64, .........
$displayQRCodeAsBase64 = GenerateQrCode::fromArray([
    new Seller('تموينات نور القاف للتجارة'), // seller name        
    new TaxNumber('310526635900003'), // seller tax number
    new InvoiceDate('2022-04-25T15:30:00Z'), // invoice date as Zulu ISO8601 @see https://en.wikipedia.org/wiki/ISO_8601
    new InvoiceTotalAmount('9999'), // invoice total amount
    new InvoiceTaxAmount('25') // invoice tax amount
    // .......
])->render();

echo $displayQRCodeAsBase64 ;


// now 