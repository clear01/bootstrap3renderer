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
class ContainerNode extends StatementNode
{
	public static function create(Tag $tag): StatementNode
	{
		$tag->outputMode = $tag::OutputRemoveIndentation;
		$tag->expectArguments();

		$node = $tag->node = new static;
		$node->name = $tag->parser->parseUnquotedStringOrExpression();
		return $node;
	}

	public function print(PrintContext $context): string
	{
		return $context->format(
			'$formContainer = Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global); '
			. 'echo $this->global->formRenderingDispatcher->renderContainer($this->global->formsStack, $formContainer) %line;'
			. "\n\n",
			$this->name,
			$this->position,
			$this->content,
		);
	}

	public function &getIterator(): \Generator
	{
		yield $this->name;
	}

}