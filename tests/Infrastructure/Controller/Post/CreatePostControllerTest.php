<?php

namespace App\Tests\Infrastructure\Controller\Post;

use App\Infrastructure\Persistence\Doctrine\Post\Post;
use App\Infrastructure\Persistence\Doctrine\Post\PostDoctrineRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreatePostControllerTest extends WebTestCase
{

    private KernelBrowser $client;
    private PostDoctrineRepository $repository;
    private string $path = '/posts/create/';

    public function testPage(): void
    {
        $this->client->request('GET', $this->path);
        $this->client->followRedirect();

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Symfony Hexagonal Architecture - Create Post');
    }

    public function testCreate(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->client->request('GET', $this->path);
        $this->client->followRedirect();

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'post[title]' => 'Testing title',
            'post[content]' => 'Testing content',
            'post[publishedAt][date]' => '2022-06-17',
            'post[publishedAt][time]' => '23:54',
        ]);
        $this->client->followRedirect();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Post::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }
}