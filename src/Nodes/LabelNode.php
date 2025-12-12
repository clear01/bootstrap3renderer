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
use function count;

/**
 * {label}
 */
class LabelNode extends \Nette\Bridges\FormsLatte\Nodes\LabelNode
{
/*
 *   return $writer->write(
            $this->ln($node)
            . '$_label = ' // $_label is used by macroLabelEnd
            . $this->renderingDispatcher
            . '->renderLabel($this->global->formsStack, ')
        . $this->writeControlReturningExpression($writer, $name)
        . $writer->write(', ')
        . $this->writeAttrsFromMacroOrTag($writer, $attrs)
        . $writer->write(
            ', %0.var); echo $_label'
            . ($attrs === NULL ? '' : '->attributes()'),
            count($words) ? $words[0] : NULL
        );
 */

	public function print(PrintContext $context): string
	{
		return $context->format(
			'$ʟ_label_item = Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global);'
			. 'echo ($ʟ_label = $this->global->formRenderingDispatcher->renderLabel($this->global->formsStack, $ʟ_label_item, %2.node, $this->part))'
			. ($this->attributes->items ? '?->addAttributes(%2.node)' : '')
			. ($this->void ? ' %3.line;' : '?->startTag() %3.line; %4.node echo $ʟ_label?->endTag() %5.line;'),
			$this->name,
			$this->part,
			$this->attributes,
			$this->position,
			$this->content,
			$this->endLine,
		);

	}



}