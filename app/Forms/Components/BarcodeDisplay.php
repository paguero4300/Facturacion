<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class BarcodeDisplay extends Field
{
    protected string $view = 'forms.components.barcode-display';

    public function getBarcode(): string
    {
        $record = $this->getRecord();

        if (!$record || !$record->barcode) {
            return '';
        }

        return $record->getBarcodeImageSvg();
    }

    public function getBarcodeCode(): string
    {
        $record = $this->getRecord();
        return $record->barcode ?? '';
    }
}