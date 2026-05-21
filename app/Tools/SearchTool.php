<?php

namespace App\Tools;

class SearchTool
{
    /**
     * Define hardcoded mock web result to use temporay later
     *  will implement actual live web api search 
     */
    private array $web = [
        [
            "title" => "How to Fix a Flat Bike Tire: A Step-by-Step Guide",
            "url" => "https://rei.com",
            "snippet" => "Learn how to fix a flat bike tire in 5 easy steps. First, remove the wheel. Second, take off the tire bead using levers. Third, find the leak, patch or replace the inner tube, and pump it up."
        ],
        [
            "title" => "Average Software Engineer Salary in Nigeria 2024",
            "url" => "https://salaryexplorer.com",
            "snippet" => "The average software engineer salary in Lagos, Nigeria is approximately ₦4,800,000 per year. Senior engineers can earn between ₦7,000,000 and ₦12,000,000 annually."
        ],
        [
            "title" => "USD to NGN Exchange Rate Today",
            "url" => "https://xe.com",
            "snippet" => "1 US Dollar equals approximately 1,580 Nigerian Naira as of today. The exchange rate has fluctuated between 1,500 and 1,620 over the past 30 days."
        ],
        [
            "title" => "President of Nigeria 2024",
            "url" => "https://bbc.com/africa",
            "snippet" => "Bola Ahmed Tinubu is the current President of Nigeria, having assumed office on May 29, 2023 after winning the February 2023 general elections."
        ],
        [
            "title" => "",
            "url" => "https://example-broken-link.com",
            "snippet" => ''
        ],
        [
            "title" => "Unrecognized Search Query - Help Center",
            "url" => "https://search-engine.internal",
            "snippet" => "Your search did not match any documents. Please ensure all words are spelled correctly or try using more general keywords."
        ],
    ];
        
    /**
     * Search Tool definition
     */
    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'web_search',
                'description' => 'Search the live internet to retrieve up-to-date',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'query' => [
                            'type' => 'string',
                            'description' => 'The specific search query string optimized for search engine (e.g., "Naira to dollar today").'
                        ],
                        'max_results' => [
                            'type' => 'integer',
                            'description' => 'The maximun number of search results snippets to return.',
                            'default' => 5,
                        ],
                    ],
                    'required' => ['query', 'max_results'],
                    'additionalProperties' => false,
                ],
                'strict' => true,
            ],
        ];
    }

    /**
     *  Execute search tool
     */
    public function execute(array $params): string
    {
        // Check if search query is empty
        if (!isset($params['query']) || empty(trim($params['query']))) {
            return 'No results found: empty query.';
        }

        // Normalize the search keyword to lowercase for case-insensitive matching
        $keyword = strtolower(trim($params['query']));
        $matches = [];

        // Loop through each mock website result
        foreach ($this->web as $result) {
            $title   = strtolower($result['title'] ?? '');
            $snippet = strtolower($result['snippet'] ?? '');

            // Combine text to search both title and snippet easily
            $searchableText = $title . ' ' . $snippet;

            if (str_contains($searchableText, $keyword)) {
                // Accumulate all matches
                $matches[] = $result;
            }
        }

        if (empty($matches)) {
            return 'No results found for query: ' . $params['query'];
        }

        // Convert to string for LLM consumption
        $output = '';
        foreach ($matches as $i => $r) {
            $output .= ($i + 1) . ". {$r['title']}\n";
            $output .= "   URL: {$r['url']}\n";
            $output .= "   {$r['snippet']}\n\n";
        }

        return trim($output);
    }
}
