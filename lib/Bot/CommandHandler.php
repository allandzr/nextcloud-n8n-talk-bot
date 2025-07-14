<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Bot;

use OCA\N8nTalkBot\Service\BotConfigService;
use Psr\Log\LoggerInterface;

class CommandHandler {
	public function __construct(
		private BotConfigService $configService,
		private LoggerInterface $logger
	) {}

	/**
	 * Handle a bot command
	 */
	public function handleCommand(string $command, array $messageData = []): ?string {
		$parts = explode(' ', trim($command));
		$cmd = strtolower($parts[0]);
		$args = array_slice($parts, 1);

		// Extract user info from message data if available
		$userName = 'User';
		if (isset($messageData['actor']['name'])) {
			$userName = $messageData['actor']['name'];
		}

		return match ($cmd) {
			'about', 'о боте' => $this->handleAbout(),
			default => $this->handleUnknownCommand($cmd),
		};
	}

	private function handleAbout(): string {
		$name = $this->configService->getBotName();
		$description = $this->configService->getBotDescription();
		$enabled = $this->configService->isBotEnabled();
		$prefix = $this->configService->getCommandPrefix();
		$n8nEnabled = $this->configService->isN8nIntegrationEnabled();
		$n8nUrl = $this->configService->getN8nWebhookUrl();
		
		$config = "**🤖 О боте {$name}:**\n\n" .
			"{$description}\n\n" .
			"**Версия:** 1.0.0\n" .
			"**Платформа:** Nextcloud Talk\n" .
			"**Создан для:** улучшения общения в чатах! 💬\n\n" .
			"**📊 Конфигурация бота:**\n" .
			"• **Название:** {$name}\n" .
			"• **Префикс команд:** `{$prefix}`\n" .
			"• **Статус бота:** " . ($enabled ? '✅ Активен' : '❌ Отключен') . "\n" .
			"• **n8n интеграция:** " . ($n8nEnabled ? '✅ Включена' : '❌ Отключена') . "\n";
		
		if ($n8nEnabled && !empty($n8nUrl)) {
			$config .= "• **n8n Webhook URL:** `{$n8nUrl}`\n";
		}
		
		$config .= "• **Время работы:** " . date('H:i:s d.m.Y');
		
		return $config;
	}

	private function handleUnknownCommand(string $cmd): string {
		$prefix = $this->configService->getCommandPrefix();
		return "❓ Неизвестная команда: `{$cmd}`\n" .
			"Доступна только команда `{$prefix}about` для информации о боте.";
	}
} 