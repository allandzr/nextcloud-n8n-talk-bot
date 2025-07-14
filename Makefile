# Makefile for Nextcloud Talk Bot

.PHONY: help install enable disable test clean lint fix-lint docs

APP_NAME=nextcloud_talk_bot
NEXTCLOUD_DIR=../../..
OCC=$(NEXTCLOUD_DIR)/occ

help: ## Show help message
	@echo "Nextcloud Talk Bot - Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies (if any)
	@echo "Installing dependencies..."
	@if [ -f composer.json ]; then composer install --no-dev; fi
	@if [ -f package.json ]; then npm ci --production; fi

enable: ## Enable the app in Nextcloud
	@echo "Enabling Talk Bot app..."
	@cd $(NEXTCLOUD_DIR) && php $(OCC) app:enable $(APP_NAME)

disable: ## Disable the app in Nextcloud
	@echo "Disabling Talk Bot app..."
	@cd $(NEXTCLOUD_DIR) && php $(OCC) app:disable $(APP_NAME)

test: ## Run tests
	@echo "Running tests..."
	@if [ -f composer.json ] && grep -q "phpunit" composer.json; then \
		composer test; \
	else \
		echo "No tests configured"; \
	fi

clean: ## Clean build artifacts
	@echo "Cleaning build artifacts..."
	@rm -rf vendor/
	@rm -rf node_modules/
	@rm -rf build/
	@rm -f *.tar.gz

lint: ## Run code linting
	@echo "Running PHP linting..."
	@if command -v php-cs-fixer >/dev/null 2>&1; then \
		php-cs-fixer fix --dry-run --diff; \
	else \
		echo "php-cs-fixer not found, skipping PHP linting"; \
	fi
	@echo "Running Psalm static analysis..."
	@if command -v psalm >/dev/null 2>&1; then \
		psalm --show-info=false; \
	else \
		echo "psalm not found, skipping static analysis"; \
	fi

fix-lint: ## Fix code style issues
	@echo "Fixing PHP code style..."
	@if command -v php-cs-fixer >/dev/null 2>&1; then \
		php-cs-fixer fix; \
	else \
		echo "php-cs-fixer not found, cannot fix code style"; \
	fi

docs: ## Generate documentation
	@echo "Generating documentation..."
	@if command -v phpdoc >/dev/null 2>&1; then \
		phpdoc -d lib/ -t docs/api/; \
	else \
		echo "phpdoc not found, skipping documentation generation"; \
	fi

status: ## Show app status
	@echo "Talk Bot app status:"
	@cd $(NEXTCLOUD_DIR) && php $(OCC) app:list | grep -E "($(APP_NAME)|spreed)" || true

logs: ## Show recent logs
	@echo "Recent Nextcloud logs:"
	@cd $(NEXTCLOUD_DIR) && tail -20 data/nextcloud.log | grep -i "talk\|bot" || echo "No bot-related logs found"

config: ## Show bot configuration
	@echo "Bot configuration:"
	@cd $(NEXTCLOUD_DIR) && php $(OCC) config:app:get $(APP_NAME) || echo "App not configured yet"

reset-config: ## Reset bot configuration
	@echo "Resetting bot configuration..."
	@cd $(NEXTCLOUD_DIR) && php $(OCC) config:app:delete $(APP_NAME) --output=json

talk-status: ## Show Talk app status
	@echo "Talk app status:"
	@cd $(NEXTCLOUD_DIR) && php $(OCC) app:list | grep spreed || echo "Talk app not found"

setup-dev: install enable ## Setup for development
	@echo "Setting up development environment..."
	@echo "✅ Dependencies installed"
	@echo "✅ App enabled"
	@echo ""
	@echo "🚀 Development setup complete!"
	@echo ""
	@echo "Next steps:"
	@echo "1. Configure bot in Admin Settings → Additional → Nextcloud Talk Bot"
	@echo "2. Test with: make test"
	@echo "3. Check status with: make status"

package: clean ## Create release package
	@echo "Creating release package..."
	@mkdir -p build
	@tar -czf build/$(APP_NAME).tar.gz \
		--exclude='build' \
		--exclude='.git*' \
		--exclude='node_modules' \
		--exclude='vendor' \
		--exclude='*.log' \
		--exclude='Makefile' \
		.
	@echo "Package created: build/$(APP_NAME).tar.gz"

install-tools: ## Install development tools
	@echo "Installing development tools..."
	@if ! command -v composer >/dev/null 2>&1; then \
		echo "Please install Composer first: https://getcomposer.org/"; \
		exit 1; \
	fi
	@composer global require friendsofphp/php-cs-fixer
	@composer global require vimeo/psalm
	@echo "Development tools installed"

check: lint test ## Run all checks (lint + test)
	@echo "✅ All checks completed"

# Development shortcuts
dev-install: setup-dev ## Alias for setup-dev
dev-reset: disable clean enable ## Reset development environment
dev-logs: logs ## Show development logs

# Help is default target
.DEFAULT_GOAL := help 