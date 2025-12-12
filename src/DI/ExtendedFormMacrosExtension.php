<?php declare(strict_types = 1);

namespace Clear01\BootstrapForm\DI;

use Clear01\BootstrapForm\Latte\FormMacros;
use Clear01\BootstrapForm\Latte\FormRenderingDispatcher;
use Clear01\BootstrapForm\Latte\FormExtension;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;

class ExtendedFormMacrosExtension extends CompilerExtension
{

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

//        $formMacros = FormMacros::class;
        $builder->addDefinition($this->prefix('formRenderingDispatcher'))->setType(FormRenderingDispatcher::class);
//        $builder->getDefinition('latte.latteFactory')->getResultDefinition()
//            ->addSetup("?->onCompile[] = function() use (?) { $formMacros::install(?->getCompiler()); }",
//                ['@self', '@self', '@self',])
//            ->addSetup("?->addProvider('formRenderingDispatcher', ?)", [
//                '@self',
//                $this->prefix('@formRenderingDispatcher'),
//            ]);
	//	$extension = new Statement(FormExtension::class);
		//$extension = new Statement(FormExtension::class);
		$extension = $builder->addDefinition($this->prefix('formExtension'))->setType(FormExtension::class);

		$builder->getDefinition('latte.latteFactory')
			->getResultDefinition()
			->addSetup('addExtension', [$extension]);
	}
}
