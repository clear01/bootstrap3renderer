<?php

namespace Clear01\BootstrapForm;

use Nette\SmartObject;
use Nette\Utils\Strings;

class PairAttributes
{
    use SmartObject;

    public $container = [];
    public $input = [];
    public $label = [];

    public static function fetch(array $attrs)
    {
        $p = new static;
        foreach ($attrs as $key => $val) {
            if (Strings::startsWith($key, 'input-')) {
                $p->input[substr($key, 6)] = $val;
            } elseif (Strings::startsWith($key, 'label-')) {
                $p->label[substr($key, 6)] = $val;
            } else {
                $p->container[$key] = $val;
            }
        }
        return $p;
    }
}
