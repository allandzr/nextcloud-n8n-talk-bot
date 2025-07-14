<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Listener;

use OCA\N8nTalkBot\Bot\BotManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Talk\Events\ChatMessageSentEvent;
use Psr\Log\LoggerInterface;

/**
 * @template-implements IEventListener<ChatMessageSentEvent>
 */
class TalkChatMessageListener implements IEventListener {
	public function __construct(
		private BotManager $botManager,
		private LoggerInterface $logger
	) {}

	public function handle(Event $event): void {
		if (!($event instanceof ChatMessageSentEvent)) {
			return;
		}

		if (!$this->botManager->isInitialized()) {
			$this->logger->debug('Bot not initialized, skipping message');
			return;
		}

		$comment = $event->getComment();
		$room = $event->getRoom();

		// Extract message data
		$messageData = [
			'id' => $comment->getId(),
			'message' => $comment->getMessage(),
			'token' => $room->getToken(),
			'actorType' => $comment->getActorType(),
			'actorId' => $comment->getActorId(),
			'actorDisplayName' => $comment->getActorDisplayName(),
			'timestamp' => $comment->getCreationDateTime()->getTimestamp(),
			'messageType' => $comment->getMessageType(),
		];

		$this->logger->debug('Received chat message event', [
			'message_id' => $messageData['id'],
			'token' => $messageData['token'],
			'actor' => $messageData['actorId'],
			'message_preview' => substr($messageData['message'], 0, 50)
		]);

		// Skip system messages
		if ($comment->getMessageType() !== 'comment') {
			$this->logger->debug('Skipping non-comment message', [
				'message_type' => $comment->getMessageType()
			]);
			return;
		}

		// Skip empty messages
		if (empty($messageData['message'])) {
			return;
		}

		try {
			$this->botManager->handleMessage($messageData);
		} catch (\Exception $e) {
			$this->logger->error('Error handling chat message', [
				'exception' => $e,
				'message_data' => $messageData
			]);
		}
	}
} 