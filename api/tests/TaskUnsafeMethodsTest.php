<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Task;
use App\Entity\Tasklist;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class TaskUnsafeMethodsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use DhtdlTestHelperTrait;

    public function testCreate(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Tasklist::class, ['title' => 'Vacation']);

        $response = $client->request('POST', '/tasks', [
            'json' => [
                'tasklist' => $iri,
                'title' => 'Barbecue on sunday',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Task',
            'title' => 'Barbecue on sunday',
        ]);

        $this->assertTaskWithTasklist($response->toArray());
    }

    /* public function testCreateWithEmptyFields(): void */
    /* { */
    /*     $client = static::createClient(); */
    /*     $response = $client->request('POST', '/tasks', ['json' => []]); */

    /*     $this->assertResponseStatusCodeSame(400); */
    /*     $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8'); */

    /*     $this->assertJsonContains([ */
    /*         '@context' => '/contexts/ConstraintViolationList', */
    /*         '@type' => 'ConstraintViolationList', */
    /*         'hydra:title' => 'An error occurred', */
    /*         'hydra:description' => <<<DESCRIPTION */
/* title: This value should not be blank. */
/* tasklist: This value should not be null. */
/* DESCRIPTION, */
    /*         'violations' => [ */
    /*             [ */
    /*                 'propertyPath' => 'title', */
    /*                 'message' => 'This value should not be blank.', */
    /*             ], */
    /*             [ */
    /*                 'propertyPath' => 'tasklist', */
    /*                 'message' => 'This value should not be null.', */
    /*             ], */
    /*         ], */
    /*     ]); */
    /* } */

    public function testCreateWithStatusDoneEqualsTrue(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Tasklist::class, ['title' => 'Vacation']);

        $response = $client->request('POST', '/tasks', [
            'json' => [
                'tasklist' => $iri,
                'title' => 'Update my profile',
                'done' => true,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            'done' => false,
        ]);
    }

    public function testPatchToDone(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Task::class, ['title' => 'Relax in Fernando de Noronha']);

        $doctrine = static::$container->get('doctrine');
        $repository = $doctrine->getRepository(Task::class);
        $entity = $repository->findOneBy(['title' => 'Relax in Fernando de Noronha']);
        $this->assertFalse($entity->getDone());

        $response = $client->request('PATCH', $iri, [
            'headers' => [
                'content-type' => 'application/merge-patch+json',
            ],
            'body' => \json_encode([
                'done' => true,
            ]),
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $doctrine->getManager()->refresh($entity);
        $this->assertTrue($entity->getDone());

        $this->assertJsonContains([
            '@context' => '/contexts/Task',
            'title' => 'Relax in Fernando de Noronha',
        ]);

        $this->assertTaskWithTasklist($response->toArray());
    }

    public function testDeleteTask(): void
    {
        $client = static::createClient();
        $iri = $this->findIriBy(Task::class, ['title' => 'Relax in Fernando de Noronha']);

        $response = $client->request('DELETE', $iri);
        $this->assertResponseStatusCodeSame(204);

        $doctrine = static::$container->get('doctrine');
        $repository = $doctrine->getRepository(Task::class);
        $this->assertNull($repository->findOneBy(['title' => 'Relax in Fernando de Noronha']));
    }
}
