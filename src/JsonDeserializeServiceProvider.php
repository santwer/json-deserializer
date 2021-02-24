<?php

namespace antwersv\jsonDeserializer;

use antwersv\jsonDeserializer\Commands\MakeDeserializer;
use Illuminate\Support\ServiceProvider;

class JsonDeserializeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            MakeDeserializer::class
        ]);
    }

    public function register()
    {

    }
}
