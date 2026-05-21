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
            "title" => "Tech Salaries in Nigeria: What Developers Earn in 2024",
            "url" => "https://techpoint.africa",
            "snippet" => "Lagos-based software developers and engineers earn between 
                        ₦3,600,000 and ₦7,200,000 annually depending on experience. 
                        The median annual salary for a mid-level software engineer 
                        in Lagos is ₦4,800,000."
        ],
        [
            "title" => "Nigeria Developer Salary Report 2024",
            "url" => "https://paystack.com/salary-report",
            "snippet" => "Based on data from 500+ Nigerian tech professionals, the average 
                        annual compensation for software engineers in Lagos is 
                        ₦4,800,000. Senior engineers earn upward of ₦9,000,000."
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

        $query = strtolower(trim($params['query']));

        // Split into individual keywords, filter out short stop words
        $keywords = array_filter(
            explode(' ', $query),
            fn($word) => strlen($word) > 3  // ignore "the", "for", "and" etc.
        );

        $scored = [];

        foreach ($this->web as $index => $result) {
            $searchableText = strtolower(
                ($result['title'] ?? '') . ' ' . ($result['snippet'] ?? '')
            );

            // Count how many keywords match
            $matchCount = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($searchableText, $keyword)) {
                    $matchCount++;
                }
            }

            if ($matchCount > 0) {
                $scored[] = [
                    'result' => $result,
                    'score'  => $matchCount,
                ];
            }
        }

        if (empty($scored)) {
            return 'No results found for query: ' . $params['query'];
        }

        // Sort by most keyword matches first
        usort($scored, fn($a, $b) => $b['score'] - $a['score']);

        // Take top results up to max_results
        $maxResults = $params['max_results'] ?? 3;
        $topResults = array_slice($scored, 0, $maxResults);

        $output = "Search results for: \"{$params['query']}\"\n\n";
        foreach ($topResults as $i => $item) {
            $r = $item['result'];
            $output .= ($i + 1) . ". {$r['title']}\n";
            $output .= "   URL: {$r['url']}\n";
            $output .= "   {$r['snippet']}\n\n";
        }

        return trim($output);
    }

}
