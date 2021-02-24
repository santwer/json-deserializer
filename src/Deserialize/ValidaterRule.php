<?php


namespace antwersv\jsonDeserializer\Deserialize;


use Carbon\Carbon;

class ValidaterRule
{
    private string $class;
    private string $attitube;
    private string $type;
    private $addationalRules;

    public function __construct(string $class, string $attitube, $rule)
    {
        $this->class = $class;
        $this->attitube = $attitube;
        $this->addationalRules = $rule;
        $rp = new \ReflectionProperty($class, $attitube);
        $this->type = $rp->getType()?->getName();
    }

    public function toArray(): array
    {
        $rule = [$this->getRuleType()];
        if($this->isRequired()) {
            $rule[] = 'required';
        } else {
             $rule[] = 'nullable';
        }

        if(is_array($this->addationalRules)) {
            return array_merge($rule, $this->rule);
        }
        return $rule;
    }

    public function __toString(): string
    {
        return implode('|', $this->toArray());
    }


    private function getRuleType():string
    {
        switch ($this->type) {
            case "string":
                return 'string';
            case "int":
                return 'integer';
            case "float":
                return 'numeric';
            case "array":
                return 'array';
            case "bool":
                return 'boolean';
            case Carbon::class:
                return 'date';
        }
        if($this->type instanceof Rule) {
            //i dont know
        }
    }

    private function isRequired() : bool
    {//nullable
        $mode = call_user_func([$this->class, 'getMode']);
        return strtoupper($mode) == 'STRICT';
    }


}
