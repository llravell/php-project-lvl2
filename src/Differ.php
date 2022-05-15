<?php

namespace Hexlet\Code\Differ;

const DIFF_TYPE_ADD = 'add';
const DIFF_TYPE_REMOVE = 'remove';
const DIFF_TYPE_NO_CHANGES = 'no-changes';

const DIFF_PREFIX_BY_TYPE_MAP = [
    DIFF_TYPE_ADD => '+',
    DIFF_TYPE_REMOVE => '-',
    DIFF_TYPE_NO_CHANGES => ' ',
];

const SPACE_COUNT_IN_INDENTATION = 2;

function makeDiffItem(string $type, string $key, $value): array
{
    return ['type' => $type, 'key' => $key, 'value' => $value];
}

function parseData(string $str): array
{
    return json_decode($str, true);
}

function makeDiffList(array $data1, array $data2): array
{
    $res = [];

    foreach ($data1 as $key => $value) {
        if (!array_key_exists($key, $data2)) {
            $res[] = makeDiffItem(DIFF_TYPE_REMOVE, $key, $value);
        } elseif ($value !== $data2[$key]) {
            $res[] = makeDiffItem(DIFF_TYPE_REMOVE, $key, $value);
            $res[] = makeDiffItem(DIFF_TYPE_ADD, $key, $data2[$key]);
        } else {
            $res[] = makeDiffItem(DIFF_TYPE_NO_CHANGES, $key, $value);
        }
    }

    foreach ($data2 as $key => $value) {
        if (!array_key_exists($key, $data1)) {
            $res[] = makeDiffItem(DIFF_TYPE_ADD, $key, $value);
        }
    }

    return $res;
}

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $fileContent1 = file_get_contents($pathToFile1);
    $fileContent2 = file_get_contents($pathToFile2);

    $diff = makeDiffList(parseData($fileContent1), parseData($fileContent2));
    $result = [];

    foreach ($diff as $diffItem) {
        $indent = str_repeat(' ', SPACE_COUNT_IN_INDENTATION);
        $prefix = $indent . DIFF_PREFIX_BY_TYPE_MAP[$diffItem['type']];
        $displayedValue = var_export($diffItem['value'], true);

        $result[] = "{$prefix} {$diffItem['key']}: {$displayedValue}";
    }

    return "{\n" . implode("\n", $result) . "\n}";
}
