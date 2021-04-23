<?php


namespace antwersv\jsonDeserializer\Deserialize;


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

    public function save()
    {
        if($this->model === null || !class_exists($this->model)) {
            return false;
        }
        $modelKey = (new $this->model)->getKeyName();


        if(isset($this->{$modelKey}) && $this->{$modelKey} !== null) {
            $name = $this->{$modelKey};
            $entry = call_user_func_array([$name, '']);
        }
        dd('test', $modelKey, $this->model);
    }

    /**
     * @var string STRICT (all required|not empty) | NORMAL (null|orType) | LOSE
     */
    protected static $mode = "NORMAL";

    public static function getMode() {
        return self::$mode;
    }
}
