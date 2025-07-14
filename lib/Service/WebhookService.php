<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Service;

use OCA\N8nTalkBot\Bot\BotManager;
use Psr\Log\LoggerInterface;

class WebhookService {
	public function __construct(
		private BotManager $botManager,
		private LoggerInterface $logger
	) {}

	/**
	 * Handle incoming webhook from Talk
	 */
	public function handleWebhook(array $payload, string $signature = ''): array {
		$this->logger->debug('Webhook received', [
			'payload_keys' => array_keys($payload),
			'signature_provided' => !empty($signature)
		]);

		try {
			// Validate webhook signature if provided
			if (!empty($signature) && !$this->validateSignature($payload, $signature)) {
				$this->logger->warning('Invalid webhook signature');
				return [
					'status' => 'error',
					'message' => 'Invalid signature'
				];
			}

			// Extract event type from payload
			$eventType = $payload['event'] ?? '';
			
			switch ($eventType) {
				case 'message':
					return $this->handleMessageEvent($payload);
				case 'reaction':
					return $this->handleReactionEvent($payload);
				default:
					$this->logger->debug('Unhandled webhook event type', ['event' => $eventType]);
					return [
						'status' => 'ignored',
						'message' => 'Event type not handled'
					];
			}
		} catch (\Exception $e) {
			$this->logger->error('Error processing webhook', [
				'exception' => $e,
				'payload' => $payload
			]);

			return [
				'status' => 'error',
				'message' => 'Internal error processing webhook'
			];
		}
	}

	private function handleMessageEvent(array $payload): array {
		$messageData = $payload['data'] ?? [];
		
		if (empty($messageData)) {
			return [
				'status' => 'error',
				'message' => 'No message data provided'
			];
		}

		// Process the message through bot manager
		$this->botManager->handleMessage($messageData);

		return [
			'status' => 'success',
			'message' => 'Message processed'
		];
	}

	private function handleReactionEvent(array $payload): array {
		$reactionData = $payload['data'] ?? [];
		
		$this->logger->debug('Reaction event received', $reactionData);

		// TODO: Implement reaction handling if needed
		
		return [
			'status' => 'success',
			'message' => 'Reaction event acknowledged'
		];
	}

	private function validateSignature(array $payload, string $signature): bool {
		// TODO: Implement signature validation using bot secret
		// This would typically involve:
		// 1. Get bot secret from config
		// 2. Create expected signature from payload using HMAC
		// 3. Compare with provided signature
		
		$this->logger->debug('Signature validation not implemented yet');
		return true; // For now, accept all signatures
	}
} 