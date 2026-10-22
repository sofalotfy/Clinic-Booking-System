<?php

namespace App\APIServices\Costs;

class CostRules
{
    public static function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'name' => [$required, 'string', 'max:255'],
            'cost_category_id' => [$required, 'integer', 'exists:cost_categories,id'],
            'amount' => [$required, 'numeric', 'min:0', 'max:99999999.99'],
            'date' => [$required, 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
