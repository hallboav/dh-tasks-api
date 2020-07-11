<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Task;

class TaskSafeMethodsTest extends ApiTestCase
{
    use DhtdlTestHelperTrait;

    public function testGetCollection(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Task',
            '@id' => '/tasks',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 4,
            'hydra:search' => [
                '@type' => 'hydra:IriTemplate',
                'hydra:template' => '/tasks{?done}',
                'hydra:variableRepresentation' => 'BasicRepresentation',
                'hydra:mapping' => [
                    [
                        '@type' => 'IriTemplateMapping',
                        'variable' => 'done',
                        'property' => 'done',
                        'required' => false
                    ],
                ],
            ],
        ]);

        $this->assertCount(4, $response->toArray()['hydra:member']);

        foreach ($response->toArray()['hydra:member'] as $actual) {
            $this->assertTaskWithTasklist($actual);
        }
    }

    public function testGetCollectionDone(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/tasks?done=true');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Task',
            '@id' => '/tasks',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:view' => [
                '@id' => '/tasks?done=true',
                '@type' => 'hydra:PartialCollectionView',
            ],
            'hydra:search' => [
                '@type' => 'hydra:IriTemplate',
                'hydra:template' => '/tasks{?done}',
                'hydra:variableRepresentation' => 'BasicRepresentation',
                'hydra:mapping' => [
                    [
                        '@type' => 'IriTemplateMapping',
                        'variable' => 'done',
                        'property' => 'done',
                        'required' => false
                    ],
                ],
            ],
            'hydra:member' => [
                ['title' => 'Buy a new television'],
            ],
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);

        foreach ($response->toArray()['hydra:member'] as $actual) {
            $this->assertTaskWithTasklist($actual);
        }
    }

    public function testGetCollectionToDo(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/tasks?done=false');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Task',
            '@id' => '/tasks',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
            'hydra:view' => [
                '@id' => '/tasks?done=false',
                '@type' => 'hydra:PartialCollectionView',
            ],
            'hydra:search' => [
                '@type' => 'hydra:IriTemplate',
                'hydra:template' => '/tasks{?done}',
                'hydra:variableRepresentation' => 'BasicRepresentation',
                'hydra:mapping' => [
                    [
                        '@type' => 'IriTemplateMapping',
                        'variable' => 'done',
                        'property' => 'done',
                        'required' => false
                    ],
                ],
            ],
        ]);

        $this->assertCount(3, $response->toArray()['hydra:member']);

        foreach ($response->toArray()['hydra:member'] as $actual) {
            $this->assertTaskWithTasklist($actual);
        }
    }

    public function testGetItem(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Task::class, ['title' => 'Drill wall to put curtains']);

        $response = $client->request('GET', $iri);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Task',
            'title' => 'Drill wall to put curtains',
        ]);

        $this->assertTaskWithTasklist($response->toArray());
    }
}
