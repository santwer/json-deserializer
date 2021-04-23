<?php


namespace antwersv\jsonDeserializer\Deserialize;


use antwersv\jsonDeserializer\JsonDeserializer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use function Couchbase\defaultDecoder;

class Deserialize
{
    /**
     * @var Illuminate\Database\Eloquent\Model $model
     */
    protected $model = null;

    public function __construct(?array $data = null)
    {
        if($data !== null) {
            $this->setAttributes($data);
        }
    }

    public function getModel()
    {
        return $this->model;
    }

    private function setAttributes(array $data) {
            foreach($data as $attribute => $value) {
                $rp = new \ReflectionProperty($this, $attribute);
                $typeName = $rp->getType()?->getName();
                if(class_exists($typeName)) {
                    $this->{$attribute} = call_user_func_array ([$typeName, 'parse'], [$value]);
                } else {
                    $this->{$attribute} = $value;
                }
            }
    }

    public function toArray()
    {
        $attributes = JsonDeserializer::getDeserializeClassData(get_class ($this));
        if($attributes === null) {
            return [];
        }
        return $this->getAttributes($attributes)->toArray();
    }

    /**
     * @param  array  $attributes
     * @return \Illuminate\Support\Collection
     */
    private function getAttributes(array $attributes) :\Illuminate\Support\Collection
    {
       $collect = collect();
       foreach($attributes as $key => $attribute) {
           $value = null;
           if(isset($this->{$key})) {
               $value = $this->{$key};
           }
           $collect->put($key, $value);
       }
       return $collect;
    }

    public function save()
    {
        if($this->model === null || !class_exists($this->model)) {
            return false;
        }
        $modelKey = (new $this->model)->getKeyName();
        /**
         * @var Model
         */
        $entry = null;
        if(isset($this->{$modelKey}) && $this->{$modelKey} !== null) {
            $value = $this->{$modelKey};
            $entry = call_user_func_array([$this->model, 'find'], [$value]);
        }
        if($entry === null) {
            $entry = new $this->model;
        }
        foreach($this->toArray() as $key => $value)
        {
            $entry->{$key} = $value;
        }
        return $entry->save();
    }

    /**
     * @var string STRICT (all required|not empty) | NORMAL (null|orType) | LOSE
     */
    protected static $mode = "NORMAL";

    public static function getMode() {
        return self::$mode;
    }
}
