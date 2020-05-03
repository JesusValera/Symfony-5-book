<?php

declare(strict_types=1);

namespace App\Message;

final class CommentMessage
{
    private int $id;
    private string $url;
    private array $context;

    public function __construct(int $id, string $url, array $context)
    {
        $this->id = $id;
        $this->url = $url;
        $this->context = $context;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
