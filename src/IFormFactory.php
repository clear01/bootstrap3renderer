<?php

namespace Clear01\BootstrapForm;

use Nette\Forms\Form;

interface IFormFactory
{
    /**
     * @param string $formClass - Nette\Forms\Form descendant class. If NULL, it uses
     * if Nette\Application\UI\Form if it exists or Nette\Forms\Form otherwise.
     * @return Form
     */
    public function create($formClass = NULL);
}
