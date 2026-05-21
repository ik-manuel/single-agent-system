# Single Agent System

A ReAct-pattern AI agent built with Laravel and Qwen3-32b via Groq.
The agent autonomously decides which tools to use, executes them, 
observes results, and loops until it can produce a final answer — 
without any hardcoded routing logic.


## Architecture

POST /api/agent
       │
       ▼
AgentController        → validates input, handles errors
       │
       ▼
AgentService           → ReAct loop (Thought → Action → Observe)
       │
       ▼
ToolRunnerService      → routes tool name to implementation
       │
  ┌────┼────┐
  ▼    ▼    ▼
Calc Search  DB        → execute, return string result to LLM

## How the ReAct Loop Works

1. User query enters the loop with a system prompt and tool definitions
2. LLM reasons about what information it needs (Thought)
3. LLM outputs a tool call request in JSON (Action)  
4. Application executes the tool and appends result to history (Observe)
5. LLM reads the result and decides: call another tool or give final answer
6. Loop exits on final answer or max iterations (safety guard)


## Tools

| Tool | Description |
|---|---|
| `calculator` | Evaluates arithmetic expressions safely |
| `web_search` | Searches for current information (mock) |
| `database_tool` | Queries complaints by category, urgency, status |


## Engineering Decisions

**Model:** Initially used `llama-3.3-70b-versatile` on Groq. During testing,
this model produced inconsistent tool-calling behaviour — falling back to 
XML-style function tags instead of JSON when multiple tools were registered,
causing 400 errors on every tool invocation. Switched to `qwen/qwen3-32b` 
which has stronger function-calling fine-tuning. All test cases passed 
consistently after the switch.

**Mock search:** The SearchTool uses keyword tokenization (not full-string 
matching) to reliably match agent queries against mock data regardless of 
phrasing variation.

**Safety:** Max iterations guard prevents infinite loops. Tool execution 
errors are caught and returned as strings so the agent can reason about 
failures rather than crash.


## Sample Requests


### Calculator
POST /api/agent
{ "query": "What is 18 percent of 250000?" }

### Database
POST /api/agent  
{ "query": "Show me all high urgency complaints" }

### Multi-step (search + calculator)
POST /api/agent
{ "query": "What is 15 percent of the average software engineer salary in Lagos?" }


## Stack
- Laravel 13
- Groq API (qwen/qwen3-32b)
- PHP 8.4