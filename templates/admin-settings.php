<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/** @var array $_ */
/** @var \OCP\IL10N $l */

?>

<div id="n8n-talk-bot-admin" class="section">
	<h2><?php p($l->t('n8n Talk Bot')); ?></h2>
	
	<div class="settings-hint">
		<p><?php p($l->t('Configure your Talk bot to automatically forward messages to n8n workflows and respond with automated replies.')); ?></p>
	</div>

	<form id="bot-settings-form">
		
		<div class="setting">
			<input type="checkbox" id="bot-enabled" class="checkbox" <?php if ($_['bot_enabled']): ?>checked="checked"<?php endif; ?>>
			<label for="bot-enabled">
				<?php p($l->t('Enable Talk Bot')); ?>
			</label>
			<p><em><?php p($l->t('When enabled, the bot will respond to messages in Talk conversations.')); ?></em></p>
		</div>

		<div class="setting">
			<label for="bot-name"><?php p($l->t('Bot Name')); ?></label>
			<input type="text" id="bot-name" value="<?php p($_['bot_name']); ?>" placeholder="<?php p($l->t('n8n Talk Bot')); ?>" maxlength="64">
			<p><em><?php p($l->t('Display name for the bot')); ?></em></p>
		</div>

		<div class="setting">
			<label for="command-prefix"><?php p($l->t('Command Prefix')); ?></label>
			<input type="text" id="command-prefix" value="<?php p($_['command_prefix']); ?>" placeholder="/" maxlength="5" style="width: 60px;">
			<p><em><?php p($l->t('Character that starts bot commands (e.g., /, !, @)')); ?></em></p>
		</div>

		<div class="setting">
			<input type="checkbox" id="auto-response" class="checkbox" <?php if ($_['auto_response']): ?>checked="checked"<?php endif; ?>>
			<label for="auto-response">
				<?php p($l->t('Auto Response')); ?>
			</label>
			<p><em><?php p($l->t('Automatically respond to mentions and direct messages')); ?></em></p>
		</div>

		<h3><?php p($l->t('n8n Integration')); ?></h3>
		
		<div class="setting">
			<input type="checkbox" id="n8n-integration-enabled" class="checkbox" <?php if ($_['n8n_integration_enabled']): ?>checked="checked"<?php endif; ?>>
			<label for="n8n-integration-enabled">
				<?php p($l->t('Enable n8n Integration')); ?>
			</label>
			<p><em><?php p($l->t('Forward all messages to external n8n workflow for processing')); ?></em></p>
		</div>

		<div class="setting">
			<label for="n8n-webhook-url"><?php p($l->t('n8n Webhook URL')); ?></label>
			<input type="url" id="n8n-webhook-url" value="<?php p($_['n8n_webhook_url']); ?>" placeholder="https://your-n8n.domain.com/webhook/your-webhook-id" style="width: 400px;">
			<p><em><?php p($l->t('URL of your n8n webhook that will receive and process chat messages')); ?></em></p>
		</div>

		<div class="setting">
			<button type="button" id="save-settings" class="primary"><?php p($l->t('Save Settings')); ?></button>
			<span id="save-status" style="margin-left: 10px;"></span>
		</div>
	</form>

	<div class="setting">
		<h3><?php p($l->t('Available Commands')); ?></h3>
		<p><?php p($l->t('Users can use these commands in Talk:')); ?></p>
		<ul>
			<li><code>/about</code> - <?php p($l->t('Show bot information and configuration')); ?></li>
		</ul>
		<p><em><?php p($l->t('All other messages are automatically forwarded to n8n workflows when integration is enabled.')); ?></em></p>
	</div>
</div> 