<?php 

// Source utile : https://medium.com/@doobie-droid/improve-your-php-code-quality-how-to-set-up-php-cs-fixer-c60cd0f82623

$finder = PhpCsFixer\Finder::create()
->in(__DIR__)
->exclude([
    'bootstrap',    // Framework files
    'storage',      // Application storage
    'vendor',       // Composer dependencies
    'mysql'         // Database files
]);

return (new PhpCsFixer\Config())
->setRiskyAllowed(false)
->setRules([
    '@PSR12' => true,
    'array_syntax' => ['syntax' => 'short'],
    'no_unused_imports' => true,
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'binary_operator_spaces' => ['default' => 'single_space'],
    'single_quote' => true,  // Uses single quotes where possible
    'trailing_comma_in_multiline' => ['elements' => ['arrays']],  
])
->setFinder($finder);