<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\ImageOptimizer;
use App\Message\CommentMessage;
use App\Notification\CommentReviewNotification;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Workflow\WorkflowInterface;

final class CommentMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private SpamChecker $spamChecker;
    private CommentRepository $commentRepository;
    private MessageBusInterface $messageBus;
    private WorkflowInterface $workflow;
    private NotifierInterface $notifier;
    private ImageOptimizer $imageOptimizer;
    private string $photoDir;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        SpamChecker $spamChecker,
        CommentRepository $commentRepository,
        MessageBusInterface $messageBus,
        WorkflowInterface $commentStateMachine,
        NotifierInterface $notifier,
        ImageOptimizer $imageOptimizer,
        string $photoDir,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->spamChecker = $spamChecker;
        $this->commentRepository = $commentRepository;
        $this->messageBus = $messageBus;
        $this->workflow = $commentStateMachine;
        $this->notifier = $notifier;
        $this->imageOptimizer = $imageOptimizer;
        $this->photoDir = $photoDir;
        $this->logger = $logger;
    }

    public function __invoke(CommentMessage $message): void
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }

        if ($this->workflow->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            $transition = 'accept';
            if (2 === $score) {
                $transition = 'reject_spam';
            } elseif (1 === $score) {
                $transition = 'might_be_spam';
            }

            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();
            $this->messageBus->dispatch($message);
        } elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
            $notification = new CommentReviewNotification($comment, $message->getReviewUrl());
            $this->notifier->send($notification, ...$this->notifier->getAdminRecipients());
        } elseif ($this->workflow->can($comment, 'optimize')) {
            if ($comment->getPhotoFilename()) {
                $this->imageOptimizer->resize($this->photoDir . '/' . $comment->getPhotoFilename());
            }
            $this->workflow->apply($comment, 'optimize');
            $this->entityManager->flush();
        } elseif ($this->logger) {
            $this->logger->debug('Dropping comment message', [
                'comment' => $comment->getId(),
                'state' => $comment->getState()
            ]);
        }
    }
}
