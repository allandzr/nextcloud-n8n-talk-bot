<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\N8nTalkBot\Command;

use OCA\N8nTalkBot\Bot\CommandHandler;
use OCA\N8nTalkBot\Service\BotConfigService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestBotCommand extends Command {

	public function __construct(
		private BotConfigService $configService,
		private CommandHandler $commandHandler,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('n8n-talk-bot:test')
			->setDescription('Test bot command handling')
			->addArgument('command', InputArgument::REQUIRED, 'Command to test (without prefix)');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$command = $input->getArgument('command');
		
		$output->writeln('<info>Testing bot configuration...</info>');
		$output->writeln('Bot enabled: ' . ($this->configService->isBotEnabled() ? 'yes' : 'no'));
		$output->writeln('Bot name: ' . $this->configService->getBotName());
		$output->writeln('Command prefix: ' . $this->configService->getCommandPrefix());
		
		$output->writeln('');
		$output->writeln('<info>Testing command: ' . $command . '</info>');
		
		// Create test message data
		$testMessageData = [
			'actor' => [
				'id' => 'admin',
				'name' => 'Admin User',
				'type' => 'users'
			],
			'object' => [
				'content' => $this->configService->getCommandPrefix() . $command
			]
		];
		
		$response = $this->commandHandler->handleCommand($command, $testMessageData);
		
		$output->writeln('');
		$output->writeln('<info>Bot response:</info>');
		$output->writeln($response ?: '<error>No response</error>');
		
		return Command::SUCCESS;
	}
}