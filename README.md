# Nextcloud Talk Bot

🤖 Advanced bot for Nextcloud Talk conversations with intelligent responses and custom commands.

## Features

- **Smart Bot** - Intelligent bot that can respond to messages in Talk conversations
- **Chat Integration** - Seamlessly integrates with Nextcloud Talk API
- **Custom Commands** - Supports custom commands and responses
- **Configurable** - Admin settings for bot configuration
- **Analytics** - Basic usage analytics and logging
- **Webhook Support** - Can receive and respond to webhook events
- **Rich Responses** - Supports rich text formatting and reactions
- **Multi-language Support** - Commands available in English and Russian

## Installation

1. **Download** this app from the Nextcloud App Store or clone this repository into your `apps-extra` directory
2. **Enable** the app in your Nextcloud admin panel
3. **Configure** the bot in Admin Settings → Additional → Nextcloud Talk Bot
4. **Enable Talk** - Make sure Nextcloud Talk app is installed and enabled

## Configuration

### Admin Settings

Navigate to **Admin Settings → Additional → Nextcloud Talk Bot** to configure:

- **Bot Name** - Display name for the bot
- **Bot Description** - Description of the bot's purpose
- **Command Prefix** - Character(s) that prefix bot commands (default: `!`)
- **Response Mode** - How the bot responds to messages:
  - `auto` - Responds to keywords automatically
  - `manual` - Only responds to specific triggers
  - `hybrid` - Combination of auto and manual
- **Features** - Enable/disable bot features:
  - `webhook` - Receive chat messages as webhooks
  - `response` - Post messages and reactions as responses
  - `event` - Read posted messages from local events
  - `reaction` - Get notified about reactions

### Talk Integration

The bot integrates with Nextcloud Talk through:

1. **Event Listeners** - Listens to chat messages in real-time
2. **Talk Broker API** - Uses Nextcloud's Talk integration API
3. **Webhook Support** - Can receive webhook notifications from Talk

## Usage

### Commands

All commands start with the configured prefix (default `!`):

#### Basic Commands
- `!help` - Show help message
- `!ping` - Test bot responsiveness
- `!info` - Show bot information
- `!status` - Show bot status
- `!version` - Show bot version

#### Utility Commands
- `!time` - Show current time
- `!date` - Show current date
- `!echo <text>` - Repeat the provided text
- `!random <number>` - Generate random number from 1 to specified number
- `!calc <expression>` - Simple calculator

#### Russian Commands
The bot also supports Russian commands:
- `!помощь` - Help
- `!время` - Time
- `!дата` - Date
- `!повтори <текст>` - Echo
- `!случайно <число>` - Random
- `!вычисли <выражение>` - Calculate

### Auto-responses

The bot can automatically respond to common phrases:
- "привет" / "hello" → Greeting response
- "спасибо" / "thanks" → Acknowledgment
- "как дела" / "how are you" → Status response
- "время" / "time" → Current time
- "дата" / "date" → Current date

### Mentions

Mention the bot with `@BotName` to get its attention for help or information.

## Development

### Project Structure

```
nextcloud-talk-bot/
├── appinfo/
│   ├── info.xml              # App metadata
│   └── routes.php            # API routes
├── lib/
│   ├── AppInfo/
│   │   └── Application.php   # Main app class
│   ├── Bot/
│   │   ├── BotManager.php    # Main bot logic
│   │   ├── CommandHandler.php # Command processing
│   │   └── MessageProcessor.php # Message processing
│   ├── Controller/
│   │   ├── AdminController.php # Admin API
│   │   ├── ApiController.php   # Public API
│   │   └── WebhookController.php # Webhook handling
│   ├── Listener/
│   │   └── TalkChatMessageListener.php # Talk event listener
│   ├── Service/
│   │   ├── BotConfigService.php # Configuration management
│   │   └── WebhookService.php   # Webhook processing
│   └── Settings/
│       └── AdminSettings.php   # Admin settings interface
└── templates/
    └── admin-settings.php     # Admin settings template
```

### Architecture

The bot uses an event-driven architecture:

1. **Event Listener** - Captures chat messages from Talk
2. **Message Processor** - Analyzes and categorizes messages
3. **Command Handler** - Processes bot commands
4. **Bot Manager** - Coordinates responses and actions
5. **Config Service** - Manages bot configuration
6. **Webhook Service** - Handles external webhook requests

### API Endpoints

#### Admin API
- `GET /api/v1/admin/config` - Get bot configuration
- `PUT /api/v1/admin/config` - Update bot configuration
- `POST /api/v1/admin/secret` - Regenerate bot secret
- `POST /api/v1/admin/test` - Test bot configuration

#### Public API
- `GET /api/v1/status` - Get bot status
- `POST /webhook` - Receive webhook from Talk
- `GET /webhook/status` - Check webhook status

#### OCS API
- `GET /api/v1/bot/info` - Get bot information (OCS format)

### Dependencies

- **Nextcloud** 28+ (PHP 8.1+)
- **Talk App** (spreed) - Required for chat functionality
- **PHP Extensions** - json, openssl for webhook signatures

## Configuration Examples

### Basic Setup

```php
// Enable bot
$config->setBotEnabled(true);
$config->setBotName('MyBot');
$config->setBotDescription('Helpful assistant for our team');
$config->setCommandPrefix('!');
$config->setResponseMode('hybrid');
$config->setBotFeatures(['webhook', 'response', 'event']);
```

### Webhook Configuration

```bash
# Register bot with Talk (using occ commands)
php occ talk:bot:install "MyBot" "your-secret-key-here" "https://yourserver.com/apps/nextcloud_talk_bot/webhook" "Team assistant bot"

# Enable in conversations
php occ talk:bot:setup 1 conversation-token
```

## Troubleshooting

### Common Issues

1. **Bot not responding**
   - Check if bot is enabled in admin settings
   - Verify Talk app is installed and working
   - Check Nextcloud logs for errors

2. **Commands not working**
   - Verify command prefix in settings
   - Check if message starts with correct prefix
   - Ensure bot name doesn't conflict with usernames

3. **Webhook issues**
   - Check webhook URL accessibility
   - Verify bot secret configuration
   - Review webhook logs in admin panel

### Logs

Check logs in:
- Nextcloud admin panel → Logging
- Admin settings → Talk Bot → Debug logs

### Support

- **Issues** - Report bugs on GitHub
- **Documentation** - Check Nextcloud developer docs
- **Community** - Ask questions in Nextcloud forums

## License

AGPL-3.0-or-later

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

Please follow Nextcloud coding standards and include appropriate tests.

---

**Note**: This app is designed for development and testing. Review security implications before using in production environments. 