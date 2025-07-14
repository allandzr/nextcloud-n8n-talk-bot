<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Settings;

use OCA\N8nTalkBot\Service\BotConfigService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IL10N;
use OCP\Settings\ISettings;
use OCP\Util;

class AdminSettings implements ISettings {

	public function __construct(
		private BotConfigService $configService,
		private IL10N $l
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		// Get current configuration
		$config = $this->configService->getBotConfig();
		
		// Prepare parameters for template
		$parameters = [
			'bot_enabled' => $config['enabled'],
			'bot_name' => $config['name'],
			'command_prefix' => $config['commandPrefix'],
			'auto_response' => $config['autoResponse'],
			'description' => $config['description'],
			'n8n_integration_enabled' => $config['n8n_integration_enabled'],
			'n8n_webhook_url' => $config['n8n_webhook_url'],
		];

		// Load JavaScript and CSS
		Util::addScript('n8n-talk-bot', 'admin-settings');
		Util::addStyle('n8n-talk-bot', 'admin-settings');

		return new TemplateResponse('n8n-talk-bot', 'admin-settings', $parameters, '');
	}

	/**
	 * @return string the section ID, e.g. 'sharing'
	 */
	public function getSection(): string {
		return 'groupware';
	}

	/**
	 * @return int whether the form should be rather on the top or bottom of
	 *             the admin section. The forms are arranged in ascending order of the
	 *             priority values. It is required to return a value between 0 and 100.
	 */
	public function getPriority(): int {
		return 80;
	}
} 