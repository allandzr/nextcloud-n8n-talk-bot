<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Controller;

use OCA\N8nTalkBot\Bot\BotManager;
use OCA\N8nTalkBot\Service\BotConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class ApiController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private BotConfigService $configService,
		private BotManager $botManager
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Get bot status
	 */
	#[PublicPage]
	public function status(): JSONResponse {
		return new JSONResponse([
			'app' => 'nextcloud_talk_bot',
			'version' => '1.0.0',
			'status' => $this->configService->isBotEnabled() ? 'enabled' : 'disabled',
			'initialized' => $this->botManager->isInitialized(),
			'name' => $this->configService->getBotName(),
			'features' => $this->configService->getBotFeatures(),
			'command_prefix' => $this->configService->getCommandPrefix(),
			'response_mode' => $this->configService->getResponseMode(),
		]);
	}

	/**
	 * Get bot information (OCS API)
	 */
	public function getBotInfo(): JSONResponse {
		if (!$this->configService->isBotEnabled()) {
			return new JSONResponse([
				'status' => 'disabled',
				'message' => 'Bot is not enabled'
			], 404);
		}

		return new JSONResponse([
			'name' => $this->configService->getBotName(),
			'description' => $this->configService->getBotDescription(),
			'command_prefix' => $this->configService->getCommandPrefix(),
			'features' => $this->configService->getBotFeatures(),
			'response_mode' => $this->configService->getResponseMode(),
			'version' => '1.0.0'
		]);
	}
} 