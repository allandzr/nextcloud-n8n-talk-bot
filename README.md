# n8n Talk Bot for Nextcloud

🤖 **Intelligent bot for automating communication through n8n workflows**

A Nextcloud Talk bot that forwards all chat messages to external n8n workflows and returns automated responses. Perfect for creating intelligent chatbots, customer support automation, and workflow triggers directly from your Nextcloud Talk conversations.

## ✨ Features

- **🔄 n8n Integration**: Forward all messages to n8n webhooks for processing
- **🤖 Automated Responses**: Receive and display responses from n8n workflows  
- **⚙️ Admin Configuration**: Easy setup through Nextcloud admin panel
- **💬 Single Command**: `/about` command shows bot info and configuration
- **🛡️ Error Handling**: Graceful fallback when n8n is unavailable
- **📊 Status Monitoring**: Real-time configuration display

## 🚀 Installation

### Method 1: From GitHub Release

1. Download the latest release from [GitHub Releases](https://github.com/allandzr/nextcloud-n8n-talk-not/releases)
2. Extract to your Nextcloud apps directory:
   ```bash
   cd /path/to/nextcloud/apps-extra/
   tar -xzf n8n-talk-bot.tar.gz
   ```
3. Enable the app:
   ```bash
   sudo -u www-data php occ app:enable n8n-talk-bot
   ```

### Method 2: From Source

1. Clone the repository:
   ```bash
   cd /path/to/nextcloud/apps-extra/
   git clone https://github.com/allandzr/nextcloud-n8n-talk-not.git n8n-talk-bot
   ```
2. Enable the app:
   ```bash
   sudo -u www-data php occ app:enable n8n-talk-bot
   ```

## ⚙️ Configuration

1. **Access Admin Panel**: Go to **Settings → Administration → n8n Talk Bot**
2. **Enable the Bot**: Check "Enable Talk Bot"
3. **Configure n8n Integration**:
   - Check "Enable n8n Integration"  
   - Enter your n8n webhook URL
4. **Save Settings**

### Example n8n Webhook URL:
```
https://your-n8n-instance.com/webhook/your-webhook-id
```

## 🔌 n8n Workflow Setup

### Request Format (sent to n8n):
```json
{
  "messageId": "message_123",
  "conversationToken": "conversation_abc123",
  "userId": "user_456",
  "userDisplayName": "John Doe", 
  "message": "Hello, how are you?",
  "timestamp": "2025-01-14T20:06:25Z",
  "messageType": "chat"
}
```

### Response Format (from n8n):
```json
{
  "success": true,
  "response": "Hello! I'm doing great, thanks for asking!",
  "shouldReply": true
}
```

**Alternative response format:**
```json
{
  "message": "Your workflow response here"
}
```

## 🎯 Usage

### For Users:
- Type `/about` in any Talk conversation to see bot info and configuration
- All other messages are automatically forwarded to n8n workflows (when enabled)
- Receive automated responses from your n8n workflows

### For Administrators:
- Configure bot settings in **Settings → Administration → n8n Talk Bot**
- Monitor bot status and n8n integration health
- Enable/disable features as needed

## 🛠️ Development

### Requirements:
- Nextcloud 25.0+
- PHP 8.0+
- Nextcloud Talk app enabled

### Project Structure:
```
n8n-talk-bot/
├── appinfo/
│   ├── info.xml              # App metadata
│   └── routes.php            # API routes
├── lib/
│   ├── Bot/                  # Bot logic
│   ├── Controller/           # API controllers  
│   ├── Listener/             # Event listeners
│   ├── Service/              # Core services
│   └── Settings/             # Admin settings
├── templates/
│   └── admin-settings.php    # Admin panel template
├── js/
│   └── admin-settings.js     # Frontend JavaScript
└── css/
    └── admin-settings.css    # Styles
```

### Local Development:
```bash
# Clone for development
git clone https://github.com/allandzr/nextcloud-n8n-talk-not.git
cd nextcloud-n8n-talk-not

# Enable in development environment
docker-compose exec nextcloud php occ app:enable n8n-talk-bot
```

## 🔧 API Endpoints

- `POST /apps/n8n-talk-bot/admin/config` - Save bot configuration
- `POST /apps/n8n-talk-bot/webhook/receive` - Receive external webhooks
- `GET /apps/n8n-talk-bot/api/status` - Get bot status

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the AGPL-3.0 License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: Check this README and inline code comments
- **Issues**: Report bugs on [GitHub Issues](https://github.com/allandzr/nextcloud-n8n-talk-not/issues)
- **Discussions**: Ask questions in [GitHub Discussions](https://github.com/allandzr/nextcloud-n8n-talk-not/discussions)

## 🙏 Acknowledgments

- [Nextcloud](https://nextcloud.com/) for the amazing platform
- [n8n](https://n8n.io/) for workflow automation
- Nextcloud Talk team for the chat platform

---

**Made with ❤️ for the Nextcloud community**
