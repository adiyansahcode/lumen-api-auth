<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);
;

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@PSR12' => true,

        // Array Notation
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_whitespace_before_comma_in_array' => true,
        'normalize_index_brace' => true,

        // Basic
        'braces' => [
            'allow_single_line_anonymous_class_with_empty_body' => true,
            'allow_single_line_closure' => false,
            'position_after_anonymous_constructs' => 'same',
            'position_after_control_structures' => 'same',
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'encoding' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,

        // Cast Notation
        'cast_spaces' => [
            'space' => 'single'
        ],
        'lowercase_cast' => true,
        'no_short_bool_cast' => true,
        'short_scalar_cast' => true,

        // Casing
        'constant_case' => [
            'case' => 'lower',
        ],
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,

        // Class Notation
        'class_definition' => [
            'multi_line_extends_each_single_line' => false,
            'single_line' => false,
            'single_item_single_line' => false,
            'space_before_parenthesis' => true
        ],
        'no_blank_lines_after_class_opening' => true,
        'ordered_class_elements' => [
            'order' => ['use_trait']
        ],
        'single_class_element_per_statement' => [
            'elements' => [
                'property'
            ]
        ],
        'single_trait_insert_per_statement' => true,
        'visibility_required' => [
            'elements' => [
                'const',
                'method',
                'property'
            ]
        ],

        // Comment
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'single_line_comment_style' => [
            'comment_types' => ['hash']
        ],

        // Control Structure
        'elseif' => true,
        'include' => true,
        'no_alternative_syntax' => true,
        'no_break_comment' => [
            'comment_text' => 'no break'
        ],
        'no_trailing_comma_in_list_call' => true,
        'no_unneeded_control_parentheses' => true,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,

        // Function Notation
        'function_declaration' => [
            'closure_function_spacing' => 'one'
        ],
        'function_typehint_space' => true,
        'method_argument_space' => [
            'after_heredoc' => false,
            'keep_multiple_spaces_after_comma' => false,
            'on_multiline' => 'ensure_fully_multiline'
        ],
        'no_spaces_after_function_name' => true,
        'return_type_declaration' => [
            'space_before' => 'none'
        ],

        // Import
        'fully_qualified_strict_types' => true,
        'no_leading_import_slash' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const'
            ],
            'sort_algorithm' => 'alpha'
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,

        // Language Construct
        'declare_equal_normalize' => [
            'space' => 'none'
        ],
        'explicit_indirect_variable' => true,

        // Namespace Notation
        'blank_line_after_namespace' => true,
        'no_leading_namespace_whitespace' => true,
        'single_blank_line_before_namespace' => true,

        // Operator
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'increment_style' => [
            'style' => 'post'
        ],
        'new_with_braces' => true,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'unary_operator_spaces' => true,

        // PHPDoc
        'align_multiline_comment' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,

        // PHP Tag
        'blank_line_after_opening_tag' => true,
        'full_opening_tag' => true,
        'linebreak_after_opening_tag' => true,
        'no_closing_tag' => true,

        // Return Notation
        'no_useless_return' => true,
        'simplified_null_return' => false,

        // Semicolon
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line'
        ],
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => true,

        // String Notation
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'single_quote' => [
            'strings_containing_single_quote_chars' => false
        ],

        // Whitespace
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'return',
            ],
        ],
        'compact_nullable_typehint' => true,
        'indentation_type' => true,
        'line_ending' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
                'use_trait',
            ]
        ],
        'no_spaces_around_offset' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setLineEnding("\n")
;
