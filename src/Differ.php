<?php

namespace Hexlet\Code\Differ;

use function Hexlet\Code\Parsers\parse;

const DIFF_TYPE_ADD = 'add';
const DIFF_TYPE_REMOVE = 'remove';
const DIFF_TYPE_NO_CHANGES = 'no-changes';

const DIFF_PREFIX_BY_TYPE_MAP = [
    DIFF_TYPE_ADD => '+',
    DIFF_TYPE_REMOVE => '-',
    DIFF_TYPE_NO_CHANGES => ' ',
];

const SPACE_COUNT_IN_INDENTATION = 4;

function makeDiffItem(string $type, string $key, $value, $children = null): object
{
    return (object) ['type' => $type, 'key' => $key, 'value' => $value, 'children' => $children];
}

function makeDiffList(object $data1, object $data2): array
{
    $arrData1 = (array) $data1;
    $arrData2 = (array) $data2;

    $res = [];

    foreach ($arrData1 as $key => $value) {
        $value2 = $arrData2[$key] ?? null;

        if (!array_key_exists($key, $arrData2)) {
            $res[] = makeDiffItem(DIFF_TYPE_REMOVE, $key, $value);
        } elseif (is_object($value) && is_object($value2)) {
            $children = makeDiffList($value, $value2);
            $res[] = makeDiffItem(DIFF_TYPE_NO_CHANGES, $key, null, $children);
        } elseif ($value !== $value2) {
            $res[] = makeDiffItem(DIFF_TYPE_REMOVE, $key, $value);
            $res[] = makeDiffItem(DIFF_TYPE_ADD, $key, $value2);
        } else {
            $res[] = makeDiffItem(DIFF_TYPE_NO_CHANGES, $key, $value);
        }
    }

    foreach ($arrData2 as $key => $value) {
        if (!array_key_exists($key, $arrData1)) {
            $res[] = makeDiffItem(DIFF_TYPE_ADD, $key, $value);
        }
    }

    usort($res, fn ($a, $b) => strcmp($a->key, $b->key));

    return $res;
}

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $fileContent1 = file_get_contents($pathToFile1);
    $fileContent2 = file_get_contents($pathToFile2);

    $diffList = makeDiffList(
        parse($fileContent1, pathinfo($pathToFile1, PATHINFO_EXTENSION)),
        parse($fileContent2, pathinfo($pathToFile2, PATHINFO_EXTENSION))
    );

    $stringify = function ($value, $deep = 1) use (&$stringify) {
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
            $strValue = $stringify($childVal, $deep + 1);
            $parts[] = "{$indent}{$childKey}: {$strValue}";
        }

        $indent = $deep - 1 > 0
            ? str_repeat(' ', SPACE_COUNT_IN_INDENTATION * ($deep - 1))
            : '';

        return "{\n" . implode("\n", $parts) . "\n{$indent}}";
    };

    $buildAst = function ($data, $deep = 1) use (&$buildAst, $stringify) {
        if (is_array($data)) {
            return ['type' => 'diff_list', 'body' => array_map(fn ($child) => $buildAst($child, $deep + 1), $data)];
        }

        $prefix = DIFF_PREFIX_BY_TYPE_MAP[$data->type] ?? ' ';
        if ($data->children) {
            return ['type' => 'diff', 'prefix' => $prefix, 'name' => $data->key, 'body' => $buildAst($data->children, $deep)];
        }

        return ['type' => 'diff', 'prefix' => $prefix, 'name' => $data->key, 'body' => $stringify($data->value, $deep)];
    };

    $stringifyAst = function ($ast, $deep = 0) use (&$stringifyAst) {
        if ($ast['type'] === 'diff_list') {
            $indent = $deep > 0
                ? str_repeat(' ', SPACE_COUNT_IN_INDENTATION * $deep)
                : '';

            $parts = array_map(fn ($part) => $stringifyAst($part, $deep + 1), $ast['body']);
            $body = implode("\n", $parts);

            return "{\n{$body}\n{$indent}}";
        }

        $indentWithPrefix = str_pad("{$ast['prefix']} ", SPACE_COUNT_IN_INDENTATION * $deep, ' ', STR_PAD_LEFT);
        $body = is_array($ast['body']) ? $stringifyAst($ast['body'], $deep) : $ast['body'];
        $body = empty($body) ? $body : ' ' . $body;

        return "{$indentWithPrefix}{$ast['name']}:{$body}";
    };

    return $stringifyAst($buildAst($diffList));
}
