<?php


namespace antwersv\jsonDeserializer;


use antwersv\jsonDeserializer\Deserialize\ValidaterRule;
use Illuminate\Support\Facades\Validator;

class JsonDeserializer
{
    private array $json = [];
    private string $class = "";
    protected $collection = null;
    protected $rules = null;
    protected $isArray = false;

    /**
     * @param array $json
     * @param string|array $class
     * @return \Illuminate\Support\Collection
     */
    public static function deserialize(array $json, string|array $class): \Illuminate\Support\Collection
    {
        $deserialier = new JsonDeserializer($json, $class);

        $data = $deserialier->getCollection();
        if ($data === null) {
            return collect();
        }
        return $data;
    }

    /**
     * JsonDeserializer constructor.
     * @param array $json
     * @param string|array $class
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

    private function getCollection()
    {
        $data = collect($this->validateData());
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
        return $this->rules->map(fn($rule) => (string)$rule)->toArray();
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
            $attitube = '*.' . $attitube;
        }
        return $attitube;
    }
}
