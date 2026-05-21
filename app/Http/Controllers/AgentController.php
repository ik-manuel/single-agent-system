<?php

namespace App\Http\Controllers;

use App\Services\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class AgentController extends Controller
{
    /**
     * Instantiate 
     */
    public function __construct(private AgentService $agent) { }

    /**
     * Run agent
     */
    public function run(Request $request): JsonResponse
    {
        // Validate input query
        $validated = $request->validate(['query' => 'required|string|min:8|max:500']);

        try {
            // Run agent
            $response = $this->agent->run($validated['query']);
        } catch (Exception $e) {
            Log::error('AgentController: agent run failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Agent failed to process your request. Please try again.',
            ], 500);
        }

        return response()->json([
            'response'        => $response['answer'],
            'tokens'          => $response['tokens'],
            'tools_used'      => $response['tools_used'],
            'iterations_used' => $response['iterations_used'],
            'round'           => $response['round'],
        ]);
    }
}
