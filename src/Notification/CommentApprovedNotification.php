<?php

declare(strict_types=1);

namespace App\Notification;

use App\Entity\Comment;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;

final class CommentApprovedNotification extends Notification implements EmailNotificationInterface
{
    private Comment $comment;
    private string $url;

    public function __construct(Comment $comment, string $url)
    {
        $this->comment = $comment;
        $this->url = $url;

        parent::__construct('Comment approved');
    }

    public function asEmailMessage(Recipient $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);
        $message->getMessage()
            ->htmlTemplate('emails/comment_approved_notification.html.twig')
            ->context([
                'comment' => $this->comment,
                'url' => $this->url,
            ]);

        return $message;
    }
}
