<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Controller;

use OCA\N8nTalkBot\Service\WebhookService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;

class WebhookController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private WebhookService $webhookService
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Receive webhook from Talk
	 */
	#[PublicPage]
	public function receive(): JSONResponse {
		try {
			// Get raw input
			$input = file_get_contents('php://input');
			$payload = json_decode($input, true);

			if (json_last_error() !== JSON_ERROR_NONE) {
				return new JSONResponse([
					'status' => 'error',
					'message' => 'Invalid JSON payload'
				], 400);
			}

			// Get signature from headers
			$signature = $this->request->getHeader('X-Talk-Signature') ?? '';

			// Process webhook
			$result = $this->webhookService->handleWebhook($payload, $signature);

			$statusCode = match($result['status']) {
				'success' => 200,
				'ignored' => 200,
				'error' => 400,
				default => 500
			};

			return new JSONResponse($result, $statusCode);

		} catch (\Exception $e) {
			return new JSONResponse([
				'status' => 'error',
				'message' => 'Internal server error'
			], 500);
		}
	}

	/**
	 * Get webhook status
	 */
	#[PublicPage]
	public function status(): JSONResponse {
		return new JSONResponse([
			'status' => 'active',
			'message' => 'Webhook endpoint is ready',
			'timestamp' => time(),
			'version' => '1.0.0'
		]);
	}
} 