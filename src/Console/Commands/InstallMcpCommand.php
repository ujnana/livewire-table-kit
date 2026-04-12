<?php

namespace Unlab\LivewireTableKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallMcpCommand extends Command
{
    protected $signature = 'livewire-table-kit:install-mcp';

    protected $description = 'Install Livewire Table Kit AI Skills (MCP Server)';

    public function handle(Filesystem $filesystem): int
    {
        $this->info('📦 Installing Livewire Table Kit AI Skills');
        $this->newLine();

        if (! $this->confirm('Do you want to install MCP AI Skills for this package?', true)) {
            $this->info('❌ Installation cancelled. No changes were made.');
            return 0;
        }

        $mcpDirectory = base_path('.mcp');
        
        if (! $filesystem->exists($mcpDirectory)) {
            $filesystem->makeDirectory($mcpDirectory);
            $this->line('  Created <info>.mcp</info> directory');
        }

        $configPath = $mcpDirectory . '/livewire-table-kit.json';
        
        $filesystem->put($configPath, json_encode([
            'mcpServers' => [
                'livewire-table-kit' => [
                    'command' => 'php',
                    'args' => [
                        'artisan',
                        'livewire-table-kit:mcp'
                    ],
                    'cwd' => base_path()
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->line('  Published <info>.mcp/livewire-table-kit.json</info> configuration');
        $this->newLine();

        $this->info('✅ AI Skills installed successfully!');
        $this->newLine();
        $this->info('📝 Restart your AI client (Claude Desktop, Windsurf, Cline) to activate the skills.');
        $this->newLine();

        return 0;
    }
}