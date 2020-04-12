<?php

declare(strict_types=1);

namespace App;

use App\Entity\Comment;
use RuntimeException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SpamChecker
{
    private HttpClientInterface $client;
    private string $endPoint;

    public function __construct(HttpClientInterface $client, string $akismetKey)
    {
        $this->client = $client;
        $this->endPoint = sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetKey);
    }

    /**
     * @return int Spam score: 0: not spam, 1: maybe spam, 2: blatant spam
     *
     * @throws RuntimeException if the call did not work
     */
    public function getSpamScore(Comment $comment, array $context): int
    {
        $response = $this->client->request('POST', $this->endPoint, [
            'body' => array_merge($context, [
                'blog' => 'https://127.0.0.1:8000/',
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor(),
                'comment_author_email' => $comment->getEmail(),
                'comment_content' => $comment->getText(),
                'comment_date_gmt' => (null !== $comment->getCreatedAt())
                    ? $comment->getCreatedAt()->format('c')
                    : '',
                'blog_lang' => 'en',
                'blog_charset' => 'UTF-8',
                'is_test' => true,
            ])
        ]);

        $headers = $response->getHeaders();
        if ('discard' === ($headers['x-akismet-pro-tip'][0] ?? '')) {
            return 2;
        }

        $content = $response->getContent();
        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new RuntimeException(
                sprintf('Unable to check for spam: %s (%s).', $content, $headers['x-akismet-debug-help'][0])
            );
        }

        return ('true' === $content) ? 1 : 0;
    }
}
