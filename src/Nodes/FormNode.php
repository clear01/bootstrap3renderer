<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Clear01\BootstrapForm\Nodes;

use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;


/**
 * {form.body}
 */
class FormNode extends \Nette\Bridges\FormsLatte\Nodes\FormNode
{


	public function print(PrintContext $context): string
	{
		return $context->format(
			'$form = $this->global->formsStack[] = '
			. ($this->name instanceof StringNode
				? '$this->global->uiControl[%node]'
				: 'is_object($ʟ_tmp = %node) ? $ʟ_tmp : $this->global->uiControl[$ʟ_tmp]')
			. ' %line;'
			. 'Nette\Bridges\FormsLatte\Runtime::initializeForm($form);'
			. ($this->print
				? 'echo $this->global->formRenderingDispatcher->renderBegin($form, %node) %1.line;'
				: '')
			. ' %3.node '
			. ($this->print
				? 'echo $this->global->formRenderingDispatcher->renderEnd(array_pop($this->global->formsStack))'
				: 'array_pop($this->global->formsStack)')
			. " %4.line;\n\n",
			$this->name,
			$this->position,
			$this->attributes,
			$this->content,
			$this->endLine,
		);

	}



}