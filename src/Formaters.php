<?php

namespace Hexlet\Code\Formaters;

use const Hexlet\Code\DiffList\DIFF_TYPE_ADD;
use const Hexlet\Code\DiffList\DIFF_TYPE_REMOVE;
use const Hexlet\Code\DiffList\DIFF_TYPE_NO_CHANGES;

const DIFF_PREFIX_BY_TYPE_MAP = [
    DIFF_TYPE_ADD => '+',
    DIFF_TYPE_REMOVE => '-',
    DIFF_TYPE_NO_CHANGES => ' ',
];

const SPACE_COUNT_IN_INDENTATION = 4;

function format($data, string $format)
{
    $formatersMap = [
        'stylish' => fn ($data)
            => stylishFormat($data)
    ];

    if (! array_key_exists($format, $formatersMap)) {
        throw new \Exception("Unknown output format {$format}");
    }

    $format = $formatersMap[$format];

    return $format($data);
}

function stringifyValue($value, $deep = 1) {
    if (is_string($value)) {
        return $value;
    }

    if (!is_object($value)) {
        return mb_strtolower(var_export($value, true));
    }

    $parts = [];
    $children = (array) $value;

    foreach ($children as $childKey => $childVal) {
        $indent = str_repeat(' ', SPACE_COUNT_IN_INDENTATION * $deep);
        $strValue = stringifyValue($childVal, $deep + 1);
        $parts[] = "{$indent}{$childKey}: {$strValue}";
    }

    $indent = $deep - 1 > 0
        ? str_repeat(' ', SPACE_COUNT_IN_INDENTATION * ($deep - 1))
        : '';

    return "{\n" . implode("\n", $parts) . "\n{$indent}}";
};

function stylishFormat($diffList)
{
    $iter = function ($data, $deep = 0) use (&$iter) {
        if (is_array($data)) {
            $indent = $deep > 0
                ? str_repeat(' ', SPACE_COUNT_IN_INDENTATION * $deep)
                : '';

            $parts = array_map(fn ($part) => $iter($part, $deep + 1), $data);
            $body = implode("\n", $parts);

            return "{\n{$body}\n{$indent}}";
        }

        $prefix = DIFF_PREFIX_BY_TYPE_MAP[$data->type] ?? ' ';
        $indentWithPrefix = str_pad("{$prefix} ", SPACE_COUNT_IN_INDENTATION * $deep, ' ', STR_PAD_LEFT);
        $body = $data->children ? $iter($data->children, $deep) : stringifyValue($data->value, $deep + 1);
        $body = empty($body) ? $body : ' ' . $body;

        return "{$indentWithPrefix}{$data->key}:{$body}";
    };

    return $iter($diffList);
}
