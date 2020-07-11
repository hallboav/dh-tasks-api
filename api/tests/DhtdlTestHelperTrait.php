<?php

namespace App\Tests;

trait DhtdlTestHelperTrait
{
    private function assertTasklistWithTasks(array $actual): void
    {
        $this->assertTaskList($actual);

        $this->assertArrayHasKey('tasks', $actual);
        $this->assertEquals('array', gettype($actual['tasks']));

        foreach ($actual['tasks'] as $task) {
            $this->assertTask($task);
        }
    }

    private function assertTasklist(array $actual): void
    {
        $this->assertArrayHasKey('@id', $actual);
        $this->assertRegExp('#^\/tasklists\/[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$#', $actual['@id']);

        $this->assertArrayHasKey('@type', $actual);
        $this->assertEquals('Tasklist', $actual['@type']);

        $this->assertArrayHasKey('id', $actual);
        $this->assertRegExp('#^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$#', $actual['id']);

        $this->assertArrayHasKey('createdAt', $actual);
        $this->assertDateTimeFormat($actual['createdAt'], \DateTime::ISO8601);

        $this->assertArrayHasKey('updatedAt', $actual);
        $this->assertDateTimeFormat($actual['updatedAt'], \DateTime::ISO8601);

        $this->assertArrayHasKey('title', $actual);
        $this->assertEquals('string', gettype($actual['title']));
    }

    private function assertTaskWithTasklist(array $actual): void
    {
        $this->assertTask($actual);

        $this->assertArrayHasKey('tasklist', $actual);
        $this->assertEquals('array', gettype($actual['tasklist']));

        $this->assertTasklist($actual['tasklist']);
    }

    private function assertTask(array $actual): void
    {
        $this->assertArrayHasKey('@id', $actual);
        $this->assertRegExp('#^\/tasks\/[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$#', $actual['@id']);

        $this->assertArrayHasKey('@type', $actual);
        $this->assertEquals('Task', $actual['@type']);

        $this->assertArrayHasKey('id', $actual);
        $this->assertRegExp('#^[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}$#', $actual['id']);

        $this->assertArrayHasKey('createdAt', $actual);
        $this->assertDateTimeFormat($actual['createdAt'], \DateTime::ISO8601);

        $this->assertArrayHasKey('updatedAt', $actual);
        $this->assertDateTimeFormat($actual['updatedAt'], \DateTime::ISO8601);

        $this->assertArrayHasKey('done', $actual);
        $this->assertEquals('boolean', gettype($actual['done']));

        $this->assertArrayHasKey('title', $actual);
        $this->assertEquals('string', gettype($actual['title']));

        if (isset($actual['details'])) {
            $this->assertEquals('string', gettype($actual['details']));
        }
    }

    private function assertDateTimeFormat(string $actual, string $format): void
    {
        $datetime = \DateTime::createFromFormat($format, $actual);
        $this->assertNotEquals($datetime, false, sprintf('Failed asserting that %s can be used to create a DateTime instance from format %s', $actual, $format));

        $expected = $actual;
        $this->assertEquals($expected, $datetime->format($format));
    }
}
