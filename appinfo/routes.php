<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'routes' => [
		// Admin routes
		['name' => 'admin#updateConfig', 'url' => '/admin/config', 'verb' => 'POST'],

		// Webhook routes
		['name' => 'webhook#receive', 'url' => '/webhook', 'verb' => 'POST'],
		['name' => 'webhook#status', 'url' => '/webhook/status', 'verb' => 'GET'],

		// Public API routes
		['name' => 'api#status', 'url' => '/api/v1/status', 'verb' => 'GET'],
	],

	'ocs' => [
		// OCS API routes for Talk integration
		['name' => 'api#getBotInfo', 'url' => '/api/v1/bot/info', 'verb' => 'GET'],
	]
]; 