<?php

declare(strict_types=1);

namespace App\Message;

final class CommentMessage
{
    private int $id;
    private string $reviewUrl;
    private array $context;

    public function __construct(int $id, string $reviewUrl, array $context)
    {
        $this->id = $id;
        $this->reviewUrl = $reviewUrl;
        $this->context = $context;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReviewUrl(): string
    {
        return $this->reviewUrl;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
