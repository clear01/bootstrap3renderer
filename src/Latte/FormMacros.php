<?php

namespace Clear01\BootstrapForm\Latte;

use Latte\CompileException;
use Latte\Compiler;
use Latte\Macros\MacroSet;
use Latte\MacroNode;
use Latte\PhpWriter;
use Nette\Bridges\FormsLatte\FormMacros as NFormMacros;

/**
 * Provides extra form macros:
 *
 * <code>
 * {pair name|$control} as {$form->getRenderer()->renderPair($form['name'])}
 * {group name|$group} as {$form->getRenderer()->renderGroup($form['name'])}
 * {container name|$container} as {$form->getRenderer()->renderContainer($form['name'])}
 * {form.errors [all]]} as {$form->getRenderer()->renderGlobalErrors(!$all)}
 * {form.body} as {$form->getRenderer()->renderBody()}
 * {input.errors name|$control} as {$form->getRenderer()->renderControlErrors($form['name'])}
 * </code>
 *
 * Overrides form macros:
 *
 * <code>
 * {form} to render form begin and end using custom renderer
 *        (FormsLatte\FormMacros uses FormsLatte\Runtime::renderFormBegin directly)
 *
 * {label}
 * {input} to enable custom renderers of labels and controls
 *           (FormsLatte\FormMacros renders the controls directly without renderer processing)
 *
 * <form n:name>
 * <label n:name>
 * <input|select|textarea|button n:name>
 * </code>
 *
 * Overridden macros are passed to extended form renderer if available, otherwise they are processed
 * as usual:
 *   - form using Nette\Bridges\FormsLatte\Runtime::renderForm(Begin|End)
 *   - label and control passing through Html from IControl::get(Control|Label)(Part)?()
 *
 */
class FormMacros extends NFormMacros
{

    private $renderingDispatcher = '$this->global->formRenderingDispatcher';

    private static $supportedNameTags = ['select', 'input', 'button', 'textarea', 'form', 'label'];

    /**
     * @param Compiler $compiler
     * @return MacroSet
     */
    public static function install(Compiler $compiler)
    {
        $me = new static($compiler);
        $me->addMacro('pair', [$me, 'macroPair']);
        $me->addMacro('group', [$me, 'macroGroup']);
        $me->addMacro('container', [$me, 'macroContainer']);
        $me->addMacro('form', [$me, 'macroForm'], [$me, 'macroFormEnd']);
        $me->addMacro('form.errors', [$me, 'macroFormErrors']);
        $me->addMacro('form.body', [$me, 'macroFormBody']);
        $me->addMacro('label', [$me, 'macroLabel'], [$me, 'macroLabelEnd'], NULL, self::AUTO_EMPTY);
        $me->addMacro('input', [$me, 'macroInput']);
        $me->addMacro('input.errors', [$me, 'macroInputErrors']);
        $me->addMacro('name', [$me, 'macroName'], [$me, 'macroNameEnd'], [$me, 'macroNameAttr']);
        return $me;
    }

    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     */
    public function macroPair(MacroNode $node, PhpWriter $writer)
    {
        return $writer->write(
            $this->ln($node)
            . 'echo '
            . $this->renderingDispatcher
            . '->renderPair($this->global->formsStack, ')
        . $this->renderFormComponent($node, $writer)
        . $writer->write(', %node.array)');
    }

    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     */
    public function macroGroup(MacroNode $node, PhpWriter $writer)
    {
        return $writer->write(
            $this->ln($node)
            . 'echo '
            . $this->renderingDispatcher
            . '->renderGroup($this->global->formsStack,'
            . 'is_object(%node.word) ? %node.word : reset($this->global->formsStack)->getGroup(%node.word))');
    }

    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     */
    public function macroContainer(MacroNode $node, PhpWriter $writer)
    {
        // writer intentionally not used - already processed by renderFormComponent
        return $writer->write(
            $this->ln($node)
            . 'echo '
            . $this->renderingDispatcher
            . '->renderContainer($this->global->formsStack, ')
        . $this->renderFormComponent($node, $writer)
        . $writer->write(')');
    }

    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroForm(MacroNode $node, PhpWriter $writer)
    {
        parent::macroForm($node, $writer); //to use argument validations from Nette and set node->replaced
        return $this->_macroFormBegin($node, $writer);
    }

    /**
     * Common handler for {form} and <form n:name>
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @param array|NULL $attrs if NULL, whole tag is normally rendered. if not NULL, redirected from <form n:name>
     *   -> override attrs and render only remaining attributes
     * @return string
     */
    protected function _macroFormBegin(MacroNode $node, PhpWriter $writer, array $attrs = NULL)
    {
        $name = $node->tokenizer->fetchWord();
        $node->tokenizer->reset();

        $formRetrievalCode = ($name[0] === '$' ? 'is_object(%node.word) ? %node.word : ' : '')
            . '$this->global->uiControl[%node.word]';
        return $writer->write(
            $this->ln($node)
            . 'echo '
            . $this->renderingDispatcher
            . '->renderBegin($form = $_form = $this->global->formsStack[] = '
            . $formRetrievalCode
            . ', '
            . ($attrs === NULL ? '%node.array' : '%0.var'),
            $attrs
        )
        . $writer->write(', %0.var)',
            $attrs === NULL
        );

    }

    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @param bool $withTags false = skip </form> tag
     * @return string
     */
    public function macroFormEnd(MacroNode $node, PhpWriter $writer, $withTags = TRUE)
    {
        return $writer->write(
            $this->ln($node)
            . 'echo ' . $this->renderingDispatcher . '->renderEnd(array_pop($this->global->formsStack), %0.var)',
            $withTags
        );
    }

    /**
     * {label ...}
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroLabel(MacroNode $node, PhpWriter $writer)
    {
        parent::macroLabel($node, $writer);
        $node->tokenizer->reset();
        return $this->_macroLabel($node, $writer);
    }

    /**
     * Common handle for {label} and <label n:name>
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @param array $attrs
     * @return string
     */
    protected function _macroLabel(MacroNode $node, PhpWriter $writer, array $attrs = NULL)
    {
        $words = $node->tokenizer->fetchWords();
        $name = array_shift($words);

        return $writer->write(
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
    }

    /**
     * {input ...}
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroInput(MacroNode $node, PhpWriter $writer)
    {
        parent::macroInput($node, $writer);
        $node->tokenizer->reset();
        return $this->_macroInput($node, $writer);
    }

    /**
     * Common handle for {input} and <input n:name>
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @param array $attrs
     * @return string
     * @throws CompileException
     */
    public function _macroInput(MacroNode $node, PhpWriter $writer, array $attrs = NULL)
    {
        $words = $node->tokenizer->fetchWords();
        $name = array_shift($words);

        return $writer->write(
            $this->ln($node)
            . '$_input = '
            . $this->renderingDispatcher
            . '->renderControl($this->global->formsStack, $_control = ')
        . $this->writeControlReturningExpression($writer, $name)
        . $writer->write(', ')
        . $this->writeAttrsFromMacroOrTag($writer, $attrs)
        . $writer->write(
            ', %0.raw); echo $_input'
            . ($attrs === NULL ? '' : '->attributes()'),
            count($words) ? $writer->formatWord($words[0]) : 'NULL'
        );
    }

    /**
     * {form.errors}
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroFormErrors(MacroNode $node, PhpWriter $writer)
    {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        }
        $node->replaced = TRUE;
        return $writer->write(
            $this->ln($node)
            . 'echo '
            . $this->renderingDispatcher . '->renderGlobalErrors($this->global->formsStack%0.raw);',
            $node->args === 'all' ? ', FALSE' : ''
        );
    }

    /**
     * {form.body}
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroFormBody(MacroNode $node, PhpWriter $writer)
    {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        }
        $node->replaced = TRUE;
        return $writer->write(
            $this->ln($node)
            . 'echo '
            . $this->renderingDispatcher . '->renderBody($this->global->formsStack);'
        );
    }

    /**
     * {input.errors ...}
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    public function macroInputErrors(MacroNode $node, PhpWriter $writer)
    {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        }
        $words = $node->tokenizer->fetchWords();
        if (!$words) {
            throw new CompileException('Missing name in ' . $node->getNotation());
        }
        $node->replaced = TRUE;
        $name = array_shift($words);

        $ctrlExpr = ($name[0] === '$' ? 'is_object(%0.word) ? %0.word : ' : '')
            . 'end($this->global->formsStack)[%0.word]';
        return $writer->write(
            $this->ln($node)
            . 'echo '
            . $this->renderingDispatcher
            . "->renderControlErrors(\$this->global->formsStack, $ctrlExpr)",
            $name
        );
    }

    /**
     * <form n:name>, <input n:name>, <select n:name>, <textarea n:name>, <label n:name> and <button n:name>
     */
    public function macroNameAttr(MacroNode $node, PhpWriter $writer)
    {
        $tagName = strtolower($node->htmlNode->name);

        //all other nodes MUST have rendered end tag
        $node->empty = $tagName === 'input';

        // clear attributes that were overriden in HTML tag
        $attrs = array_fill_keys(array_keys($node->htmlNode->attrs), NULL);

        if ($tagName === 'form') {
            return $this->_macroFormBegin($node, $writer, $attrs);
        } elseif ($tagName === 'label') {
            return $this->_macroLabel($node, $writer, $attrs);
        } elseif (in_array($tagName, static::$supportedNameTags, TRUE)) {
            return $this->_macroInput($node, $writer, $attrs);
        } else {
            throw new CompileException("Unsupported tag <$tagName n:name>, did you mean one of "
                . implode(', ', static::$supportedNameTags) . '?');
        }
    }

    public function macroNameEnd(MacroNode $node, PhpWriter $writer)
    {
        $tagName = strtolower($node->htmlNode->name);
        if ($tagName === 'form') {
            $node->innerContent .= '<?php ' . $this->macroFormEnd($node, $writer, FALSE) . ' ?>';
        } elseif ($tagName === 'label') {
            if ($node->htmlNode->empty) {
                // inner content of rendered label without wrapping
                $node->innerContent = '<?php echo $_label->getHtml(); ?>';
            }
        } elseif ($tagName === 'button') {
            if ($node->htmlNode->empty) {
                // because input type button has its caption stored in value attribute instead of node content
                $node->innerContent = '<?php echo $_control->caption; ?>';
            }
        } else {
            if (!$node->htmlNode->empty) {
                throw new CompileException("Element <$tagName n:name=...> must not have any content, use empty variant <$tagName n:name=... />");
            }
            $node->innerContent = '<?php echo $_input->getHtml() ?>';
        }
    }

    /**
     * Common method to generate code extracting single form component (like $form[%node.word])
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string
     * @throws CompileException
     */
    protected function renderFormComponent(MacroNode $node, PhpWriter $writer)
    {
        if ($node->modifiers) {
            throw new CompileException('Modifiers are not allowed in ' . $node->getNotation());
        }
        $words = $node->tokenizer->fetchWords();
        if (!$words) {
            throw new CompileException('Missing name in ' . $node->getNotation());
        }
        $node->replaced = TRUE;
        $name = array_shift($words);
        return $writer->write($name[0] === '$' ?
            'is_object(%0.word) ? %0.word : end($this->global->formsStack)[%0.word]' :
            'end($this->global->formsStack)[%0.word]',
            $name
        );
    }

    private function ln(MacroNode $node)
    {
        return "/* line $node->startLine */\n";
    }

    private function writeAttrsFromMacroOrTag(PhpWriter $writer, array $attrs = NULL)
    {
        return $writer->write($attrs === NULL ? '%node.array' : '%0.var', $attrs);
    }

    /**
     * @param PhpWriter $writer
     * @param $name
     * @return string
     */
    protected function writeControlReturningExpression(PhpWriter $writer, $name)
    {
        return $writer->write(
            ($name[0] === '$'
                ? 'is_object(%0.word) ? %0.word : '
                : '')
            . 'end($this->global->formsStack)[%0.word]',
            $name);
    }

}
