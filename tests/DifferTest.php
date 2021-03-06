<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use function Hexlet\Code\Differ\genDiff;

class DifferTest extends TestCase
{
    private function getFixtureFullPath(string $fixtureName)
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public function testGenDiffJson(): void
    {
        $result = genDiff(
            $this->getFixtureFullPath('file1.json'),
            $this->getFixtureFullPath('file2.json')
        );

        $this->assertStringEqualsFile($this->getFixtureFullPath('expected'), $result);
    }

    public function testGenDiffYaml(): void
    {
        $result = genDiff(
            $this->getFixtureFullPath('file1.yaml'),
            $this->getFixtureFullPath('file2.yml')
        );

        $this->assertStringEqualsFile($this->getFixtureFullPath('expected'), $result);
    }
}
