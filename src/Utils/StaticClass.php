<?php

namespace Clear01\BootstrapForm\Utils;

use Nette\StaticClassException;

/**
 * Trait for marking class as a static - disables instantiation by disabling constructor.
 *
 * @author Richard Ejem <richard@ejem.cz>
 */
trait StaticClass
{
    public function __construct() { throw new StaticClassException; }
}