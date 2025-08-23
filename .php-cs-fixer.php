<?php
declare(strict_types=1);
use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Fixer\Internal\ConfigurableFixerTemplateFixer;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

return (new Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                       => true,
        'declare_strict_types'         => true,
        'strict_comparison'            => true,
        'strict_param'                 => true,
        'blank_line_after_opening_tag' => true,
        'no_extra_blank_lines'         => true,
        'class_attributes_separation'  => true,
        'array_syntax'                 => ['syntax' => 'short'],
        'binary_operator_spaces'       => ['default' => 'align_single_space']
    ])
    ->setFinder(
        Finder::create()
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/tests')
            ->ignoreDotFiles(true)
            ->ignoreVCSIgnored(true)
            ->in(__DIR__)
            ->append([__DIR__.'/php-cs-fixer'])
    );
