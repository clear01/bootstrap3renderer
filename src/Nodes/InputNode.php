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
use function array_shift;
use function count;

/**
 * {label}
 */
class InputNode extends \Nette\Bridges\FormsLatte\Nodes\InputNode
{


	public function print(PrintContext $context): string
	{
		return $context->format(
			'echo $this->global->formRenderingDispatcher->renderControl($this->global->formsStack, Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global), %2.node, $this->part)'
			. ' %3.line;',
			$this->name,
			$this->part,
			$this->attributes,
			$this->position,
		);



	}



}