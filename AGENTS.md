# Project Instructions

## Code review graph

When the user asks to "cek program secara keseluruhan" or makes an equivalent broad project-review request:

1. Check `code-review-graph` first and update it if the graph is stale.
2. Use the graph to identify relevant modules, dependencies, tests, and the blast radius before scanning files broadly.
3. Verify graph findings against the actual source code and tests; never treat the graph as the sole source of truth.
4. If the graph or MCP integration is unavailable, fall back to repository search and state that fallback briefly.
