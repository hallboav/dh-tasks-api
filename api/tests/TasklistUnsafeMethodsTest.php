<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Tasklist;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class TasklistUnsafeMethodsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use DhtdlTestHelperTrait;

    public function testCreate(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', '/tasklists', [
            'json' => [
                'title' => 'Write docker-compose.yaml',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Tasklist',
            'title' => 'Write docker-compose.yaml',
        ]);

        $this->assertTasklistWithTasks($response->toArray());
    }

    public function testDelete(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Tasklist::class, ['title' => 'Vacation']);
        $response = $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);

        // RefreshDatabaseTrait
        $doctrine = static::$container->get('doctrine');
        $repository = $doctrine->getRepository(Tasklist::class);
        $this->assertNull($repository->findOneBy(['title' => 'Vacation']));
    }

    public function testPatch(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Tasklist::class, ['title' => 'Vacation']);

        $response = $client->request('PATCH', $iri, [
            'headers' => [
                'content-type' => 'application/merge-patch+json',
            ],
            'body' => \json_encode([
                'title' => 'New name',
            ]),
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Tasklist',
            '@id' => $iri,
            'id' => substr($iri, -36),
            'title' => 'New name',
        ]);

        $this->assertTasklistWithTasks($response->toArray());
    }

    public function testCreateWithEmptyFields(): void
    {
        $client = static::createClient();
        $response = $client->request('POST', '/tasklists', ['json' => []]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonEquals([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => <<<HYDRA_DESCRIPTION
title: This value should not be blank.
HYDRA_DESCRIPTION,
            'violations' => [
                [
                    'propertyPath' => 'title',
                    'message' => 'This value should not be blank.',
                ],
            ],
        ]);
    }
}
