<?php

namespace Chocofamily\LaravelEventSauce\Console;

use DateTimeImmutable;
use Chocofamily\LaravelEventSauce\Exceptions\MakeFileFailed;
use Illuminate\Support\Str;

final class MakeAggregateCommand extends MakeCommand
{
    protected $signature = 'make:aggregate-root {namespace} {--migration}';

    protected $description = 'Create a new aggregate root and resources';

    public function handle(): void
    {
        $aggregateRootClass = $this->formatClassName($this->argument('namespace'));
        $aggregateRootPath = $this->getPath($aggregateRootClass);

        $aggregateRootIdClass = $this->formatClassName($this->argument('namespace').'Id');
        $aggregateRootIdPath = $this->getPath($aggregateRootIdClass);

        $aggregateRootRepositoryClass = $this->formatClassName($this->argument('namespace').'Repository');
        $aggregateRootRepositoryPath = $this->getPath($aggregateRootRepositoryClass);

        try {
            $this->ensureValidPaths([
                $aggregateRootPath,
                $aggregateRootIdPath,
                $aggregateRootRepositoryPath,
            ]);
        } catch (MakeFileFailed $exception) {
            $this->error($exception->getMessage());
        }
        $this->makeDirectory($aggregateRootPath);

        $replacements = [
            'aggregateRoot' => $aggregateRoot = class_basename($aggregateRootClass),
            'namespace' => substr($aggregateRootClass, 0, strrpos($aggregateRootClass, '\\')),
            'table' => Str::snake(class_basename($aggregateRootClass)).'_domain_messages',
            'migration' => 'Create'.ucfirst(class_basename($aggregateRootClass)).'DomainMessagesTable',
        ];

        $this->makeFiles([
            'AggregateRoot' => $aggregateRootPath,
            'AggregateRootId' => $aggregateRootIdPath,
            'AggregateRootRepository' => $aggregateRootRepositoryPath,
        ], $replacements);

        if ($this->option('migration')) {
            $this->createMigration($replacements);
        }

        $this->info("{$aggregateRoot} classes and resources created successfully!");

        if ($this->option('migration')) {
            $this->comment("Run `php artisan migrate` to create the {$replacements['table']} table.");
        }
    }

    private function createMigration(array $replacements): void
    {
        $timestamp = (new DateTimeImmutable())->format('Y_m_d_His');
        $filename = "{$timestamp}_create_{$replacements['table']}_table.php";

        $this->filesystem->put(
            $this->laravel->databasePath("migrations/{$filename}"),
            $this->getStubContent('create_domain_messages_table', $replacements)
        );
    }
}
