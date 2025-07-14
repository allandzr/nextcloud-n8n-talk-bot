<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\AppInfo;

use OCA\N8nTalkBot\Bot\BotManager;
use OCA\N8nTalkBot\Bot\CommandHandler;
use OCA\N8nTalkBot\Bot\MessageProcessor;
use OCA\N8nTalkBot\Command\TestBotCommand;
use OCA\N8nTalkBot\Listener\TalkChatMessageListener;
use OCA\N8nTalkBot\Listener\BotInvokeListener;
use OCA\N8nTalkBot\Service\BotConfigService;
use OCA\N8nTalkBot\Service\N8nIntegrationService;
use OCA\N8nTalkBot\Service\WebhookService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;

class Application extends App implements IBootstrap {

	public const APP_ID = 'n8n-talk-bot';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		// Register services
		$context->registerService(BotConfigService::class, function ($c) {
			return new BotConfigService(
				$c->get(\OCP\IAppConfig::class)
			);
		});

		$context->registerService(MessageProcessor::class, function ($c) {
			return new MessageProcessor(
				$c->get(BotConfigService::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		$context->registerService(CommandHandler::class, function ($c) {
			return new CommandHandler(
				$c->get(BotConfigService::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		$context->registerService(BotManager::class, function ($c) {
			return new BotManager(
				$c->get(BotConfigService::class),
				$c->get(MessageProcessor::class),
				$c->get(CommandHandler::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		$context->registerService(WebhookService::class, function ($c) {
			return new WebhookService(
				$c->get(BotManager::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		$context->registerService(N8nIntegrationService::class, function ($c) {
			return new N8nIntegrationService(
				$c->get(\OCP\Http\Client\IClientService::class),
				$c->get(BotConfigService::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		// Register BotInvokeListener service
		$context->registerService(BotInvokeListener::class, function ($c) {
			return new BotInvokeListener(
				$c->get(BotConfigService::class),
				$c->get(CommandHandler::class),
				$c->get(N8nIntegrationService::class),
				$c->get(\Psr\Log\LoggerInterface::class)
			);
		});

		// Register console command
		$context->registerService(TestBotCommand::class, function ($c) {
			return new TestBotCommand(
				$c->get(BotConfigService::class),
				$c->get(CommandHandler::class)
			);
		});

		// Register event listeners
		$context->registerEventListener(
			\OCA\Talk\Events\ChatMessageSentEvent::class,
			TalkChatMessageListener::class
		);
		
		// Register BotInvokeEvent listener
		$context->registerEventListener(
			\OCA\Talk\Events\BotInvokeEvent::class,
			BotInvokeListener::class
		);
		
		// Register console commands
		$context->registerService('OCA\N8nTalkBot\Command\TestBotCommand', function($c) {
			return $c->get(TestBotCommand::class);
		});
	}

	public function boot(IBootContext $context): void {
		$configService = $context->getAppContainer()->get(BotConfigService::class);
		
		// Register bot with Talk if enabled and not already registered
		if ($configService->isBotEnabled()) {
			$this->registerBotWithTalk($context);
		}
	}
	
	private function registerBotWithTalk(IBootContext $context): void {
		$configService = $context->getAppContainer()->get(BotConfigService::class);
		$dispatcher = $context->getAppContainer()->get(IEventDispatcher::class);
		
		// Create BotInstallEvent to register our bot
		$event = new \OCA\Talk\Events\BotInstallEvent(
			name: $configService->getBotName(),
			secret: $configService->getBotSecret(),
			url: 'nextcloudapp://' . self::APP_ID,
			description: $configService->getBotDescription(),
			features: \OCA\Talk\Model\Bot::FEATURE_EVENT | \OCA\Talk\Model\Bot::FEATURE_RESPONSE | \OCA\Talk\Model\Bot::FEATURE_REACTION
		);
		
		$dispatcher->dispatchTyped($event);
	}
} 