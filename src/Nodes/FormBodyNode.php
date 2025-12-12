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
class FormBodyNode extends StatementNode
{
	public static function create(Tag $tag): static
	{
		$tag->outputMode = $tag::OutputKeepIndentation;
		return new static;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'echo $this->global->formRenderingDispatcher->renderBody($this->global->formsStack);'
		);
	}


	public function &getIterator(): \Generator
	{
		if (false) {
			yield;
		}
	}
}