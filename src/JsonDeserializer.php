<?php


namespace antwersv\jsonDeserializer;


use antwersv\jsonDeserializer\Deserialize\Collection;
use antwersv\jsonDeserializer\Deserialize\ValidaterRule;
use Illuminate\Support\Facades\Validator;
use function Couchbase\defaultDecoder;

class JsonDeserializer
{
    private array $json = [];
    private string $class = "";
    protected $collection = null;
    protected $rules = null;
    protected $isArray = false;

    /**
     * @param  array  $json
     * @param  string|array  $class
     * @return \Illuminate\Support\Collection
     */
    public static function deserialize(array $json, string|array $class): Collection
    {
        $deserialier = new JsonDeserializer($json, $class);

        $data = $deserialier->getCollection();
        if ($data === null) {
            return new Collection();
        }
        return $data;
    }

    public static function save(Collection $collection)
    {
        $modelEntries = [];
        foreach ($collection as $item) {
            $modelEntries[$item->getModel()][] = $item->toArray();
        }
        foreach ($modelEntries as $model => $entries) {
            self::saveToModel($entries, $model);
        }
    }

    private static function saveToModel(array $entries, string $model)
    {
        if (! class_exists($model)) {
            return null;
        }
        $modelKey = (new $model)->getKeyName();
        $keys = array_map(fn($entry) => isset($entry[$modelKey]) ? $entry[$modelKey] : null, $entries);
        $dbEntries = self::checkModelEntries($model, $modelKey, $keys);

        foreach ($dbEntries as $entry) {
            $filtered = last(array_filter($entries, function ($item) use ($entry, $modelKey) {
                return isset($item[$modelKey]) && $item[$modelKey] === $entry->{$modelKey};
            }));
            foreach ($filtered as $modelAttr => $values) {
                $entry->{$modelAttr} = $values;
            }
            $entry->save();
        }

        $insertEntries = array_filter($entries, function ($item) use ($dbEntries, $modelKey) {
            if (! isset($item[$modelKey])) {
                return true;
            }
            return $dbEntries->where($modelKey, $item[$modelKey])->count() === 0;
        });
        return call_user_func_array([$model, 'insert'], [$insertEntries]);

    }

    /**
     * @param  string  $model
     * @param  string  $key
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private static function checkModelEntries(string $model, string $key, array $ids)
    {
        if (! class_exists($model)) {
            return null;
        }
        $ids = array_filter($ids, fn($id) => $id !== null);
        if (empty($ids)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }
        $entries = call_user_func_array([$model, 'whereIn'], [$key, $ids]);
        return $entries->get();
    }

    public static function getDeserializeClassData(string $class): ?array
    {
        if (! class_exists($class)) {
            return null;
        }
        return get_class_vars($class);
    }

    /**
     * JsonDeserializer constructor.
     * @param  array  $json
     * @param  string|array  $class
     */
    public function __construct(array $json, string|array $class)
    {
        $this->json = $json;
        if (is_array($class)) {
            $firstKey = array_key_first($class);
            $this->class = $class[$firstKey];
            $this->isArray = true;
        }

        $this->getClassVars();

    }

    /**
     * @return Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    private function getCollection()
    {
        $data = new Collection($this->validateData());
        $data = $data->map(function ($item) {
            return $this->getObjectFromClass($item);
        });
        return $data;

    }

    /**
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateData()
    {
        $validator = Validator::make($this->json,
            $this->getRules()
        );

        return $validator->validate();
    }

    private function getObjectFromClass(array $data)
    {
        return new $this->class($data);
    }

    /**
     * @return mixed
     */
    private function getRules()
    {
        return $this->rules->map(fn($rule) => (string) $rule)->toArray();
    }

    /**
     *
     */
    private function getClassVars()
    {
        $vars = get_class_vars($this->class);
        $this->rules = collect();
        foreach ($vars as $attitube => $rule) {
            $this->rules->put(
                $this->getRuleAttribute($attitube),
                new ValidaterRule($this->class, $attitube, $rule)
            );
        }
    }

    /**
     * @param $attitube
     * @return string
     */
    private function getRuleAttribute($attitube): string
    {
        if ($this->isArray) {
            $attitube = '*.'.$attitube;
        }
        return $attitube;
    }
}
