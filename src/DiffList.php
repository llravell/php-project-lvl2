<?php

namespace Hexlet\Code\DiffList;

const DIFF_TYPE_ADD = 'add';
const DIFF_TYPE_REMOVE = 'remove';
const DIFF_TYPE_NO_CHANGES = 'no-changes';

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
