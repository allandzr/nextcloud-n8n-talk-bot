/**
 * SPDX-FileCopyrightText: 2025 Your Name
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

document.addEventListener('DOMContentLoaded', function() {
	console.log('Nextcloud Talk Bot admin settings loaded');

	// Save settings
	const saveButton = document.getElementById('save-settings');
	if (saveButton) {
		saveButton.addEventListener('click', function() {
			const status = document.getElementById('save-status');
			if (status) {
				status.textContent = 'Saving...';
				status.style.color = '#0082c9';
			}

			const settings = {
				enabled: document.getElementById('bot-enabled')?.checked || false,
				name: document.getElementById('bot-name')?.value || 'HelpBot',
				commandPrefix: document.getElementById('command-prefix')?.value || '/',
				autoResponse: document.getElementById('auto-response')?.checked || true,
				n8nIntegrationEnabled: document.getElementById('n8n-integration-enabled')?.checked || false,
				n8nWebhookUrl: document.getElementById('n8n-webhook-url')?.value || ''
			};

			const formData = new FormData();
			formData.append('enabled', settings.enabled);
			formData.append('name', settings.name);
			formData.append('commandPrefix', settings.commandPrefix);
			formData.append('autoResponse', settings.autoResponse);
			formData.append('n8nIntegrationEnabled', settings.n8nIntegrationEnabled);
			formData.append('n8nWebhookUrl', settings.n8nWebhookUrl);
			
			// Add request token if available
			if (typeof OC !== 'undefined' && OC.requestToken) {
				formData.append('requesttoken', OC.requestToken);
			}

			const url = (typeof OC !== 'undefined' && OC.generateUrl) 
				? OC.generateUrl('/apps/n8n-talk-bot/admin/config')
				: '/apps/n8n-talk-bot/admin/config';

			fetch(url, {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (status) {
					if (data.success !== false) {
						status.textContent = 'Settings saved successfully!';
						status.style.color = '#46ba61';
					} else {
						status.textContent = 'Error saving settings: ' + (data.message || 'Unknown error');
						status.style.color = '#e9322d';
					}
					setTimeout(() => {
						status.textContent = '';
					}, 3000);
				}
			})
			.catch(error => {
				console.error('Error:', error);
				if (status) {
					status.textContent = 'Error saving settings';
					status.style.color = '#e9322d';
				}
			});
		});
	}
}); 