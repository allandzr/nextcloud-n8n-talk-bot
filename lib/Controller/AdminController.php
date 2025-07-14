<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Controller;

use OCA\N8nTalkBot\Service\BotConfigService;
use OCA\N8nTalkBot\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\AuthorizedAdminSetting;
use OCP\AppFramework\Http\JSONResponse;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IRequest;

class AdminController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private BotConfigService $configService,
		private IEventDispatcher $eventDispatcher
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Update bot configuration
	 */
	#[AuthorizedAdminSetting(settings: 'OCA\N8nTalkBot\Settings\AdminSettings')]
	public function updateConfig(
		bool $enabled = false,
		string $name = 'HelpBot',
		string $commandPrefix = '/',
		bool $autoResponse = true,
		bool $n8nIntegrationEnabled = false,
		string $n8nWebhookUrl = ''
	): JSONResponse {
		try {
			// Update settings
			$this->configService->setBotEnabled($enabled);
			
			if (!empty($name)) {
				$this->configService->setBotName($name);
			}

			if (!empty($commandPrefix)) {
				$this->configService->setCommandPrefix($commandPrefix);
			}

			$this->configService->setAutoResponse($autoResponse);

			// Update n8n integration settings
			$this->configService->setN8nIntegrationEnabled($n8nIntegrationEnabled);
			
			if (!empty($n8nWebhookUrl)) {
				$this->configService->setN8nWebhookUrl($n8nWebhookUrl);
			}

			// Re-register bot with Talk if enabled
			if ($enabled) {
				$this->registerBotWithTalk();
			}

			return new JSONResponse([
				'success' => true,
				'message' => 'Settings saved successfully',
				'config' => $this->configService->getBotConfig()
			]);
		} catch (\Exception $e) {
			return new JSONResponse([
				'success' => false,
				'message' => 'Failed to save settings: ' . $e->getMessage()
			], 500);
		}
	}
	
	private function registerBotWithTalk(): void {
		// Create BotInstallEvent to register/update our bot
		$event = new \OCA\Talk\Events\BotInstallEvent(
			name: $this->configService->getBotName(),
			secret: $this->configService->getBotSecret(),
			url: 'nextcloudapp://' . Application::APP_ID,
			description: $this->configService->getBotDescription(),
			features: \OCA\Talk\Model\Bot::FEATURE_EVENT | \OCA\Talk\Model\Bot::FEATURE_RESPONSE | \OCA\Talk\Model\Bot::FEATURE_REACTION
		);
		
		$this->eventDispatcher->dispatchTyped($event);
	}
} 