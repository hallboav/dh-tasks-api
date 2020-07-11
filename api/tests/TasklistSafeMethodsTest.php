<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Tasklist;

class TasklistSafeMethodsTest extends ApiTestCase
{
    use DhtdlTestHelperTrait;

    public function testGetCollection(): void
    {
        $client = static::createClient();
        $response = $client->request('GET', '/tasklists');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Tasklist',
            '@id' => '/tasklists',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
        ]);

        $this->assertCount(3, $response->toArray()['hydra:member']);

        foreach ($response->toArray()['hydra:member'] as $actual) {
            $this->assertTasklistWithTasks($actual);
        }
    }

    public function testGetItem(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Tasklist::class, ['title' => 'Vacation']);
        $response = $client->request('GET', $iri);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Tasklist',
            'title' => 'Vacation',
        ]);

        $this->assertTasklistWithTasks($response->toArray());
    }
}
