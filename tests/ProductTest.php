<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const API_TOKEN = 'ae11e4d8c8d033aa8fe87c68f50021f1f645966f8eb2c2fe9223a068096934a5b0016d9637e86915c72dc0189cbb60eb70063e6d4c05c3cb3ef942090a';

    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $client;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /** setUpBeforeClass method adds to test class before starting unittests one times
     * (More useful for connect to database or create data for each tests (test user))
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    /** setUp method adds to each test before */
    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('gorutin@example.com');
        $user->setPassword('goru');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $api = new ApiToken();
        $api->setToken(self::API_TOKEN);
        $api->setUser($user);
        $this->entityManager->persist($api);
        $this->entityManager->flush();
    }

    /** Method allowed to initialize after all tests.
     * tearDown method will quite useful if one of tests broke
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetCollection(): void
    {
        $response = $this->client->request('GET', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            "@context" => "/api/contexts/Product",
            "@id" => "/api/products",
            "@type" => "hydra:Collection",
            "hydra:totalItems" => 90,
            "hydra:view" => [
                "@id" => "/api/products?page=1",
                "@type" => "hydra:PartialCollectionView",
                "hydra:first" => "/api/products?page=1",
                "hydra:last" => "/api/products?page=9",
                "hydra:next" => "/api/products?page=2"
            ]
        ]);
        $this->assertCount(7, $response->toArray('hydra:members'));
    }

    public function testPagination(): void
    {
        $this->client->request('GET', '/api/products?page=2', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            "@context" => "/api/contexts/Product",
            "@id" => "/api/products",
            "@type" => "hydra:Collection",
            "hydra:totalItems" => 90,
            "hydra:view" => [
                "@id" => "/api/products?page=2",
                "@type" => "hydra:PartialCollectionView",
                "hydra:first" => "/api/products?page=1",
                "hydra:last" => "/api/products?page=9",
                "hydra:previous" => "/api/products?page=1",
                "hydra:next" => "/api/products?page=3"
            ]
        ]);
    }

    public function testCreateProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => ['Content-Type' => 'application/ld+json', 'x-api-token' => self::API_TOKEN],
            'json' => [
                "name" => "A Test Product",
                "description" => "testcase",
                "issueDate" => "1985-07-31",
                "manufacturer" => "/api/manufacturers/1"
            ]
        ]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            "name" => "A Test Product",
            "description" => "testcase",
            "issueDate" => "1985-07-31T00:00:00+00:00",
        ]);
    }

    public function testUpdateProduct(): void
    {
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['Content-Type' => 'application/ld+json', 'x-api-token' => self::API_TOKEN],
            'json' => [
                "description" => "testcase",
                "manufacturer" => "/api/manufacturers/1"

            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );
        $this->assertJsonContains([
            "@id" => "/api/products/1",
            "description" => "testcase",
        ]);
    }

    public function testfailCreateProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => ['Content-Type' => 'application/ld+json', 'x-api-token' => self::API_TOKEN],
            'json' => [
                "name" => "A Test Product",
                "description" => "testcase",
                "issueDate" => "1985-07-31",
            ]
        ]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function testInvalidToken(): void
    {
        $this->client->request('PUT', '/api/products/1', [
            'headers' => ['Content-Type' => 'application/ld+json', 'x-api-token' => 'fake-token'],
            'json' => [
                "description" => "testcase",
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertJsonContains([
            'message' => 'Invalid credentials.',
        ]);
    }
}
