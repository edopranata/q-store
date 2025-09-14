<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class MakeServiceCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class with repository interface and repository';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/service.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Services';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceModel($stub, $name)
            ->replaceCamelModel($stub, $name)
            ->replacePluralModel($stub, $name)
            ->replaceSnakePluralModel($stub, $name)
            ->replaceRepositoryInterface($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the model name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceModel(&$stub, $name)
    {
        $modelName = $this->getModelName($name);
        $stub = str_replace(['{{ model }}', '{{model}}'], $modelName, $stub);

        return $this;
    }

    /**
     * Replace the repository interface name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceRepositoryInterface(&$stub, $name)
    {
        $repositoryInterface = $this->getRepositoryInterfaceName($name);
        $stub = str_replace(['{{ repositoryInterface }}', '{{repositoryInterface}}'], $repositoryInterface, $stub);

        return $this;
    }

    /**
     * Replace the camel case model name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceCamelModel(&$stub, $name)
    {
        $camelModel = Str::camel($this->getModelName($name));
        $stub = str_replace(['{{ camelModel }}', '{{camelModel}}'], $camelModel, $stub);

        return $this;
    }

    /**
     * Replace the plural model name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replacePluralModel(&$stub, $name)
    {
        $pluralModel = Str::plural($this->getModelName($name));
        $stub = str_replace(['{{ pluralModel }}', '{{pluralModel}}'], $pluralModel, $stub);

        return $this;
    }

    /**
     * Replace the snake case plural model name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceSnakePluralModel(&$stub, $name)
    {
        $snakePluralModel = Str::snake(Str::plural($this->getModelName($name)));
        $stub = str_replace(['{{ snakePluralModel }}', '{{snakePluralModel}}'], $snakePluralModel, $stub);

        return $this;
    }

    /**
     * Get the model name from the service name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getModelName($name)
    {
        $serviceName = class_basename($name);
        return str_replace('Service', '', $serviceName);
    }

    /**
     * Get the repository interface name from the service name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getRepositoryInterfaceName($name)
    {
        $modelName = $this->getModelName($name);
        return $modelName . 'RepositoryInterface';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the service already exists'],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        // Validate that the model exists
        $modelName = $this->getNameInput();
        if (!$this->modelExists($modelName)) {
            $this->error("Model '{$modelName}' does not exist. Available models: " . implode(', ', $this->getAvailableModels()));
            return false;
        }

        // Create Service
        if (parent::handle() === false && ! $this->option('force')) {
            return false;
        }

        // Create Repository Interface
        $this->createRepositoryInterface();
        
        // Create Repository
        $this->createRepository();

        return true;
    }

    /**
     * Check if model exists.
     *
     * @param string $modelName
     * @return bool
     */
    protected function modelExists(string $modelName): bool
    {
        $modelPath = app_path("Models/{$modelName}.php");
        return file_exists($modelPath);
    }

    /**
     * Get available models.
     *
     * @return array
     */
    protected function getAvailableModels(): array
    {
        $modelsPath = app_path('Models');
        $models = [];
        
        if (is_dir($modelsPath)) {
            $files = scandir($modelsPath);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $models[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }
        
        return $models;
    }

    /**
     * Create the repository interface.
     *
     * @return void
     */
    protected function createRepositoryInterface()
    {
        $modelName = $this->getNameInput();
        $interfaceName = $modelName . 'RepositoryInterface';
        $interfacePath = app_path("Repositories/Contracts/{$interfaceName}.php");
        
        if (file_exists($interfacePath) && !$this->option('force')) {
            $this->error("Repository Interface {$interfaceName} already exists!");
            return;
        }
        
        $this->makeDirectory($interfacePath);
        
        $stub = $this->getRepositoryInterfaceStub();
        $stub = $this->replaceRepositoryInterfacePlaceholders($stub, $modelName);
        
        file_put_contents($interfacePath, $stub);
        $this->info("Repository Interface [{$interfacePath}] created successfully.");
    }

    /**
     * Create the repository.
     *
     * @return void
     */
    protected function createRepository()
    {
        $modelName = $this->getNameInput();
        $repositoryName = $modelName . 'Repository';
        $repositoryPath = app_path("Repositories/{$repositoryName}.php");
        
        if (file_exists($repositoryPath) && !$this->option('force')) {
            $this->error("Repository {$repositoryName} already exists!");
            return;
        }
        
        $this->makeDirectory($repositoryPath);
        
        $stub = $this->getRepositoryStub();
        $stub = $this->replaceRepositoryPlaceholders($stub, $modelName);
        
        file_put_contents($repositoryPath, $stub);
        $this->info("Repository [{$repositoryPath}] created successfully.");
    }

    /**
     * Get the repository interface stub.
     *
     * @return string
     */
    protected function getRepositoryInterfaceStub()
    {
        $stubPath = $this->resolveStubPath('/stubs/repository-interface.stub');
        return file_get_contents($stubPath);
    }

    /**
     * Get the repository stub.
     *
     * @return string
     */
    protected function getRepositoryStub()
    {
        $stubPath = $this->resolveStubPath('/stubs/repository.stub');
        return file_get_contents($stubPath);
    }

    /**
     * Replace placeholders in repository interface stub.
     *
     * @param string $stub
     * @param string $modelName
     * @return string
     */
    protected function replaceRepositoryInterfacePlaceholders($stub, $modelName)
    {
        $replacements = [
            '{{ namespace }}' => 'App\\Repositories\\Contracts',
            '{{namespace}}' => 'App\\Repositories\\Contracts',
            '{{ class }}' => $modelName . 'RepositoryInterface',
            '{{class}}' => $modelName . 'RepositoryInterface',
            '{{ model }}' => $modelName,
            '{{model}}' => $modelName,
            '{{ camelModel }}' => Str::camel($modelName),
            '{{camelModel}}' => Str::camel($modelName),
            '{{ pluralModel }}' => Str::plural($modelName),
            '{{pluralModel}}' => Str::plural($modelName),
            '{{ snakePluralModel }}' => Str::snake(Str::plural($modelName)),
            '{{snakePluralModel}}' => Str::snake(Str::plural($modelName)),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Replace placeholders in repository stub.
     *
     * @param string $stub
     * @param string $modelName
     * @return string
     */
    protected function replaceRepositoryPlaceholders($stub, $modelName)
    {
        $replacements = [
            '{{ namespace }}' => 'App\\Repositories',
            '{{namespace}}' => 'App\\Repositories',
            '{{ class }}' => $modelName . 'Repository',
            '{{class}}' => $modelName . 'Repository',
            '{{ model }}' => $modelName,
            '{{model}}' => $modelName,
            '{{ camelModel }}' => Str::camel($modelName),
            '{{camelModel}}' => Str::camel($modelName),
            '{{ pluralModel }}' => Str::plural($modelName),
            '{{pluralModel}}' => Str::plural($modelName),
            '{{ snakePluralModel }}' => Str::snake(Str::plural($modelName)),
            '{{snakePluralModel}}' => Str::snake(Str::plural($modelName)),
            '{{ repositoryInterface }}' => $modelName . 'RepositoryInterface',
            '{{repositoryInterface}}' => $modelName . 'RepositoryInterface',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stub);
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }
}