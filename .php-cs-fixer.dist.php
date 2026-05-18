<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'linebreak_after_opening_tag' => false,
        'blank_line_after_opening_tag' => false,
        'declare_parentheses' => true,
        'declare_equal_normalize' => [
            'space' => 'none',
        ],
        'global_namespace_import' => [
            'import_classes' => false,
        ],
    ])
    ->setFinder($finder)
;
