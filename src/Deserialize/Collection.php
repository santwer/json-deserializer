<?php


namespace antwersv\jsonDeserializer\Deserialize;


use antwersv\jsonDeserializer\JsonDeserializer;
use function Couchbase\defaultDecoder;

class Collection extends \Illuminate\Support\Collection
{
    public function save()
    {
        JsonDeserializer::save($this);
    }
}
