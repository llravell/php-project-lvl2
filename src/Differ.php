<?php

namespace Hexlet\Code\Differ;

use function Hexlet\Code\Parsers\parse;
use function Hexlet\Code\DiffList\makeDiffList;
use function Hexlet\Code\Formaters\format;

function genDiff(string $pathToFile1, string $pathToFile2, string $format = 'stylish'): string
{
    $fileContent1 = file_get_contents($pathToFile1);
    $fileContent2 = file_get_contents($pathToFile2);

    $diffList = makeDiffList(
        parse($fileContent1, pathinfo($pathToFile1, PATHINFO_EXTENSION)),
        parse($fileContent2, pathinfo($pathToFile2, PATHINFO_EXTENSION))
    );

    return format($diffList, $format);
}
