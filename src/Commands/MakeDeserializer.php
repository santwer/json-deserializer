<?php

namespace antwersv\jsonDeserializer\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeDeserializer extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:deserializer {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Deserializer Class';

    protected $type = 'Deserialize';

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Deserialize';
    }


    protected function getStub()
    {
        $stub = null;
        $stub = '/../stubs/deserialize.stub';
        return $this->resolveStubPath($stub);
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

    protected function createFolderIfNotExists()
    {
        $folder = $this->laravel->basePath(trim('app/Http/Deserialize', '/'));
        if(!is_dir($folder)) {
            mkdir($folder);
        }
    }


    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $this->createFolderIfNotExists();
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        $replace["use {$controllerNamespace}\Services;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }
}
