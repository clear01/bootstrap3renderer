<?php

namespace Clear01\BootstrapForm;

use Nette\Forms\Container;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use Nette\Forms\IControl;
use Nette\Forms\IFormRenderer;
use Nette\Utils\Html;

interface IExtendedFormRenderer extends IFormRenderer
{
    /**
     * @param IControl $control
     * @param PairAttributes $attrs
     * @return Html
     */
    public function renderPair(IControl $control, PairAttributes $attrs = NULL);

    /**
     * @param ControlGroup $group
     * @return Html
     */
    public function renderGroup(ControlGroup $group);

    /**
     * @param Container $container
     * @return Html
     */
    public function renderContainer(Container $container);

    /**
     * @param bool $ownOnly - render only global errors (false: include all controls' errors)
     * @return Html
     */
    public function renderGlobalErrors($ownOnly = TRUE);

    /**
     * Render all form pairs, groups and containers
     *
     * @return Html
     */
    public function renderBody();

    /**
     * @param IControl $control
     * @return Html
     */
    public function renderControlErrors(IControl $control);

    /**
     * @param IControl $control
     * @param array $attrs
     * @param string $part
     * @return Html
     */
    public function renderLabel(IControl $control, array $attrs = [], $part = NULL);

    /**
     * @param IControl $control
     * @param array $attrs
     * @param string $part
     * @return Html
     */
    public function renderControl(IControl $control, array $attrs = [], $part = NULL);

    /**
     * @param Form $form
     * @param array $attrs
     * @param bool $withTags
     * @return Html
     */
    public function renderBegin(Form $form, array $attrs, $withTags = TRUE);

    /**
     * @param bool $withTags FALSE = skip </form> end tag
     * @return Html
     */
    public function renderEnd($withTags = TRUE);
}
