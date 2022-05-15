<?php

namespace Hexlet\Code\Tests;

use PHPUnit\Framework\TestCase;
use function Hexlet\Code\Differ\genDiff;

class UserTest extends TestCase
{
    private function getFixtureFullPath(string $fixtureName)
    {
        $parts = [__DIR__, 'fixtures', $fixtureName];
        return realpath(implode('/', $parts));
    }

    public function testFlatDiff(): void
    {
        $result = genDiff(
            $this->getFixtureFullPath('flat/file1.json'),
            $this->getFixtureFullPath('flat/file2.json')
        );

        $this->assertStringEqualsFile($this->getFixtureFullPath('flat/expected'), $result);
    }
}
