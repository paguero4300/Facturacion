<?php

namespace App\Rules;

use App\Services\DeliveryValidationService;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDeliveryDate implements ValidationRule
{
    private DeliveryValidationService $deliveryService;

    public function __construct()
    {
        $this->deliveryService = new DeliveryValidationService();
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return; // Allow null/empty values
        }

        try {
            $date = Carbon::parse($value);
            
            if (!$this->deliveryService->isValidDeliveryDate($date)) {
                $fail($this->deliveryService->getValidationMessage($date));
            }
        } catch (\Exception $e) {
            $fail('La fecha proporcionada no es válida.');
        }
    }
}
