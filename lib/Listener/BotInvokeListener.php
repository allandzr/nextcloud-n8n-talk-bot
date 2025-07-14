<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Listener;

use OCA\N8nTalkBot\Bot\CommandHandler;
use OCA\N8nTalkBot\Service\BotConfigService;
use OCA\N8nTalkBot\Service\N8nIntegrationService;
use OCA\Talk\Events\BotInvokeEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use Psr\Log\LoggerInterface;

/**
 * @template-implements IEventListener<BotInvokeEvent>
 */
class BotInvokeListener implements IEventListener {

	public function __construct(
		private BotConfigService $configService,
		private CommandHandler $commandHandler,
		private N8nIntegrationService $n8nService,
		private LoggerInterface $logger,
	) {
	}

	public function handle(Event $event): void {
		if (!$event instanceof BotInvokeEvent) {
			return;
		}

		// Check if bot is enabled
		if (!$this->configService->isBotEnabled()) {
			return;
		}

		$message = $event->getMessage();
		
		// Handle different message types
		switch ($message['type']) {
			case 'Create':
			case 'Activity':
				$this->handleChatMessage($event, $message);
				break;
			case 'Like':
				$this->handleReaction($event, $message);
				break;
			case 'Join':
				$this->handleBotJoin($event, $message);
				break;
			case 'Leave':
				$this->handleBotLeave($event, $message);
				break;
		}
	}

	private function handleChatMessage(BotInvokeEvent $event, array $message): void {
		if (!isset($message['object']['content'])) {
			return;
		}

		$content = $message['object']['content'];
		
		// Check if content is JSON and decode it
		if (is_string($content) && str_starts_with($content, '{')) {
			$decoded = json_decode($content, true);
			if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
				$content = $decoded['message'];
			}
		}

		// Prepare message data for n8n
		$messageData = [
			'messageId' => $message['object']['id'] ?? uniqid('msg_'),
			'conversationToken' => $message['target']['id'] ?? 'unknown',
			'actorId' => $message['actor']['id'] ?? '',
			'actorDisplayName' => $message['actor']['name'] ?? '',
			'message' => $content,
			'messageType' => 'chat'
		];

		// Try to process with n8n first
		$n8nResponse = $this->n8nService->processMessage($messageData);
		
		if ($n8nResponse) {
			// Determine what response to send based on n8n response format
			$responseText = null;
			
			if (!empty($n8nResponse['response'])) {
				// Standard format: {"success": true, "response": "text", "shouldReply": true}
				$responseText = $n8nResponse['response'];
			} elseif (!empty($n8nResponse['message'])) {
				// Alternative format: {"message": "text"}
				$responseText = $n8nResponse['message'];
			} elseif (is_string($n8nResponse)) {
				// Simple string response
				$responseText = $n8nResponse;
			}
			
			if ($responseText && ($n8nResponse['shouldReply'] ?? true)) {
				$this->logger->debug('Sending n8n response to chat', [
					'response_text' => $responseText,
					'response_format' => array_keys($n8nResponse)
				]);
				
				$event->addAnswer($responseText);
				return;
			}
		}

		// Fall back to standard bot processing if n8n didn't respond
		$commandPrefix = $this->configService->getCommandPrefix();
		
		// Check if message is a command
		if (str_starts_with($content, $commandPrefix)) {
			$command = substr($content, strlen($commandPrefix));
			
			$response = $this->commandHandler->handleCommand($command, $message);
			
			if ($response) {
				$event->addAnswer($response, reply: true);
			}
		} elseif ($this->configService->getAutoResponse()) {
			// Auto-response to mentions or direct messages
			$botName = $this->configService->getBotName();
			if (str_contains($content, '@' . $botName) || str_contains($content, $botName)) {
				$event->addAnswer(
					"Привет! Я " . $botName . ". Используйте `" . $commandPrefix . "help` для списка команд.",
					reply: true
				);
			}
		}
	}

	private function handleReaction(BotInvokeEvent $event, array $message): void {
		// Handle reaction events
		if (isset($message['content'])) {
			$reaction = $message['content'];
			
			// Add reaction back for certain emojis
			if (in_array($reaction, ['👍', '❤️', '😀'])) {
				$event->addReaction($reaction);
			}
		}
	}

	private function handleBotJoin(BotInvokeEvent $event, array $message): void {
		// Bot was added to a conversation
		$event->addAnswer(
			"👋 Привет! Я " . $this->configService->getBotName() . 
			". Используйте `" . $this->configService->getCommandPrefix() . "help` для списка доступных команд."
		);
	}

	private function handleBotLeave(BotInvokeEvent $event, array $message): void {
		// Bot was removed from conversation - nothing to do
	}
} 