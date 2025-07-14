<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Bot;

use OCA\N8nTalkBot\Service\BotConfigService;
use Psr\Log\LoggerInterface;

class MessageProcessor {
	public function __construct(
		private BotConfigService $configService,
		private LoggerInterface $logger
	) {}

	public function processMessage(array $messageData): void {
		$message = $messageData['message'] ?? '';
		$token = $messageData['token'] ?? '';
		$actorId = $messageData['actorId'] ?? '';
		$actorDisplayName = $messageData['actorDisplayName'] ?? '';

		$this->logger->debug('Processing regular message', [
			'message' => $message,
			'token' => $token,
			'actor' => $actorId
		]);

		// Skip if message is empty or from bot itself
		if (empty($message) || $this->isBotMessage($actorId)) {
			return;
		}

		$responseMode = $this->configService->getResponseMode();

		switch ($responseMode) {
			case 'auto':
				$this->processAutoResponse($messageData);
				break;
			case 'manual':
				// Only respond to specific triggers
				$this->processManualResponse($messageData);
				break;
			case 'hybrid':
				// Combination of auto and manual
				$this->processHybridResponse($messageData);
				break;
		}
	}

	private function processAutoResponse(array $messageData): void {
		$message = strtolower($messageData['message'] ?? '');
		$token = $messageData['token'] ?? '';

		// Simple keyword-based responses
		$responses = $this->getAutoResponses();

		foreach ($responses as $keyword => $response) {
			if (str_contains($message, $keyword)) {
				$this->logger->info('Auto response triggered', [
					'keyword' => $keyword,
					'token' => $token
				]);
				// TODO: Send response via BotManager
				break;
			}
		}
	}

	private function processManualResponse(array $messageData): void {
		$message = strtolower($messageData['message'] ?? '');
		$token = $messageData['token'] ?? '';

		// Check for specific triggers that require manual handling
		$triggers = [
			'@' . strtolower($this->configService->getBotName()),
			'помощь',
			'help',
			'info',
			'информация'
		];

		foreach ($triggers as $trigger) {
			if (str_contains($message, $trigger)) {
				$this->handleManualTrigger($messageData, $trigger);
				break;
			}
		}
	}

	private function processHybridResponse(array $messageData): void {
		// First try manual triggers
		$this->processManualResponse($messageData);
		
		// If no manual trigger matched, try auto response
		if (!$this->wasResponseSent($messageData)) {
			$this->processAutoResponse($messageData);
		}
	}

	private function handleManualTrigger(array $messageData, string $trigger): void {
		$token = $messageData['token'] ?? '';
		$actorDisplayName = $messageData['actorDisplayName'] ?? 'Пользователь';

		switch ($trigger) {
			case 'помощь':
			case 'help':
				$response = $this->getHelpMessage($actorDisplayName);
				break;
			case 'info':
			case 'информация':
				$response = $this->getInfoMessage();
				break;
			default:
				$response = "Привет, {$actorDisplayName}! Я бот {$this->configService->getBotName()}. Напишите 'помощь' для получения списка команд.";
				break;
		}

		$this->logger->info('Manual trigger response', [
			'trigger' => $trigger,
			'token' => $token,
			'response' => $response
		]);

		// TODO: Send response via BotManager
	}

	private function getAutoResponses(): array {
		return [
			'привет' => 'Привет! 👋',
			'hello' => 'Hello! 👋',
			'спасибо' => 'Пожалуйста! 😊',
			'thanks' => 'You\'re welcome! 😊',
			'как дела' => 'У меня всё отлично, спасибо за вопрос!',
			'how are you' => 'I\'m doing great, thanks for asking!',
			'время' => 'Текущее время: ' . date('H:i:s'),
			'time' => 'Current time: ' . date('H:i:s'),
			'дата' => 'Сегодня: ' . date('d.m.Y'),
			'date' => 'Today: ' . date('Y-m-d'),
		];
	}

	private function getHelpMessage(string $userName): string {
		$prefix = $this->configService->getCommandPrefix();
		
		return "Привет, {$userName}! 🤖\n\n" .
			   "Я бот {$this->configService->getBotName()}. Вот что я умею:\n\n" .
			   "**Команды:**\n" .
			   "• {$prefix}help - показать эту справку\n" .
			   "• {$prefix}time - текущее время\n" .
			   "• {$prefix}date - текущая дата\n" .
			   "• {$prefix}info - информация о боте\n" .
			   "• {$prefix}ping - проверить работу бота\n\n" .
			   "**Автоответы:**\n" .
			   "Просто напишите 'привет', 'спасибо', 'как дела' и я отвечу!\n\n" .
			   "Упомяните меня @{$this->configService->getBotName()} для привлечения внимания.";
	}

	private function getInfoMessage(): string {
		return "ℹ️ **Информация о боте:**\n\n" .
			   "**Название:** {$this->configService->getBotName()}\n" .
			   "**Описание:** {$this->configService->getBotDescription()}\n" .
			   "**Префикс команд:** {$this->configService->getCommandPrefix()}\n" .
			   "**Режим ответов:** {$this->configService->getResponseMode()}\n" .
			   "**Возможности:** " . implode(', ', $this->configService->getBotFeatures()) . "\n\n" .
			   "Создан для улучшения общения в Nextcloud Talk! 🚀";
	}

	private function isBotMessage(string $actorId): bool {
		// Check if the message is from the bot itself to avoid loops
		$botName = strtolower($this->configService->getBotName());
		return strtolower($actorId) === $botName || 
			   str_contains(strtolower($actorId), 'bot');
	}

	private function wasResponseSent(array $messageData): bool {
		// TODO: Implement logic to track if response was already sent
		// This could be done by checking a cache or database
		return false;
	}
} 