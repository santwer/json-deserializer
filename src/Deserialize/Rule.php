<?php


namespace antwersv\jsonDeserializer\Deserialize;


class Rule
{
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



    /**
     * @var string STRICT (all required|not empty) | NORMAL (null|orType) | LOSE
     */
    protected static $mode = "NORMAL";

    public static function getMode() {
        return self::$mode;
    }
}
