<?php

namespace Chocofamily\LaravelEventSauce\Console;

use Chocofamily\LaravelEventSauce\Exceptions\MakeFileFailed;

final class MakeConsumerCommand extends MakeCommand
{
    protected $signature = 'make:consumer {class}';

    protected $description = 'Create a new consumer class';

    public function handle(): void
    {
        $consumerClass = $this->formatClassName($this->argument('class'));
        $consumerPath = $this->getPath($consumerClass);
        try {
            $this->ensureValidPaths([
                $consumerPath,
            ]);
        } catch (MakeFileFailed $exception) {
            $this->error($exception->getMessage());
        }
        $this->makeDirectory($consumerPath);
        $this->makeFiles(
            ['Consumer' => $consumerPath],
            [
                'consumer' => class_basename($consumerClass),
                'namespace' => substr($consumerClass, 0, strrpos($consumerClass, '\\')),
            ]
        );
        $this->info("{$consumerClass} class created successfully!");
    }
}
