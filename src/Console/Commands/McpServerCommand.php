<?php

namespace Unlab\LivewireTableKit\Console\Commands;

use Illuminate\Console\Command;

class McpServerCommand extends Command
{
    protected $signature = 'livewire-table-kit:mcp';

    protected $description = 'MCP Server for Livewire Table Kit AI Skills';

    public function handle(): int
    {
        while (true) {
            $line = fgets(STDIN);
            
            if ($line === false) {
                break;
            }
            
            $request = json_decode(trim($line), true);
            
            if (! $request || ! isset($request['method'])) {
                $this->respondJson(['jsonrpc' => '2.0', 'id' => $request['id'] ?? null, 'error' => ['code' => -32600, 'message' => 'Invalid Request']]);
                continue;
            }

            $response = match ($request['method']) {
                'initialize' => $this->handleInitialize($request),
                'tools/list' => $this->handleListTools($request),
                'tools/call' => $this->handleCallTool($request),
                'shutdown' => exit(0),
                default => ['jsonrpc' => '2.0', 'id' => $request['id'], 'result' => []],
            };

            $this->respondJson($response);
        }
        
        return 0;
    }

    protected function handleInitialize(array $request): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => [
                'protocolVersion' => '2024-11-05',
                'serverInfo' => [
                    'name' => 'livewire-table-kit',
                    'version' => '1.0.0'
                ],
                'capabilities' => [
                    'tools' => []
                ]
            ]
        ];
    }

    protected function handleListTools(array $request): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => [
                'tools' => [
                    [
                        'name' => 'livewire_table_generate',
                        'description' => 'Generate Livewire Table component for given Eloquent Model',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'model' => [
                                    'type' => 'string',
                                    'description' => 'Full class name of Eloquent Model (e.g. App\Models\User)'
                                ],
                                'name' => [
                                    'type' => 'string',
                                    'description' => 'Table class name (e.g. UsersTable)'
                                ],
                                'columns' => [
                                    'type' => 'array',
                                    'description' => 'List of columns to include',
                                    'items' => ['type' => 'string']
                                ]
                            ],
                            'required' => ['model']
                        ]
                    ],
                    [
                        'name' => 'livewire_table_schema',
                        'description' => 'Get full schema documentation for Livewire Table Kit',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => []
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function handleCallTool(array $request): array
    {
        $toolName = $request['params']['name'];
        $arguments = $request['params']['arguments'];

        $result = match ($toolName) {
            'livewire_table_generate' => $this->generateTable($arguments),
            'livewire_table_schema' => $this->getSchema(),
            default => 'Unknown tool',
        };

        return [
            'jsonrpc' => '2.0',
            'id' => $request['id'],
            'result' => [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $result
                    ]
                ]
            ]
        ];
    }

    protected function generateTable(array $arguments): string
    {
        $model = $arguments['model'];
        $name = $arguments['name'] ?? class_basename($model) . 'Table';
        $columns = $arguments['columns'] ?? [];

        return <<<PHP
<?php

namespace App\Livewire\Tables;

use Unlab\LivewireTableKit\Livewire\Components\Tables\BaseTable;
use Illuminate\Database\Eloquent\Builder;
use $model;

class $name extends BaseTable
{
    public function query(): Builder
    {
        return $model::query();
    }

    public function columns(): array
    {
        return [
            // Add your columns here
        ];
    }
}
PHP;
    }

    protected function getSchema(): string
    {
        $schemaPath = __DIR__.'/../../../docs/mcp-schema.md';

        if (is_file($schemaPath)) {
            $schema = file_get_contents($schemaPath);

            if ($schema !== false) {
                return $schema;
            }
        }

        return <<<'MD'
# Livewire Table Kit MCP Schema

The MCP server exposes two tools:

- `livewire_table_generate`
- `livewire_table_schema`

Use `livewire_table_generate` to scaffold a Livewire table class from an Eloquent model.
Use `livewire_table_schema` to inspect the schema documentation for the package.
MD;
    }

    protected function respondJson(array $data): void
    {
        echo json_encode($data) . "\n";
        ob_flush();
        flush();
    }
}
