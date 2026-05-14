<?php

use Illuminate\Support\Facades\File;

it('installs the skill files into project-local and codex directories', function (): void {
    $projectSkillRoot = sys_get_temp_dir().'/livewire-table-kit-ai-'.uniqid('', true);
    $codexSkillRoot = sys_get_temp_dir().'/livewire-table-kit-codex-'.uniqid('', true);

    File::ensureDirectoryExists($projectSkillRoot);
    File::ensureDirectoryExists($codexSkillRoot);

    $this->expectsQuestion('Install the skill files for Codex and project-local AI workflows?', 'yes');

    $this->artisan('livewire-table-kit:install-skill', [
        '--ai-path' => $projectSkillRoot,
        '--codex-path' => $codexSkillRoot,
    ])->assertExitCode(0);

    expect(File::exists($projectSkillRoot.'/livewire-table-kit/SKILL.md'))->toBeTrue();
    expect(File::exists($projectSkillRoot.'/livewire-table-kit/agents/openai.yaml'))->toBeTrue();
    expect(File::exists($codexSkillRoot.'/livewire-table-kit/SKILL.md'))->toBeTrue();
    expect(File::exists($codexSkillRoot.'/livewire-table-kit/agents/openai.yaml'))->toBeTrue();
    expect(File::get($projectSkillRoot.'/livewire-table-kit/SKILL.md'))
        ->toContain('name: "livewire-table-kit"')
        ->toContain('description: "Senior engineer workflow for Unlab Livewire Table Kit package development. Use for source edits, generator logic, AI skills, and documentation."');
    expect(File::get($codexSkillRoot.'/livewire-table-kit/SKILL.md'))
        ->toContain('name: "livewire-table-kit"')
        ->toContain('description: "Senior engineer workflow for Unlab Livewire Table Kit package development. Use for source edits, generator logic, AI skills, and documentation."');

    File::deleteDirectory($projectSkillRoot);
    File::deleteDirectory($codexSkillRoot);
});
