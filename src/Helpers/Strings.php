<?php

namespace Clear01\BootstrapForm\Helpers;

use Clear01\BootstrapForm\Utils\StaticClass;

class Strings
{
    use StaticClass;

    /**
     * Returns substring of $string after last occurence of $separator.
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function afterLast($string, $separator)
    {
        $pos = mb_strrpos($string, $separator);
        return $pos === FALSE ? $string : mb_substr($string, $pos + mb_strlen($separator));
    }

    /**
     * Formats the string like sprintf, but with named arguments.
     *
     * Arguments format: %(name[;format])
     * % must be escaped as %%
     * Examples:
     * <code>
     * Strings::format('lorem %(foo)', ['foo' => 'ipsum']) -> returns 'lorem ipsum'
     * Strings::format('number %(foo;.3f)', ['foo' => 9]) -> returns 'number 9.000'
     * </code>
     *
     * @param string $str
     * @param array $args
     * @return string
     */
    public static function format($str, array $args)
    {
        $result = '';
        while (preg_match('~^(?P<prefix>(?:[^%]|%%)*)%\((?P<name>[a-z0-9_-]+)(?:;(?P<format>[^)]+))?\)(?P<postfix>.*)$~i', $str, $m)) {
            $str = $m['postfix'];
            $arg = $args[$m['name']];
            $result .= str_replace('%%', '%', $m['prefix'])
                . (!empty($m['format']) ? sprintf('%' . $m['format'], $arg) : $arg);
        }
        $result .= str_replace('%%', '%', $str);

        return $result;
    }
}