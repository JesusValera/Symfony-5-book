<?php

declare(strict_types=1);

namespace App\Message;

final class CommentMessage
{
    private int $id;
    private array $context;

    public function __construct(int $id, array $context)
    {
        $this->id = $id;
        $this->context = $context;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
