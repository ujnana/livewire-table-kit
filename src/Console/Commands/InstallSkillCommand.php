<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class InstallSkillCommand extends Command
{
    protected $signature = 'livewire-table-kit:install-skill
        {--codex-path= : Codex skills directory root. Defaults to $CODEX_HOME/skills or ~/.codex/skills}
        {--ai-path= : Project AI skills directory root. Defaults to .ai/skills}
        {--force : Overwrite existing skill files}';

    protected $description = 'Install Livewire Table Kit AI skill files for Codex and Boost-compatible workflows';

    public function __construct(
        protected Filesystem $files,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('📦 Installing Livewire Table Kit AI skill files');
        $this->newLine();

        if (! $this->confirm('Install the skill files for Codex and project-local AI workflows?', true)) {
            $this->info('❌ Installation cancelled. No changes were made.');

            return self::SUCCESS;
        }

        $sourcePath = __DIR__.'/../../../resources/boost/skills/livewire-table-kit';
        $skillName = 'livewire-table-kit';
        $force = (bool) $this->option('force');

        foreach ($this->targetRoots() as $label => $rootPath) {
            $targetPath = rtrim($rootPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$skillName;
            $this->installSkillDirectory($sourcePath, $targetPath, $force);
            $this->line("  Installed <info>{$label}</info> skill to <info>{$targetPath}</info>");
        }

        $this->newLine();
        $this->info('✅ AI skill files installed successfully!');
        $this->newLine();
        $this->info('📝 Restart your AI client or reload its skill index if it does not pick up the files immediately.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    protected function targetRoots(): array
    {
        $roots = [];

        $aiPath = trim((string) $this->option('ai-path'));
        if ($aiPath === '') {
            $aiPath = base_path('.ai/skills');
        } elseif (! $this->isAbsolutePath($aiPath)) {
            $aiPath = base_path($aiPath);
        }

        $roots['project AI'] = $aiPath;

        $codexPath = trim((string) $this->option('codex-path'));
        if ($codexPath === '') {
            $codexHome = $this->homeDirectoryFromEnvironment('CODEX_HOME');
            $homeDirectory = $this->homeDirectoryFromEnvironment('HOME');

            if ($codexHome !== null) {
                $codexPath = rtrim($codexHome, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'skills';
            } elseif ($homeDirectory !== null) {
                $codexPath = rtrim($homeDirectory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'.codex'.DIRECTORY_SEPARATOR.'skills';
            } else {
                $codexPath = base_path('.codex/skills');
            }
        } elseif (! $this->isAbsolutePath($codexPath)) {
            $codexPath = base_path($codexPath);
        }

        $roots['codex'] = $codexPath;

        return $roots;
    }

    protected function installSkillDirectory(string $sourcePath, string $targetPath, bool $force): void
    {
        $this->files->ensureDirectoryExists($targetPath);

        foreach ($this->files->allFiles($sourcePath) as $file) {
            $relativePath = Str::of($file->getPathname())
                ->replace($sourcePath.DIRECTORY_SEPARATOR, '')
                ->toString();

            $destination = $targetPath.DIRECTORY_SEPARATOR.$relativePath;
            $this->files->ensureDirectoryExists(dirname($destination));

            if ($this->files->exists($destination) && ! $force) {
                continue;
            }

            $this->files->copy($file->getPathname(), $destination);
        }
    }

    protected function isAbsolutePath(string $path): bool
    {
        return Str::startsWith($path, ['/', '\\']) || (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }

    protected function homeDirectoryFromEnvironment(string $key): ?string
    {
        $value = getenv($key);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return rtrim($value, DIRECTORY_SEPARATOR);
    }
}
