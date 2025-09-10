# MythicalDash
# MythicalDash Build System
# ==========================================

# Directory configurations
FRONTEND_DIR = frontend
BACKEND_DIR = backend

# Commands
YARN = yarn
NPM = npm
PHP = php
COMPOSER = COMPOSER_ALLOW_SUPERUSER=1 composer
SED = sed

# Colors and formatting
RED = \033[0;31m
GREEN = \033[0;32m
YELLOW = \033[1;33m
BLUE = \033[0;34m
PURPLE = \033[0;35m
CYAN = \033[0;36m
WHITE = \033[1;37m
BOLD = \033[1m
NC = \033[0m

# Emoji indicators
CHECK = ✓
WARN = ⚠
INFO = ℹ
ROCKET = 🚀
CLEAN = 🧹
PACKAGE = 📦
BUILD = 🔨
SERVER = 🌐
PROD = 🛡️
DEV = 🔍

# Make sure we use bash
SHELL := /bin/bash

.PHONY: help frontend backend dev release install clean test set-prod set-dev make-release-package

# Default target
help:
	@echo -e "${BOLD}${BLUE}MythicalDash Development System${NC}"
	@echo -e "${CYAN}================================${NC}\n"
	@echo -e "${BOLD}Available commands:${NC}"
	@echo -e "  ${GREEN}make frontend${NC}    ${ROCKET} Builds the frontend for production"
	@echo -e "  ${GREEN}make backend${NC}     ${BUILD} Builds the backend components"
	@echo -e "  ${GREEN}make release${NC}     ${PACKAGE} Prepares a full release build"
	@echo -e "  ${GREEN}make clean${NC}       ${CLEAN} Cleans all build artifacts"
	@echo -e "  ${GREEN}make set-prod${NC}    ${PROD} Sets APP_DEBUG to false for production\n"
	@echo -e "  ${GREEN}make set-dev${NC}     ${DEV} Sets APP_DEBUG to true for development"
	@echo -e "  ${GREEN}make get-tools${NC}  ${TOOLS} Installs development tools (NVM, Yarn, PNPM)"
	@echo -e "  ${GREEN}make upgrade-core${NC} ${UPGRADE} Upgrades the core of MythicalDash"
	@echo -e "  ${GREEN}make release-package${NC} ${PACKAGE} Creates a release package MythicalDash.zip"
	@echo -e "${YELLOW}Use 'make <command>' to execute a command${NC}\n"

# Frontend tasks
frontend:
	@echo -e "\n${BOLD}${BLUE}Frontend Build${NC} ${ROCKET}"
	@echo -e "${CYAN}=================${NC}"
	@echo -e "${GREEN}${INFO} Building frontend for production...${NC}"
	@cd $(FRONTEND_DIR) && $(YARN) build
	@echo -e "${GREEN}${CHECK} Frontend build complete!${NC}\n"

# Backend tasks
backend:
	@echo -e "\n${BOLD}${BLUE}Backend Build${NC} ${BUILD}"
	@echo -e "${CYAN}=================${NC}"
	@echo -e "${GREEN}${INFO} Building backend components...${NC}"
	@cd $(BACKEND_DIR) && $(COMPOSER) install
	@cd $(BACKEND_DIR) && $(COMPOSER) dump-autoload
	@echo -e "${GREEN}${CHECK} Backend build complete!${NC}\n"

clean-license:
	@echo -e "\n${BOLD}${BLUE}Cleaning License${NC} ${CLEAN}"
	@echo -e "${CYAN}=======================${NC}"
	@echo -e "${YELLOW}${WARN} Cleaning license...${NC}"
	@rm -rf /var/www/mythicaldash-v3/backend/storage/caches/licenses/*.json 
	@echo -e "${GREEN}${CHECK} License cleaned${NC}\n"

# Release build
release:
	@echo -e "\n${BOLD}${BLUE}Release Build${NC} ${ROCKET}"
	@echo -e "${CYAN}=================${NC}"
	@echo -e "${YELLOW}${WARN} Starting comprehensive release build...${NC}\n"
	@echo -e "${PURPLE}${INFO} Installing backend dependencies...${NC}"
	@cd $(BACKEND_DIR) && $(COMPOSER) install
	@echo -e "${GREEN}${CHECK} Backend dependencies installed${NC}\n"

	@echo -e "${PURPLE}${INFO} Installing frontend dependencies...${NC}" 
	@cd $(FRONTEND_DIR) && $(YARN)
	@echo -e "${GREEN}${CHECK} Frontend dependencies installed${NC}\n"

	@echo -e "${PURPLE}${INFO} Exporting permissions...${NC}"
	@php mythicaldash ExportPermissions
	@echo -e "${GREEN}${CHECK} Permissions exported${NC}\n"
	
	@echo -e "${PURPLE}${INFO} Frontend checks...${NC}"

	@cd $(BACKEND_DIR) && $(COMPOSER) run lint
	@cd $(FRONTEND_DIR) && $(YARN) format
	@echo -e "${GREEN}${CHECK} Frontend checks complete${NC}\n"
	
	@echo -e "${PURPLE}${INFO} Updating dependencies...${NC}"
	@cd $(FRONTEND_DIR) && $(YARN)
	@cd $(BACKEND_DIR) && $(COMPOSER) update
	@echo -e "${GREEN}${CHECK} Dependencies updated${NC}\n"
	
	@echo -e "${PURPLE}${INFO} Building applications...${NC}"
	@cd $(FRONTEND_DIR) && $(YARN) build
	@cd $(BACKEND_DIR) && $(COMPOSER) dump-autoload
	@cd $(BACKEND_DIR) && $(COMPOSER) install --optimize-autoloader
	@echo -e "${GREEN}${CHECK} Build complete${NC}\n"
	
	@echo -e "${GREEN}${ROCKET} Release build successful!${NC}\n"

# Create release package
release-package:
	@echo -e "\n${BOLD}${BLUE}Creating Release Package${NC} ${PACKAGE}"
	@echo -e "${CYAN}=========================${NC}"
	@$(MAKE) set-prod
	@$(MAKE) release
	@echo -e "${GREEN}${INFO} Creating MythicalDash.zip package...${NC}"
	@echo -e "${YELLOW}${WARN} Creating package with tracked files and frontend dist...${NC}"
	@rm -f MythicalDash.zip
	@git ls-files | zip MythicalDash.zip -@
	@zip -r MythicalDash.zip $(FRONTEND_DIR)/dist
	@echo -e "${GREEN}${CHECK} Release package created: MythicalDash.zip${NC}\n"
	@echo -e "${BOLD}${GREEN}🎉 Release package ready for distribution! 🎉${NC}\n"

lint: 
	@cd $(BACKEND_DIR) && $(COMPOSER) run lint
	@cd $(FRONTEND_DIR) && $(YARN) format

# Clean build artifacts
clean:
	@echo -e "\n${BOLD}${BLUE}Cleaning Artifacts${NC} ${CLEAN}"
	@echo -e "${CYAN}=======================${NC}"
	@echo -e "${YELLOW}${WARN} Removing artifacts and caches...${NC}"
	@cd $(FRONTEND_DIR) && rm -rf dist node_modules/
	@rm -rf $(BACKEND_DIR)/storage/packages/
	@echo -e "${GREEN}${CHECK} Clean complete!${NC}\n"

# Set production mode
set-prod:
	@echo -e "\n${BOLD}${BLUE}Setting Production Mode${NC} ${PROD}"
	@echo -e "${CYAN}=======================${NC}"
	@echo -e "${GREEN}${INFO} Setting APP_DEBUG to false...${NC}"
	@find $(BACKEND_DIR) -type f -name "*.php" -exec $(SED) -i 's/define('\''APP_DEBUG'\'', true);/define('\''APP_DEBUG'\'', false);/g' {} +
	@echo -e "${GREEN}${CHECK} Production mode set successfully!${NC}\n"

set-dev:
	@echo -e "\n${BOLD}${BLUE}Setting Development Mode${NC} ${DEV}"
	@echo -e "${CYAN}=======================${NC}"
	@echo -e "${GREEN}${INFO} Setting APP_DEBUG to true...${NC}"
	@find $(BACKEND_DIR) -type f -name "*.php" -exec $(SED) -i 's/define('\''APP_DEBUG'\'', false);/define('\''APP_DEBUG'\'', true);/g' {} +
	@echo -e "${GREEN}${CHECK} Development mode set successfully!${NC}\n"

get-tools: 
	@echo -e "\n${BOLD}${BLUE}Installing Development Tools${NC} ${TOOLS}"
	@echo -e "${CYAN}==========================${NC}"
	@echo -e "${GREEN}${INFO} Installing NVM (Node Version Manager)...${NC}"
	@curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
	@echo -e "${GREEN}${INFO} Setting up NVM environment...${NC}"
	@export NVM_DIR="$$([ -z "$${XDG_CONFIG_HOME-}" ] && printf %s "$${HOME}/.nvm" || printf %s "$${XDG_CONFIG_HOME}/nvm")"
	@[ -s "$$NVM_DIR/nvm.sh" ] && \. "$$NVM_DIR/nvm.sh" # This loads nvm
	@echo -e "${GREEN}${CHECK} Development tools installed successfully!${NC}\n"
	@echo -e "${YELLOW}${WARN} Please restart your terminal or run 'source ~/.bashrc' to use nvm${NC}\n"
	@echo -e "${GREEN}${INFO} Attempting to source NVM and install Node.js 24...${NC}"
	@bash -c 'export NVM_DIR="$$([ -z "$${XDG_CONFIG_HOME-}" ] && printf %s "$${HOME}/.nvm" || printf %s "$${XDG_CONFIG_HOME}/nvm")"; \
		[ -s "$$NVM_DIR/nvm.sh" ] && \. "$$NVM_DIR/nvm.sh"; \
		nvm install 24 && nvm use 24 && echo -e "${GREEN}${CHECK} Node.js 24 installed and activated${NC}"' || \
		echo -e "${YELLOW}${WARN} Could not automatically install Node.js 24. Please run 'nvm install 24 && nvm use 24' manually${NC}"
	@echo -e "${GREEN}${INFO} Installing Yarn package manager globally...${NC}"
	@bash -c 'export NVM_DIR="$$([ -z "$${XDG_CONFIG_HOME-}" ] && printf %s "$${HOME}/.nvm" || printf %s "$${XDG_CONFIG_HOME}/nvm")"; \
		[ -s "$$NVM_DIR/nvm.sh" ] && \. "$$NVM_DIR/nvm.sh"; \
		npm i -g yarn && echo -e "${GREEN}${CHECK} Yarn installed successfully${NC}"' || \
		echo -e "${YELLOW}${WARN} Could not install Yarn. Please run 'npm i -g yarn' manually${NC}"
	@echo -e "${GREEN}${INFO} Installing PNPM package manager globally (for future use)...${NC}"
	@bash -c 'export NVM_DIR="$$([ -z "$${XDG_CONFIG_HOME-}" ] && printf %s "$${HOME}/.nvm" || printf %s "$${XDG_CONFIG_HOME}/nvm")"; \
		[ -s "$$NVM_DIR/nvm.sh" ] && \. "$$NVM_DIR/nvm.sh"; \
		npm i -g pnpm && echo -e "${GREEN}${CHECK} PNPM installed successfully${NC}"' || \
		echo -e "${YELLOW}${WARN} Could not install PNPM. Please run 'npm i -g pnpm' manually${NC}"
	@echo -e "${GREEN}${INFO} Installing frontend packages with Yarn...${NC}"
	@bash -c 'export NVM_DIR="$$([ -z "$${XDG_CONFIG_HOME-}" ] && printf %s "$${HOME}/.nvm" || printf %s "$${XDG_CONFIG_HOME}/nvm")"; \
		[ -s "$$NVM_DIR/nvm.sh" ] && \. "$$NVM_DIR/nvm.sh"; \
		cd $(FRONTEND_DIR) && yarn && echo -e "${GREEN}${CHECK} Frontend packages installed successfully${NC}"' || \
		echo -e "${YELLOW}${WARN} Could not install frontend packages. Please run 'cd $(FRONTEND_DIR) && yarn' manually${NC}"
	@echo -e "${GREEN}${CHECK} All development tools installed successfully!${NC}\n"
	@echo -e "${GREEN}${INFO} Installing backend dependencies with Composer...${NC}"
	@cd $(BACKEND_DIR) && $(COMPOSER) install && echo -e "${GREEN}${CHECK} Backend dependencies installed successfully${NC}" || \
		echo -e "${YELLOW}${WARN} Could not install backend dependencies. Please run 'cd $(BACKEND_DIR) && composer install' manually${NC}"
	@echo -e "${BOLD}${GREEN}🎉 Tools are installed and you are ready to build MythicalDash! 🎉${NC}\n"
	
# Upgrade MythicalDash
upgrade-core:
	@echo -e "\n${BOLD}${BLUE}MythicalDash Upgrade Process${NC} ${ROCKET}"
	@echo -e "${CYAN}============================${NC}"
	@echo -e "${YELLOW}${WARN} This will upgrade MythicalDash to the latest version!${NC}"
	@echo -e "${YELLOW}${WARN} Make sure you have backed up your data before proceeding!${NC}\n"
	@read -p "Are you sure you want to continue? (y/N): " confirm && [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ] || exit 1
	@echo -e "\n${GREEN}${INFO} Step 1: Creating backup...${NC}"
	@mariadb-dump -p mythicaldash_remastered > mythicaldash_backup.sql && echo -e "${GREEN}${CHECK} Database backup created: mythicaldash_backup.sql${NC}" || echo -e "${RED}${CROSS} Failed to create database backup${NC}"
	@cd /var/www && zip -r mythicaldash_backup.zip mythicaldash-v3/ && echo -e "${GREEN}${CHECK} File system backup created: /var/www/mythicaldash_backup.zip${NC}" || echo -e "${RED}${CROSS} Failed to create file system backup${NC}"
	@echo -e "\n${GREEN}${INFO} Step 2: Downloading latest release...${NC}"
	@curl -Lo MythicalDash.zip https://github.com/MythicalLTD/MythicalDash/releases/latest/download/MythicalDash.zip && echo -e "${GREEN}${CHECK} Latest release downloaded${NC}" || (echo -e "${RED}${CROSS} Failed to download latest release${NC}" && exit 1)
	@echo -e "\n${GREEN}${INFO} Step 3: Extracting update files...${NC}"
	@unzip -o MythicalDash.zip -d /var/www/mythicaldash-v3 && echo -e "${GREEN}${CHECK} Files extracted successfully${NC}" || (echo -e "${RED}${CROSS} Failed to extract files${NC}" && exit 1)
	@rm -f MythicalDash.zip && echo -e "${GREEN}${CHECK} Cleanup: Removed zip file${NC}"
	@echo -e "\n${GREEN}${INFO} Step 4: Updating backend dependencies...${NC}"
	@cd $(BACKEND_DIR) && COMPOSER_ALLOW_SUPERUSER=1 $(COMPOSER) install --no-dev --optimize-autoloader && echo -e "${GREEN}${CHECK} Backend dependencies updated${NC}" || echo -e "${RED}${CROSS} Failed to update backend dependencies${NC}"
	@echo -e "\n${GREEN}${INFO} Step 5: Running database migrations...${NC}"
	@php mythicaldash migrate && echo -e "${GREEN}${CHECK} Database migrations completed${NC}" || echo -e "${RED}${CROSS} Database migrations failed${NC}"
	@echo -e "\n${GREEN}${INFO} Step 6: Setting proper file permissions...${NC}"
	@chown -R www-data:www-data /var/www/mythicaldash-v3/* && echo -e "${GREEN}${CHECK} File permissions set${NC}" || echo -e "${YELLOW}${WARN} Could not set file permissions (you may need to run as root)${NC}"
	@echo -e "\n${GREEN}${INFO} Step 7: Setting production mode...${NC}"
	@$(MAKE) set-prod
	@echo -e "\n${BOLD}${GREEN}🎉 Upgrade completed successfully! 🎉${NC}"
	@echo -e "${GREEN}${CHECK} MythicalDash has been upgraded to the latest version${NC}"
	@echo -e "${CYAN}${INFO} Backup files created:${NC}"
	@echo -e "  - Database: mythicaldash_backup.sql"
	@echo -e "  - Files: /var/www/mythicaldash_backup.zip\n"
