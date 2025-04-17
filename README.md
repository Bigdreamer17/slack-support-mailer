Slack Support Mailer
====================

A simple yet powerful PHP-based bot to manage support emails via Slack. Incoming support emails are automatically posted into a Slack channel, and replies from Slack threads are sent back as email responses through SMTP.

📌 How It Works
---------------

### 📥 Incoming Emails → Slack

*   When customers send an email to your support address, a webhook (email-to-slack.php) receives this email.
    
*   The email content is instantly forwarded to your specified Slack channel.
    
*   Each new email creates a unique Slack thread for clear and organized discussions.
    

### 📤 Slack Replies → Outgoing Emails

*   Replies within a Slack thread automatically trigger slack-events.php.
    
*   The bot matches Slack threads to customer email addresses.
    
*   Replies from the Slack thread are sent back to the original sender via SMTP.
    

🛠️ Setup Instructions
----------------------

### 1️⃣ Clone the Repository

```bash   

git clone https://github.com/yourusername/slack-support-mailer.git
cd slack-support-mailer
composer install

```

### 2️⃣ Configure the Environment

Copy .env.example to .env and update it:
```bash
SLACK_TOKEN=your-slack-bot-token
SLACK_CHANNEL_ID=your-channel-id
SLACK_BOT_USER_ID=your-slack-bot-user-id

SMTP_HOST=smtp.your-smtp-provider.com  SMTP_PORT=587  SMTP_SECURE=tls  SMTP_USER=your-smtp-username  SMTP_PASS=your-smtp-password  MAIL_FROM=support@yourdomain.com  MAIL_FROM_NAME="Your Support Team"   
```

### 3️⃣ Slack App Configuration

*   Go to [Slack API](https://api.slack.com/apps) and create a new Slack App.
    
*   Enable Event Subscriptions and set the Request URL to your publicly accessible slack-events.php endpoint.
    
*   Subscribe to the message.channels event.
    
*   Install the app into your workspace and note the OAuth Token and Bot User ID.
    

### 4️⃣ Deploy to Server

*   Upload files to your web server.
    
*   Ensure the logs directory is writable.
    

### 5️⃣ Test the Workflow

*   Send a test email to your support inbox.
    
*   Verify it appears in Slack.
    
*   Reply in Slack and confirm the sender receives your reply as an email.
    

📁 Project Structure
--------------------

```bash
slack-support-mailer  ├── logs                  # Directory for log files (excluded from git)
                      ├── .env.example          # Template for environment variables
                      ├── composer.json         # Composer dependencies
                      ├── composer.lock         # Composer lockfile
                      ├── email-to-slack.php    # Webhook script to send emails into Slack
                      ├── slack-events.php      # Endpoint script to handle Slack thread replies
                      └── .gitignore   
```

📦 Dependencies
---------------

*   **PHP 7.4+**
    
*   **PHPMailer**
    
*   **Dotenv** (vlucas/phpdotenv)
    

Install dependencies via composer:
```bash
composer install   

```

📝 Notes
--------

*   Logs and mappings are stored as .json files in the logs directory.
    
*   SMTP credentials must be valid and accessible from your hosting environment.
