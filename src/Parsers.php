<?php

namespace Hexlet\Code\Parsers;

use Symfony\Component\Yaml\Yaml;

function parse(string $rawData, string $format)
{
    $parsersMap = [
        'json' => fn (string $data)
            => parseJson($data),
        'yml' => fn (string $data)
            => parseYaml($data),
        'yaml' => fn (string $data)
            => parseYaml($data),
    ];

    if (! array_key_exists($format, $parsersMap)) {
        throw new \Exception("Unknown format {$format}");
    }

    $parse = $parsersMap[$format];

    return $parse($rawData);
}

function parseJson(string $rawData)
{
    return json_decode($rawData);
}

function parseYaml(string $rawData)
{
    return Yaml::parse($rawData, Yaml::PARSE_OBJECT_FOR_MAP);
}
