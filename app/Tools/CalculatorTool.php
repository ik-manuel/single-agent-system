<?php

namespace App\Tools;

class CalculatorTool
{
    /**
     * Calculator Tool definition
     */
    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'calculator',
                'description' => 'Perform  mathematical calculations, Supports, +, -, *, / ',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'expression' => [
                            'type' => 'string',
                            'description' => 'The mathematical expression to evaluate, e.g., "7 * 10" or "100/5" ',
                        ],
                    ],
                    'required' => ['expression'],
                ],
            ],
        ];
    }

    /**
     * Execute calculator
     */
    public function execute(array $params): string
    {
        try {
            // Validate to ensure entries are valid mathematics expression
            if (!preg_match('/^[\d\s\+\-\*\/\(\)\.%]+$/', $params['expression'])) {
                return 'Error: Invalid expression. Only numbers and operators (+, -, *, /, %) are allowed.';
            }

            // Evaluate expression safely
            $result = eval("return {$params['expression']};");
            return (string) $result;

        } catch (\Throwable $e) {
            return 'Error: Calculation error: ' . $e->getMessage();
        }
    }
}
