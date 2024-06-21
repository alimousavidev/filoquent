<?php

namespace AliMousavi\Filoquent\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class MakeFilter
 *
 * This class handles the creation of a new filter class for Eloquent models.
 *
 * @package AliMousavi\Filoquent\Commands
 */
class MakeFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:filter {filter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Created a new filter class for your request.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $filter = $this->argument('filter');
        $path = app_path("Filters/{$filter}.php");

        if (File::exists($path)) {
            $this->error("Filter class {$filter} already exists!");
            return;
        }

        $namespace = $this->getNamespace($filter);
        $filterName = $this->getClassName($filter);

        $this->createDirectoryIfNeeded($namespace);

        $stubContent = $this->getStubContent();
        if ($namespace != '') {
            $namespace = "\\" . $namespace;
        }

        $filterContent = str_replace(['{{ filter }}', '{{ namespace }}'], [$filterName, $namespace], $stubContent);

        File::put($path, $filterContent);
        $this->info("Filter class {$filter} created successfully.");
    }

    /**
     * Get the namespace part of the filter class.
     *
     * @param string $filter
     * @return string
     */
    protected function getNamespace(string $filter): string
    {
        $parts = explode('\\', $filter);
        array_pop($parts);
        return implode('\\', $parts);
    }

    /**
     * Get the class name part of the filter class.
     *
     * @param string $filter
     * @return string
     */
    protected function getClassName(string $filter): string
    {
        $parts = explode('\\', $filter);
        return array_pop($parts);
    }

    /**
     * Create the directory if it does not exist.
     *
     * @param string $namespace
     * @return void
     */
    protected function createDirectoryIfNeeded(string $namespace): void
    {
        $directory = app_path('Filters/' . str_replace('\\', '/', $namespace));
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Get the content of the stub file.
     *
     * @return string
     */
    protected function getStubContent(): string
    {
        return file_get_contents(__DIR__ . '/../../stubs/filter.stub');
    }
}
