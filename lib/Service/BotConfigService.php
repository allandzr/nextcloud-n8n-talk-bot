<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Service;

use OCP\IAppConfig;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class BotConfigService {
	private const APP_ID = 'n8n-talk-bot';

	// Configuration keys
	private const BOT_ENABLED = 'bot_enabled';
	private const BOT_NAME = 'bot_name';
	private const BOT_DESCRIPTION = 'bot_description';
	private const BOT_SECRET = 'bot_secret';
	private const BOT_WEBHOOK_URL = 'bot_webhook_url';
	private const BOT_FEATURES = 'bot_features';
	private const BOT_COMMAND_PREFIX = 'bot_command_prefix';
	private const BOT_RESPONSE_MODE = 'bot_response_mode';
	private const BOT_AUTO_RESPONSE = 'bot_auto_response';
	private const N8N_INTEGRATION_ENABLED = 'n8n_integration_enabled';
	private const N8N_WEBHOOK_URL = 'n8n_webhook_url';

	public function __construct(
		private \OCP\IAppConfig $appConfig
	) {
	}

	public function isBotEnabled(): bool {
		return $this->appConfig->getValueBool(self::APP_ID, self::BOT_ENABLED, false);
	}

	public function setBotEnabled(bool $enabled): void {
		$this->appConfig->setValueBool(self::APP_ID, self::BOT_ENABLED, $enabled);
	}

	public function getBotName(): string {
		return $this->appConfig->getValueString(self::APP_ID, self::BOT_NAME, 'NextcloudBot');
	}

	public function setBotName(string $name): void {
		$this->appConfig->setValueString(self::APP_ID, self::BOT_NAME, $name);
	}

	public function getBotDescription(): string {
		return $this->appConfig->getValueString(
			self::APP_ID, 
			self::BOT_DESCRIPTION, 
			'Helpful bot for Nextcloud Talk'
		);
	}

	public function setBotDescription(string $description): void {
		$this->appConfig->setValueString(self::APP_ID, self::BOT_DESCRIPTION, $description);
	}

	public function getBotSecret(): string {
		$secret = $this->appConfig->getValueString(self::APP_ID, self::BOT_SECRET, '');
		if (empty($secret)) {
			$secret = $this->generateBotSecret();
			$this->setBotSecret($secret);
		}
		return $secret;
	}

	public function setBotSecret(string $secret): void {
		$this->appConfig->setValueString(self::APP_ID, self::BOT_SECRET, $secret);
	}

	public function getBotWebhookUrl(): string {
		return $this->appConfig->getValueString(self::APP_ID, self::BOT_WEBHOOK_URL, '');
	}

	public function setBotWebhookUrl(string $url): void {
		$this->appConfig->setValueString(self::APP_ID, self::BOT_WEBHOOK_URL, $url);
	}

	public function getBotFeatures(): array {
		$features = $this->appConfig->getValueString(self::APP_ID, self::BOT_FEATURES, 'webhook,response,event');
		return explode(',', $features);
	}

	public function setBotFeatures(array $features): void {
		$this->appConfig->setValueString(self::APP_ID, self::BOT_FEATURES, implode(',', $features));
	}

	public function getCommandPrefix(): string {
		return $this->appConfig->getValueString(self::APP_ID, self::BOT_COMMAND_PREFIX, '!');
	}

	public function setCommandPrefix(string $prefix): void {
		$this->appConfig->setValueString(self::APP_ID, self::BOT_COMMAND_PREFIX, $prefix);
	}

	/**
	 * Response modes: auto, manual, hybrid
	 */
	public function getResponseMode(): string {
		return $this->appConfig->getValueString(self::APP_ID, self::BOT_RESPONSE_MODE, 'auto');
	}

	public function setResponseMode(string $mode): void {
		$this->appConfig->setValueString(self::APP_ID, self::BOT_RESPONSE_MODE, $mode);
	}

	public function getAutoResponse(): bool {
		return $this->appConfig->getValueBool(self::APP_ID, self::BOT_AUTO_RESPONSE, true);
	}

	public function setAutoResponse(bool $autoResponse): void {
		$this->appConfig->setValueBool(self::APP_ID, self::BOT_AUTO_RESPONSE, $autoResponse);
	}

	public function isN8nIntegrationEnabled(): bool {
		return $this->appConfig->getValueBool(self::APP_ID, self::N8N_INTEGRATION_ENABLED, false);
	}

	public function setN8nIntegrationEnabled(bool $enabled): void {
		$this->appConfig->setValueBool(self::APP_ID, self::N8N_INTEGRATION_ENABLED, $enabled);
	}

	public function getN8nWebhookUrl(): string {
		return $this->appConfig->getValueString(self::APP_ID, self::N8N_WEBHOOK_URL, '');
	}

	public function setN8nWebhookUrl(string $url): void {
		$this->appConfig->setValueString(self::APP_ID, self::N8N_WEBHOOK_URL, $url);
	}

	/**
	 * Get all bot configuration as array
	 */
	public function getBotConfig(): array {
		return [
			'enabled' => $this->isBotEnabled(),
			'name' => $this->getBotName(),
			'description' => $this->getBotDescription(),
			'secret' => $this->getBotSecret(),
			'webhook_url' => $this->getBotWebhookUrl(),
			'features' => $this->getBotFeatures(),
			'commandPrefix' => $this->getCommandPrefix(),
			'response_mode' => $this->getResponseMode(),
			'autoResponse' => $this->getAutoResponse(),
			'n8n_integration_enabled' => $this->isN8nIntegrationEnabled(),
			'n8n_webhook_url' => $this->getN8nWebhookUrl(),
		];
	}

	private function generateBotSecret(): string {
		return bin2hex(random_bytes(32)); // 64 character hex string
	}
} 