<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name  
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Service;

use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use Psr\Log\LoggerInterface;

class N8nIntegrationService {
	
	public function __construct(
		private IClientService $clientService,
		private BotConfigService $configService,
		private LoggerInterface $logger
	) {
	}

	/**
	 * Send message to n8n webhook and process response
	 * 
	 * @param array $messageData Message data to send to n8n
	 * @return array|null Response from n8n or null if failed/disabled
	 */
	public function processMessage(array $messageData): ?array {
		// Check if n8n integration is enabled
		if (!$this->configService->isN8nIntegrationEnabled()) {
			$this->logger->debug('n8n integration is disabled');
			return null;
		}

		$webhookUrl = $this->configService->getN8nWebhookUrl();
		if (empty($webhookUrl)) {
			$this->logger->warning('n8n webhook URL is not configured');
			return null;
		}

		try {
			// Validate webhook URL
			if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
				$this->logger->warning('Invalid n8n webhook URL', ['url' => $webhookUrl]);
				return null;
			}

			// Prepare payload in standard format
			$payload = $this->formatMessageForN8n($messageData);
			
			$this->logger->debug('Sending message to n8n', [
				'webhook_url' => $webhookUrl,
				'message_id' => $payload['messageId'] ?? 'unknown'
			]);

			// Send HTTP request to n8n with better error handling
			$client = $this->clientService->newClient();
			$response = $client->post($webhookUrl, [
				'headers' => [
					'Content-Type' => 'application/json',
					'User-Agent' => 'Nextcloud-Talk-Bot/1.0'
				],
				'json' => $payload,
				'timeout' => 10, // Reduced timeout to prevent hanging
				'connect_timeout' => 5
			]);

			// Process response
			return $this->processN8nResponse($response);

		} catch (\GuzzleHttp\Exception\ConnectException $e) {
			$this->logger->warning('Cannot connect to n8n webhook', [
				'webhook_url' => $webhookUrl,
				'error' => $e->getMessage()
			]);
			return null;
		} catch (\GuzzleHttp\Exception\RequestException $e) {
			$this->logger->warning('HTTP error when calling n8n webhook', [
				'webhook_url' => $webhookUrl,
				'status_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 'unknown',
				'error' => $e->getMessage()
			]);
			return null;
		} catch (\Exception $e) {
			$this->logger->error('Unexpected error when processing message with n8n', [
				'exception' => $e,
				'webhook_url' => $webhookUrl,
				'message_data' => $messageData
			]);
			return null;
		}
	}

	/**
	 * Format message data into standard n8n format
	 */
	private function formatMessageForN8n(array $messageData): array {
		return [
			'messageId' => $messageData['messageId'] ?? $messageData['id'] ?? uniqid('msg_'),
			'conversationToken' => $messageData['conversationToken'] ?? $messageData['token'] ?? '',
			'userId' => $messageData['actorId'] ?? '',
			'userDisplayName' => $messageData['actorDisplayName'] ?? '',
			'message' => $messageData['message'] ?? '',
			'timestamp' => date('c'), // ISO 8601 format
			'messageType' => $messageData['messageType'] ?? 'chat'
		];
	}

	/**
	 * Process response from n8n webhook
	 */
	private function processN8nResponse(IResponse $response): ?array {
		$statusCode = $response->getStatusCode();
		
		if ($statusCode !== 200) {
			$this->logger->warning('n8n returned non-200 status', [
				'status_code' => $statusCode,
				'body' => $response->getBody()
			]);
			return null;
		}

		try {
			$responseData = json_decode($response->getBody(), true);
			
			if (json_last_error() !== JSON_ERROR_NONE) {
				$this->logger->error('Invalid JSON response from n8n', [
					'json_error' => json_last_error_msg(),
					'body' => $response->getBody()
				]);
				return null;
			}

			$this->logger->debug('Received response from n8n', [
				'success' => $responseData['success'] ?? false,
				'has_response' => !empty($responseData['response'])
			]);

			return $responseData;

		} catch (\Exception $e) {
			$this->logger->error('Failed to process n8n response', [
				'exception' => $e,
				'response_body' => $response->getBody()
			]);
			return null;
		}
	}
} 