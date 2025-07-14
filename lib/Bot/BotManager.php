<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Bot;

use OCA\N8nTalkBot\Service\BotConfigService;
use OCP\Talk\IBroker;
use Psr\Log\LoggerInterface;

class BotManager {
	private ?string $botId = null;
	private bool $initialized = false;

	public function __construct(
		private BotConfigService $configService,
		private MessageProcessor $messageProcessor,
		private CommandHandler $commandHandler,
		private LoggerInterface $logger
	) {
	}

	public function isConfigured(): bool {
		return $this->configService->isBotEnabled() && 
			   !empty($this->configService->getBotName()) &&
			   !empty($this->configService->getBotSecret());
	}

	public function initialize(): bool {
		if ($this->initialized) {
			return true;
		}

		if (!$this->isConfigured()) {
			$this->logger->warning('Bot is not properly configured');
			return false;
		}

		if (!$this->talkBroker->hasBackend()) {
			$this->logger->error('Talk is not available');
			return false;
		}

		$this->initialized = true;
		$this->logger->info('Talk bot initialized successfully');
		
		return true;
	}

	public function handleMessage(array $messageData): void {
		if (!$this->initialized) {
			return;
		}

		try {
			$this->logger->debug('Processing message', ['data' => $messageData]);

			// Check if message is a command
			$message = $messageData['message'] ?? '';
			$prefix = $this->configService->getCommandPrefix();

			if (str_starts_with($message, $prefix)) {
				$this->commandHandler->handleCommand($messageData);
			} else {
				$this->messageProcessor->processMessage($messageData);
			}
		} catch (\Exception $e) {
			$this->logger->error('Error handling message', [
				'exception' => $e,
				'message_data' => $messageData
			]);
		}
	}

	public function sendMessage(string $token, string $message, array $options = []): bool {
		try {
			// TODO: Implement message sending via Talk API
			// This would typically use Talk's Chat API to send messages
			
			$this->logger->debug('Sending message', [
				'token' => $token,
				'message' => $message,
				'options' => $options
			]);

			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error sending message', [
				'exception' => $e,
				'token' => $token,
				'message' => $message
			]);
			return false;
		}
	}

	public function sendReaction(string $token, int $messageId, string $reaction): bool {
		try {
			// TODO: Implement reaction sending via Talk API
			
			$this->logger->debug('Sending reaction', [
				'token' => $token,
				'message_id' => $messageId,
				'reaction' => $reaction
			]);

			return true;
		} catch (\Exception $e) {
			$this->logger->error('Error sending reaction', [
				'exception' => $e,
				'token' => $token,
				'message_id' => $messageId,
				'reaction' => $reaction
			]);
			return false;
		}
	}

	public function getBotId(): ?string {
		return $this->botId;
	}

	public function setBotId(string $botId): void {
		$this->botId = $botId;
	}

	public function isInitialized(): bool {
		return $this->initialized;
	}
} 