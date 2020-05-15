<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ConferenceControllerTest extends WebTestCase
{
    /** @test */
    public function index(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Give your feedback');
    }

    /** @test */
    public function commentSubmission(): void
    {
        $client = static::createClient();
        $client->request('GET', '/en/conference/amsterdam-2019');
        $client->submitForm('Submit', [
            'comment_form[author]' => 'Fabien',
            'comment_form[text]' => 'Some feedback from an automated functional test',
            'comment_form[email]' => $email = 'me@automat.ed',
            'comment_form[photo]' => dirname(__DIR__, 2) . '/public/images/under-construction.gif',
        ]);
        self::assertResponseRedirects();

        // Simulate comment validation.
        /** @var Comment $comment */
        $comment = self::$container->get(CommentRepository::class)->findOneByEmail($email);
        $comment->setState('published');
        self::$container->get(EntityManagerInterface::class)->flush();

        $client->followRedirect();
        self::assertSelectorExists('div:contains("There are 2 comments.")');
    }

    /** @test */
    public function conferencePage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/');

        self::assertCount(2, $crawler->filter('h4'));

        $client->clickLink('View');

        self::assertPageTitleContains('Amsterdam');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h2', 'Amsterdam 2019');
        self::assertSelectorExists('div:contains("There is one comments")');
    }
}
