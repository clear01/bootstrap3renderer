<?php

namespace Clear01\BootstrapForm\Latte;

use Clear01\BootstrapForm\Nodes\ContainerNode;
use Clear01\BootstrapForm\Nodes\FormBodyNode;
use Clear01\BootstrapForm\Nodes\FormNode;
use Clear01\BootstrapForm\Nodes\InputNode;
use Clear01\BootstrapForm\Nodes\LabelNode;
use Clear01\BootstrapForm\Nodes\PairNode;
use Latte\CompileException;
use Latte\Extension;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;
use function strtolower;

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
class FormExtension extends Extension
{
	private $renderingDispatcher = '$this->global->formRenderingDispatcher';
	private static $supportedNameTags = ['select', 'input', 'button', 'textarea', 'form', 'label'];

	public function __construct(private readonly FormRenderingDispatcher $formRenderingDispatcher)
	{

	}

	public function getTags(): array
	{
		return [
			'pair' => PairNode::create(...),
//			'group' => $this->macroGroup(...),
			'container' => ContainerNode::create(...),
			'form' => FormNode::create(...),
//			'form.errors' => $this->macroFormErrors(...),
			'form.body' => FormBodyNode::create(...),
			'label' => LabelNode::create(...),
			'input' => InputNode::create(...),
//			'input.errors' => $this->macroInputErrors(...),
//			'name' => $this->macroName(...),

		];
	}

	public function getProviders(): array
	{

		return ['formRenderingDispatcher' => $this->formRenderingDispatcher];
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
