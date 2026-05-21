<?php

namespace App\Services;


class ToolRunnerService
{
    /**
     * Registered tools — key must match the 'name' field in each tool's definition()
     */
    private array $tools = [
        'calculator'    => \App\Tools\CalculatorTool::class,
        'web_search'    => \App\Tools\SearchTool::class,
        'database_tool' => \App\Tools\DatabaseTool::class,
        // Register new tools here
    ];

    /**
     * Finds, instantiates, and executes the requested tool.
     *
     * @param string $toolName  The tool name as declared in its definition()
     * @param array  $params    Parameters passed to the tool's execute() method
     * @return string           The string result of the tool execution
     *
     * @throws \InvalidArgumentException if the tool is not registered
     */
    public function run(string $toolName, array $params): string
    {
        if (!isset($this->tools[$toolName])) {
            throw new \InvalidArgumentException("Tool \"{$toolName}\" is not registered or does not exist.");
        }
        
        // Resolve and build the class instantly
        $toolClass = app($this->tools[$toolName]);

        // execute tool
        return $toolClass->execute($params);
    }

    /**
     * Resolves all registered tools and compiles their schemas into one array.
     *
     * @return array  Array of tool definition schemas for the LLM
     */
    public function getToolDefinitions(): array
    {
        $definitions = [];

        foreach ($this->tools as $name => $toolClass) {
            // Instantiate and Extract definition schema
            $definitions[] = app($toolClass)->definition();
        }

        return $definitions;
    }

    /**
     * Returns the list of registered tool names.
     *
     * @return array
     */
    public function getToolNames(): array
    {
        return array_keys($this->tools);
    }
}
