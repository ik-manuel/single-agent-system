<?php

namespace App\Tools;

use Illuminate\Support\Facades\DB;

class DatabaseTool
{
    /**
     * Databse tool definition
     */
    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'database_tool',
                'description' => 'Search for complaints by category, urgency, or status. Returns matching complaints.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'category' => [
                            'type' => 'string',
                            'description' => 'Filter by category: billing, shipping, product_quality, technical, other',
                        ],
                        'urgency' => [
                            'type' => 'string',
                            'description' => 'Filter by urgency: low, medium, high',
                        ],
                        'status' => [
                            'type' => 'string',
                            'description' => 'Filter by status: new, responded, resolved',
                        ],
                    ],
                    'required' => [], 
                ],
            ],
        ];
    }

    /**
     * Execute the database query
     */
    public function execute(array $params): string
    {
        try {
            $query = DB::table('complaints');

            if (isset($params['category'])) {
                $query->where('category', $params['category']);
            }
            if (isset($params['urgency'])) {
                $query->where('urgency', $params['urgency']);
            }
            if (isset($params['status'])) {
                $query->where('status', $params['status']);
            }

            $complaints = $query->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(fn($c) => [
                        'ticket_number' => $c->ticket_number,
                        'subject'       => $c->subject,
                        'status'        => $c->status,
                        'urgency'       => $c->urgency,
                        'category'      => $c->category,
                    ]);
            
            if ($complaints->isEmpty()) {
                return 'No complaints found matching the given filters.';
            }

            return json_encode([
                'results_count'   => $complaints->count(),
                'complaints'      => $complaints->toArray(),
            ]);

        } catch (\Exception $e) {
            return 'Error: Database error: ' . $e->getMessage();
        }
    }
}
