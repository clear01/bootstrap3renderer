<?php

namespace Clear01\BootstrapForm\Latte;

use Clear01\BootstrapForm\IExtendedFormRenderer;
use Clear01\BootstrapForm\PairAttributes;
use Latte\RuntimeException;
use Nette\Bridges\FormsLatte\Runtime;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\InvalidStateException;

/**
 * Bridge between extended form macros and IFormRenderer implementation created to simplify latte generated code.
 */
class FormRenderingDispatcher
{
    public function renderPair(array $formsStack, IControl $control, array $attrs)
    {
        $this->assertInForm($formsStack, 'pair');
        return $this->getExtendedRenderer($formsStack, 'pair')->renderPair($control, PairAttributes::fetch($attrs));
    }

    public function renderGroup(array $formsStack, ControlGroup $group)
    {
        $this->assertInForm($formsStack, 'group')->checkInsideTopLevelForm($formsStack, 'group');
        return $this->getExtendedRenderer($formsStack, 'group')->renderGroup($group);
    }

    public function renderContainer(array $formsStack, Container $container)
    {
        $this->assertInForm($formsStack, 'container');
        return $this->getExtendedRenderer($formsStack, 'container')->renderContainer($container);
    }

    public function renderBegin(Form $form, array $attrs, $withTags = TRUE)
    {
        $renderer = $form->getRenderer();
        if ($renderer instanceof IExtendedFormRenderer) {
            return $renderer->renderBegin($form, $attrs, $withTags);
        } else {
            /** @noinspection PhpInternalEntityUsedInspection */
            return Runtime::renderFormBegin($form, $attrs, $withTags);
        }
    }

    public function renderEnd(Form $form, $withTags = TRUE)
    {
        $renderer = $form->getRenderer();
        if ($renderer instanceof IExtendedFormRenderer) {
            return $renderer->renderEnd($withTags);
        } else {
            /** @noinspection PhpInternalEntityUsedInspection */
            return Runtime::renderFormEnd($form, $withTags);
        }
    }

    public function renderLabel(array $formsStack, IControl $control, array $attrs, $part = NULL)
    {
        $renderer = reset($formsStack)->getRenderer();
        if ($renderer instanceof IExtendedFormRenderer) {
            return $renderer->renderLabel($control, $attrs, $part);
        } else {
            if ($part !== NULL && method_exists($control, 'getLabelPart')) {
                return $control->getLabelPart($part);
            } elseif ($part === NULL && method_exists($control, 'getLabel')) {
                return $control->getLabel();
            } else {
                throw new InvalidStateException('No getLabel[Part] method available to render ' . get_class($control));
            }
        }
    }

    public function renderControl(array $formsStack, IControl $control, array $attrs, $part = NULL)
    {
        $renderer = reset($formsStack)->getRenderer();
        if ($renderer instanceof IExtendedFormRenderer) {
            return $renderer->renderControl($control, $attrs, $part);
        } else {
            if ($part !== NULL && method_exists($control, 'getControlPart')) {
                return $control->getControlPart($part);
            } elseif ($part === NULL && method_exists($control, 'getControl')) {
                return $control->getControl();
            } else {
                throw new InvalidStateException('No getControl[Part] method available to render '
                    . get_class($control));
            }
        }
    }

    public function renderGlobalErrors($formsStack, $own = TRUE)
    {
        $this->assertInForm($formsStack, 'form.errors');
        return $this->getExtendedRenderer($formsStack, 'form.errors')->renderGlobalErrors($own);
    }

    public function renderBody($formsStack)
    {
        $this->assertInForm($formsStack, 'form.body');
        return $this->getExtendedRenderer($formsStack, 'form.body')->renderBody();
    }

    public function renderControlErrors($formsStack, IControl $control)
    {
        $this->assertInForm($formsStack, 'input.errors');
        return $this->getExtendedRenderer($formsStack, 'input.errors')->renderControlErrors($control);
    }

    protected function checkInsideTopLevelForm($formsStack, $macro)
    {
        if (count($formsStack) > 1) {
            throw new RuntimeException(sprintf('Macro %s must not be used in nested form container', $macro));
        }
        return $this;
    }

    protected function assertInForm($formsStack, $macro)
    {
        if (count($formsStack) === 0) {
            throw new RuntimeException(sprintf('Cannot use %s macro outside form', $macro));
        }
        return $this;
    }

    /**
     * @param array $formsStack
     * @param string $macro
     * @return IExtendedFormRenderer
     * @throws RuntimeException
     */
    protected function getExtendedRenderer(array $formsStack, $macro)
    {
        $renderer = $this->getRenderer($formsStack);
        if (!$renderer instanceof IExtendedFormRenderer) {
            throw new RuntimeException(sprintf('%s does not support {%s} macro, please use %s as form renderer',
                get_class($renderer),
                $macro,
                IExtendedFormRenderer::class
            ));
        }
        return $renderer;
    }

    /**
     * @param array $formsStack
     * @return IExtendedFormRenderer
     * @throws RuntimeException
     */
    protected function getRenderer(array $formsStack)
    {
        return reset($formsStack)->getRenderer();
    }
}
