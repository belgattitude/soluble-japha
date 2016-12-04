<?php

$header = <<<'EOF'

(c) Sébastien Vanvelthem

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        'psr4' => true,
        //'header_comment' => array('header' => $header),
        'array_syntax' => ['syntax' => 'short'],

        //'strict_comparison' => true,
        //'strict_param' => true,

        /**
         * Extended code rules
         */

        // Adapted from @Symfony
        'binary_operator_spaces' => [
            'align_double_arrow' => false,
            'align_equals' => false,
        ],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_return' => true,
        'cast_spaces' => true,
        'class_definition' => ['singleLine' => true],
        'concat_space' => ['spacing' => 'one'], // Different from symfony (none)
        'declare_equal_normalize' => true,
        'function_typehint_space' => true,
        'hash_to_slash_comment' => true,
        'heredoc_to_nowdoc' => true,
        'include' => true,
        'lowercase_cast' => true,
        'method_separation' => true,
        'native_function_casing' => true,
        'new_with_braces' => true,
        'no_alias_functions' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_extra_consecutive_blank_lines' => [
            'curly_brace_block',
            'extra',
            'parenthesis_brace_block',
            'square_brace_block',
            'throw',
            'use',
        ],
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_spaces_around_offset' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unreachable_default_argument_value' => true,
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'normalize_index_brace' => true,
        'object_operator_without_whitespace' => true,
        'php_unit_fqcn_annotation' => true,
        'phpdoc_align' => true,
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,
        'pre_increment' => true,
        'return_type_declaration' => true,
        'self_accessor' => true,
        'short_scalar_cast' => true,
        'single_blank_line_before_namespace' => true,
        'single_class_element_per_statement' => true,
        'single_quote' => true,
        'space_after_semicolon' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'trailing_comma_in_multiline_array' => false, // Differs from Symfony (true)
        'trim_array_spaces' => true,
        'unary_operator_spaces' => true,
        'whitespace_after_comma_in_array' => true,

        // Taken from @Symfony:risky

        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'silenced_deprecation_error' => true,

    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(['src', 'test', 'bin'])
            ->filter(function (SplFileInfo $file) {
                if (strstr($file->getPath(), 'compatibility')) {
                    return false;
                }
            })
    )
;

