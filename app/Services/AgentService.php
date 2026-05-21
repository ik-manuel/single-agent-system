<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AgentService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $maxIterations;
    private int $maxTokens;
    /**
     * Create a new class instance.
     */
    public function __construct(private ToolRunnerService $runner)
    {
        $this->apiKey = config('agent.llm.api_key');
        $this->model  = config('agent.llm.model');
        $this->baseUrl  = config('agent.llm.base_url');
        $this->maxIterations  = config('agent.llm.max_iterations', 8);
        $this->maxTokens  = config('agent.llm.max_tokens', 1024);
    }

    /**
     * Build LLM message array
     */
    public function chat(array $messages, array $tools): array 
    {
        // build llm chat array
        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'tools'       => $tools,
            'tool_choice' => 'auto',
            'temperature' => 0.3,
            'max_tokens'  => $this->maxTokens,
        ];

        // llm api call
        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post($this->baseUrl . '/chat/completions', $payload);
            
            if (!$response->successful()) {
                throw new Exception('LLM API error: ' . $response->body());
            }

            // Response array
            $data = $response->json();
            $message = $data['choices'][0]['message'];

            return [
                'content'       => $message['content'] ?? null,
                'tool_calls'    => $message['tool_calls'] ?? null,
                'tokens'        => $data['usage']['total_tokens'] ?? 0,
                'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
                // TODO: handle 'length' finish_reason — response may be truncated
            ];

        } catch (Exception $e) {
            Log::info('AgentService: LLM chat API failed ', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    
    }

    /**
     * Run the ReAct agent loop for a given user query.
     */
    public function run(string $query): array
    {
        // System message
        $systemPrompt = <<<PROMPT
You are a precise AI assistant with access to tools.

Rules:
1. Think step by step before deciding which tool to use.
2. Always use a tool when the query requires real data — never guess.
3. After observing a tool result, decide if you have enough information to answer.
4. When you have enough information, respond with a clear, direct final answer.
5. Do not call the same tool twice with the same parameters.
PROMPT;

        // define message array
        $messages = [
            [
                'role'    => 'system',
                'content' => $systemPrompt,
            ],
            [
                'role'    => 'user',
                'content' => $query, 
            ],
        ];

        // defined properties
        $totalTokens = 0;
        $toolUsed = [];
        $round = 0;

        // Get all tool definination
        $allTools = $this->runner->getToolDefinitions();

        // Agent loop
        while($round < $this->maxIterations) {
            $round++;

            // Agent call
            $response = $this->chat($messages, $allTools);
            $totalTokens += $response['tokens'];

            // Final answer — LLM has enough information
            if (!empty($response['content']) && empty($response['tool_calls'])) {

                $messages[] = ['role' => 'assistant', 'content' => $response['content']];

                return [
                    'answer'          => $response['content'],
                    'tokens'          => $totalTokens,
                    'tools_used'      => $toolUsed,
                    'iterations_used' => $round,
                    'round'           => $round ."/". $this->maxIterations,
                ];
            } 

            // Tool call — execute and append results to history ($messages)
            if (!empty($response['tool_calls'])) {
                $messages[] = [
                    'role'       => 'assistant',
                    'content'    => $response['content'],
                    'tool_calls' => $response['tool_calls'],
                ];

                foreach ($response['tool_calls'] as $toolCall) {
                    $toolName = $toolCall['function']['name'];
                    $params = json_decode($toolCall['function']['arguments'], true) ?: [];
                    $toolUsed[] = $toolName;

                    // Execute tool
                    try {
                        $toolResult = $this->runner->run($toolName, $params);
                    } catch (Exception $e) {
                        $toolResult = 'Error: ' . $e->getMessage();
                    }

                    // Append result to message history
                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'name'         => $toolName,
                        'content'      => is_string($toolResult) 
                                            ? $toolResult 
                                            : json_encode($toolResult),
                    ];
                }
                continue;
            }
            // Neither content nor tool_calls — unexpected response, bail
            break;
        }

        // fallback response
        $fallback = 'I encountered an issue processing your request. Please try again.';
        return  [
            'answer'          => $fallback,
            'tokens'          => $totalTokens,
            'tools_used'      => $toolUsed,
            'iterations_used' => $round,
            'round'           => $round ."/". $this->maxIterations,
        ];
    }
}