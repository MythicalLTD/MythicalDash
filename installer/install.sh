#!/bin/bash
# MythicalDash Docker Installation Script
# Docker-only installer/uninstaller for Ubuntu/Debian

if [ "$EUID" -ne 0 ]; then
	echo -e "${RED}This installer must be run as root or with sudo.${NC}"
	echo "Please run: sudo $0"
	exit 1
fi

# Parse command-line arguments
SKIP_OS_CHECK=false
FORCE_ARM=false
SKIP_INSTALL_CHECK=false
SKIP_VIRT_CHECK=false
SKIP_SYSTEM_UPDATE=false
USE_DEV=false
DEV_BRANCH=""
DEV_SHA=""

while [[ $# -gt 0 ]]; do
	case $1 in
	--skip-os-check)
		SKIP_OS_CHECK=true
		shift
		;;
	--force-arm)
		FORCE_ARM=true
		shift
		;;
	--skip-install-check)
		SKIP_INSTALL_CHECK=true
		shift
		;;
	--skip-virt-check)
		SKIP_VIRT_CHECK=true
		shift
		;;
	--skip-system-update)
		SKIP_SYSTEM_UPDATE=true
		shift
		;;
	--dev)
		USE_DEV=true
		shift
		;;
	--dev-branch)
		USE_DEV=true
		DEV_BRANCH="$2"
		shift 2
		;;
	--dev-sha)
		USE_DEV=true
		DEV_SHA="$2"
		shift 2
		;;
	--help | -h)
		echo "MythicalDash Installer"
		echo ""
		echo "Usage: $0 [OPTIONS]"
		echo ""
		echo "Options:"
		echo "  --skip-os-check        Skip OS version compatibility checks"
		echo "  --force-arm            Bypass ARM architecture warnings and checks"
		echo "  --skip-install-check   Skip check for existing installation"
		echo "  --skip-virt-check      Skip virtualization compatibility checks"
		echo "  --skip-system-update   Skip apt update/upgrade and essential package installation"
		echo "  --dev                  Use latest dev release images"
		echo "  --dev-branch BRANCH    Use dev images for specific branch (e.g., main, develop)"
		echo "  --dev-sha SHA          Use dev images for specific commit SHA (requires --dev-branch)"
		echo "  --help, -h             Show this help message"
		echo ""
		echo "Dev Release Examples:"
		echo "  $0 --dev                           # Latest dev release"
		echo "  $0 --dev-branch main                # Dev images from main branch"
		echo "  $0 --dev-branch main --dev-sha abc1234  # Specific commit from main branch"
		echo ""
		echo "Warning: Using --skip-os-check, --force-arm, --skip-install-check, --skip-virt-check, or --skip-system-update"
		echo "may result in unsupported configurations. Use at your own risk."
		echo ""
		echo "Warning: Dev releases are development builds and may be unstable."
		exit 0
		;;
	*)
		echo "Unknown option: $1"
		echo "Use --help for usage information"
		exit 1
		;;
	esac
done

LOG_DIR=/var/www/mythicaldash
LOG_FILE=$LOG_DIR/install.log
BACKUP_DIR="/var/www/mythicaldash/backups"

# Colors (use real ANSI escapes)
NC=$'\033[0m'
RED=$'\033[0;31m'
GREEN=$'\033[0;32m'
YELLOW=$'\033[0;33m'
BLUE=$'\033[0;34m'
CYAN=$'\033[0;36m'
BOLD=$'\033[1m'

log_init() {
	sudo mkdir -p "$LOG_DIR"
	sudo touch "$LOG_FILE"
	sudo chmod 664 "$LOG_FILE" 2>/dev/null || true
	{
		echo "========================================"
		date '+%Y-%m-%d %H:%M:%S %Z' | sed 's/^/[START] /'
		echo "Script: MythicalDash Installer"
		echo "========================================"
	} >>"$LOG_FILE" 2>&1
}

log_info() {
	echo -e "${BLUE}[INFO]${NC} $1"
	echo "[INFO] $1" >>"$LOG_FILE"
}
log_success() {
	echo -e "${GREEN}[ OK ]${NC} $1"
	echo "[OK] $1" >>"$LOG_FILE"
}
log_warn() {
	echo -e "${YELLOW}[WARN]${NC} $1"
	echo "[WARN] $1" >>"$LOG_FILE"
}
log_error() {
	echo -e "${RED}[FAIL]${NC} $1"
	echo "[ERROR] $1" >>"$LOG_FILE"
}
log_step() {
	echo -e "${CYAN}${BOLD}==> $1${NC}"
	echo "[STEP] $1" >>"$LOG_FILE"
}

run_with_spinner() {
	local start_message="$1"
	local success_message="$2"
	shift 2
	local show_elapsed="false"
	if [ "${1:-}" = "true" ] || [ "${1:-}" = "false" ]; then
		show_elapsed="$1"
		shift 1
	fi

	log_step "$start_message"

	"$@" >>"$LOG_FILE" 2>&1 &
	local cmd_pid=$!
	local spinner="|/-\\"
	local i=0
	local start_ts=$(date +%s)

	if [ -t 1 ]; then
		printf '  '
	fi

	while kill -0 "$cmd_pid" >/dev/null 2>&1; do
		if [ -t 1 ]; then
			local elapsed_str=""
			if [ "$show_elapsed" = "true" ]; then
				local elapsed=$(($(date +%s) - start_ts))
				if [ $elapsed -ge 60 ]; then
					local mins=$((elapsed / 60))
					local secs=$((elapsed % 60))
					elapsed_str=" (${mins}m ${secs}s)"
				fi
			fi
			printf '\r[%c] %s%s   ' "${spinner:i%${#spinner}:1}" "$start_message" "$elapsed_str"
		fi
		i=$(((i + 1) % ${#spinner}))
		sleep 0.15
	done

	wait "$cmd_pid"
	local exit_code=$?

	if [ -t 1 ]; then
		printf '\r\033[K'
	fi

	if [ $exit_code -eq 0 ]; then
		log_success "$success_message"
		return 0
	fi

	log_error "$start_message failed. Check $LOG_FILE for details."
	return $exit_code
}

support_hint() {
	echo -e "${YELLOW}Need help?${NC} Join Discord: ${BLUE}https://discord.mythical.systems${NC}  Docs: ${BLUE}https://docs.mythical.systems${NC}"
}

upload_logs_on_fail() {
	if command -v curl >/dev/null 2>&1; then
		log_info "Uploading logs to mclo.gs for diagnostics..."
		RESPONSE=$(curl -s -X POST --data-urlencode "content@${LOG_FILE}" "https://api.mythicaldash.com/1/log")

		# Parse JSON response
		SUCCESS=$(echo "$RESPONSE" | grep -o '"success":[^,]*' | cut -d':' -f2 | tr -d '"' 2>/dev/null)
		if [ "$SUCCESS" = "true" ]; then
			URL=$(echo "$RESPONSE" | grep -o '"url":"[^"]*"' | cut -d'"' -f4 | sed 's/\\\//\//g' 2>/dev/null)
			RAW=$(echo "$RESPONSE" | grep -o '"raw":"[^"]*"' | cut -d'"' -f4 | sed 's/\\\//\//g' 2>/dev/null)
			log_success "Logs uploaded successfully!"
			echo -e "${GREEN}Logs URL:${NC} $URL"
			echo -e "${GREEN}Raw URL:${NC} $RAW"
		else
			log_warn "Failed to upload logs. Response: $RESPONSE"
		fi
	else
		log_warn "curl not available; cannot upload logs automatically."
	fi
	support_hint
}

# Initialize logging before any traps or operations use it
log_init

trap 'log_error "An unexpected error occurred."; upload_logs_on_fail' ERR
set -o pipefail

print_banner() {
	echo -e "${CYAN}${BOLD}MythicalDash${NC}"
	echo -e "${CYAN}${BOLD}Script Version: ${BLUE}2.0.1${NC}"
	echo -e "${CYAN}${BOLD}┌────────────────────────────────────────────────────────────┐${NC}"
	echo -e "${CYAN}${BOLD}${NC}  🌐 Website:  ${BLUE}www.mythical.systems${NC}           ${CYAN}${BOLD}${NC}"
	echo -e "${CYAN}${BOLD}${NC}  💻 Github:   ${BLUE}github.com/mythicalltd/mythicaldash${NC}    ${CYAN}${BOLD}${NC}"
	echo -e "${CYAN}${BOLD}${NC}  💬 Discord:  ${BLUE}discord.mythical.systems${NC}                ${CYAN}${BOLD}${NC}"
	echo -e "${CYAN}${BOLD}${NC}  📚 Docs:     ${BLUE}docs.mythical.systems${NC}                   ${CYAN}${BOLD}${NC}"
	echo -e "${CYAN}${BOLD}└────────────────────────────────────────────────────────────┘${NC}"
}

draw_hr() {
	echo -e "${CYAN}────────────────────────────────────────────────────────${NC}"
}

# Helper: detect public IPv4 and IPv6 for DNS setup (forces correct protocol, trims output)
detect_public_ips() {
	PUBLIC_IPV4=$(curl -4 -s --max-time 10 ifconfig.me 2>/dev/null || curl -4 -s --max-time 10 ipinfo.io/ip 2>/dev/null || true | tr -d '\r\n' | xargs || true)
	PUBLIC_IPV6=$(curl -6 -s --max-time 10 ifconfig.co 2>/dev/null | tr -d '\r\n' | xargs || true)
	# Validate: A record must be IPv4 (no colons), AAAA must be IPv6 (has colons)
	[[ -n "$PUBLIC_IPV4" && "$PUBLIC_IPV4" == *:* ]] && PUBLIC_IPV4=""
	[[ -n "$PUBLIC_IPV6" && "$PUBLIC_IPV6" != *:* ]] && PUBLIC_IPV6=""
}

# Helper: show DNS setup instructions (domain in $1, e.g. $domain or $panel_domain)
show_dns_setup_instructions() {
	local dns_domain="$1"
	echo -e "${BOLD}${YELLOW}DNS Setup Required${NC}"
	draw_hr
	echo -e "${BLUE}Before creating the SSL certificate, you must create DNS records:${NC}"
	echo ""
	if [ -n "$PUBLIC_IPV4" ]; then
		echo -e "${GREEN}Create an A record (IPv4):${NC}"
		echo -e "  ${BOLD}Name:${NC} $dns_domain"
		echo -e "  ${BOLD}Value:${NC} $PUBLIC_IPV4"
		echo -e "  ${BOLD}TTL:${NC} 300 (or Auto)"
		echo ""
	fi
	if [ -n "$PUBLIC_IPV6" ]; then
		echo -e "${GREEN}Create an AAAA record (IPv6):${NC}"
		echo -e "  ${BOLD}Name:${NC} $dns_domain"
		echo -e "  ${BOLD}Value:${NC} $PUBLIC_IPV6"
		echo -e "  ${BOLD}TTL:${NC} 300 (or Auto)"
		echo ""
	fi
	if [ -z "$PUBLIC_IPV4" ] && [ -z "$PUBLIC_IPV6" ]; then
		echo -e "${YELLOW}Could not detect your server's public IP. Create the appropriate DNS record manually.${NC}"
		echo ""
	else
		[ -z "$PUBLIC_IPV4" ] && echo -e "${YELLOW}IPv4 not detected (IPv6-only server). Create an AAAA record.${NC}" && echo ""
		[ -z "$PUBLIC_IPV6" ] && echo -e "${YELLOW}IPv6 not detected. An A record (IPv4) is sufficient.${NC}" && echo ""
	fi
	echo -e "${YELLOW}Please create these DNS records in your domain's DNS management panel.${NC}"
	echo -e "${YELLOW}DNS propagation can take 5-60 minutes depending on your DNS provider.${NC}"
	echo ""
}

# Helper function for centered, pretty messages
print_centered() {
	local text="$1"
	local color="${2:-$CYAN}"
	local width=60
	local padding=$(((width - ${#text}) / 2))
	printf "%*s${color}${BOLD}%s${NC}\n" $padding "" "$text"
}

print_info_box() {
	local title="$1"
	shift
	local messages=("$@")

	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "$title" "$YELLOW"
	draw_hr
	echo ""
	for msg in "${messages[@]}"; do
		echo -e "  ${BLUE}${msg}${NC}"
	done
	echo ""
	draw_hr
}

show_main_menu() {
	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	echo -e "${BOLD}Choose a component:${NC}"
	echo -e "  ${GREEN}[1]${NC} ${BOLD}Panel${NC} ${BLUE}(Web Interface)${NC}"
	echo -e "  ${YELLOW}[2]${NC} ${BOLD}SSL Certificates${NC} ${BLUE}(Let's Encrypt)${NC}"
	draw_hr
}

show_panel_menu() {
	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "Panel Operations" "$CYAN"
	draw_hr
	echo ""
	echo -e "  ${GREEN}${BOLD}[1]${NC} ${BOLD}Install Panel${NC}"
	echo -e "     ${BLUE}→ Install MythicalDash web interface using Docker${NC}"
	echo -e "     ${BLUE}→ Choose access method (Cloudflare Tunnel, Nginx, Apache, Direct)${NC}"
	echo -e "     ${BLUE}→ Choose release type (Stable Release or Development Build)${NC}"
	echo ""
	echo -e "  ${RED}${BOLD}[2]${NC} ${BOLD}Uninstall Panel${NC}"
	echo -e "     ${YELLOW}⚠️  WARNING: This will remove all Panel data and containers${NC}"
	echo -e "     ${BLUE}→ Stops and removes Docker containers${NC}"
	echo -e "     ${BLUE}→ Removes installation files and configuration${NC}"
	echo ""
	echo -e "  ${YELLOW}${BOLD}[3]${NC} ${BOLD}Update Panel${NC}"
	echo -e "     ${BLUE}→ Pull latest Docker images${NC}"
	echo -e "     ${BLUE}→ Restart containers with new version${NC}"
	echo -e "     ${BLUE}→ Switch between release and dev builds${NC}"
	echo ""
	echo -e "  ${CYAN}${BOLD}[4]${NC} ${BOLD}Backup Manager${NC}"
	echo -e "     ${BLUE}→ Create, list, restore, and manage backups${NC}"
	echo -e "     ${BLUE}→ Backup database, volumes, and configuration${NC}"
	echo -e "     ${BLUE}→ Export/Import for migrating to another server${NC}"
	echo ""
	draw_hr
}

show_backup_menu() {
	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "Backup Manager" "$CYAN"
	draw_hr
	echo ""
	echo -e "  ${GREEN}${BOLD}[1]${NC} ${BOLD}Create Backup${NC}"
	echo -e "     ${BLUE}→ Create a full backup of Panel data and configuration${NC}"
	echo -e "     ${BLUE}→ Includes database, volumes, and config files${NC}"
	echo ""
	echo -e "  ${BLUE}${BOLD}[2]${NC} ${BOLD}List Backups${NC}"
	echo -e "     ${BLUE}→ View all available backups${NC}"
	echo -e "     ${BLUE}→ Shows backup size, date, and details${NC}"
	echo ""
	echo -e "  ${YELLOW}${BOLD}[3]${NC} ${BOLD}Restore Backup${NC}"
	echo -e "     ${YELLOW}⚠️  WARNING: This will replace current data with backup${NC}"
	echo -e "     ${BLUE}→ Restore Panel from a previous backup${NC}"
	echo -e "     ${BLUE}→ Stops containers, restores data, then restarts${NC}"
	echo ""
	echo -e "  ${RED}${BOLD}[4]${NC} ${BOLD}Delete Backup${NC}"
	echo -e "     ${YELLOW}⚠️  WARNING: This will permanently delete the backup${NC}"
	echo -e "     ${BLUE}→ Remove a backup file from disk${NC}"
	echo ""
	echo -e "  ${CYAN}${BOLD}[5]${NC} ${BOLD}Export for Migration${NC}"
	echo -e "     ${BLUE}→ Create migration package to move Panel to another server${NC}"
	echo -e "     ${BLUE}→ Includes all data, config, and transfer instructions${NC}"
	echo ""
	echo -e "  ${GREEN}${BOLD}[6]${NC} ${BOLD}Import Migration${NC}"
	echo -e "     ${YELLOW}⚠️  WARNING: This will replace current installation${NC}"
	echo -e "     ${BLUE}→ Import Panel data from another server${NC}"
	echo -e "     ${BLUE}→ Restores complete Panel installation from migration package${NC}"
	echo ""
	draw_hr
}

show_release_type_menu() {
	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	echo -e "${BOLD}Choose release type:${NC}"
	echo -e "  ${GREEN}[1]${NC} ${BOLD}Stable Release${NC} ${BLUE}(Recommended for production)${NC}"
	echo -e "     ${BLUE}→ Latest stable, tested release${NC}"
	echo -e "     ${BLUE}→ Best for production environments${NC}"
	echo ""
	echo -e "  ${YELLOW}[2]${NC} ${BOLD}Development Build${NC} ${BLUE}(Latest from v3-remastered branch)${NC}"
	echo -e "     ${YELLOW}⚠️  May be unstable - for testing only${NC}"
	echo -e "     ${BLUE}→ Latest development build from v3-remastered branch${NC}"
	echo -e "     ${BLUE}→ Includes newest features and fixes${NC}"
	echo ""
	echo -e "  ${CYAN}[3]${NC} ${BOLD}Development Build (Custom)${NC} ${BLUE}(Specific branch/commit)${NC}"
	echo -e "     ${YELLOW}⚠️  May be unstable - for testing only${NC}"
	echo -e "     ${BLUE}→ Choose specific branch and optional commit${NC}"
	echo -e "     ${BLUE}→ For advanced users and testing${NC}"
	echo ""
	draw_hr
}

show_ssl_menu() {
	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "SSL Certificate Operations" "$CYAN"
	draw_hr
	echo ""
	echo -e "  ${GREEN}${BOLD}[1]${NC} ${BOLD}Install Certbot${NC}"
	echo -e "     ${BLUE}→ Install Let's Encrypt client (Certbot)${NC}"
	echo -e "     ${BLUE}→ Auto-detects and installs web server plugins${NC}"
	echo ""
	echo -e "  ${BLUE}${BOLD}[2]${NC} ${BOLD}Create Certificate (HTTP)${NC}"
	echo -e "     ${BLUE}→ Uses HTTP challenge method${NC}"
	echo -e "     ${BLUE}→ Requires port 80 to be available${NC}"
	echo -e "     ${BLUE}→ Works with Nginx, Apache, or standalone mode${NC}"
	echo ""
	echo -e "  ${YELLOW}${BOLD}[3]${NC} ${BOLD}Create Certificate (DNS)${NC}"
	echo -e "     ${BLUE}→ Uses DNS challenge method${NC}"
	echo -e "     ${BLUE}→ Requires manual TXT record creation${NC}"
	echo -e "     ${BLUE}→ Works when port 80 is not available${NC}"
	echo ""
	echo -e "  ${CYAN}${BOLD}[4]${NC} ${BOLD}Setup Auto-Renewal${NC}"
	echo -e "     ${BLUE}→ Configures automatic certificate renewal${NC}"
	echo -e "     ${BLUE}→ Creates cron job for daily renewal checks${NC}"
	echo ""
	echo -e "  ${RED}${BOLD}[5]${NC} ${BOLD}Install acme.sh${NC}"
	echo -e "     ${YELLOW}⚠️  Advanced tool for power users${NC}"
	echo -e "     ${BLUE}→ Alternative SSL certificate management tool${NC}"
	echo ""
	draw_hr
}

show_cf_mode_menu() {
	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	echo -e "${BOLD}Cloudflare Tunnel mode:${NC}"
	echo -e "  ${GREEN}[1]${NC} ${BOLD}Full Automatic${NC} ${BLUE}(API Key; creates tunnel + DNS)${NC}"
	echo -e "  ${YELLOW}[2]${NC} ${BOLD}Semi-Automatic${NC} ${BLUE}(provide Tunnel Token)${NC}"
	draw_hr
}

show_access_method_menu() {
	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	echo -e "${BOLD}Choose access method:${NC}"
	echo -e "  ${GREEN}[1]${NC} ${BOLD}Cloudflare Tunnel${NC} ${BLUE}(HTTPS via Cloudflare, no port forwarding)${NC}"
	echo -e "  ${BLUE}[2]${NC} ${BOLD}Nginx Reverse Proxy${NC} ${BLUE}(Traditional reverse proxy)${NC}"
	echo -e "  ${YELLOW}[3]${NC} ${BOLD}Apache2 Reverse Proxy${NC} ${BLUE}(Traditional reverse proxy)${NC}"
	echo -e "  ${CYAN}[4]${NC} ${BOLD}Direct Access${NC} ${BLUE}(Home hosting / no domain – use http://YOUR_IP:4832)${NC}"
	echo -e "     ${BLUE}→ No domain or SSL needed; ideal for local network or testing${NC}"
	draw_hr
}

# Ensure system is ready: update packages, upgrade, install sudo/curl and essentials
# Uses apt-get directly (we run as root); run early after OS check passes
ensure_system_ready() {
	if [ "$SKIP_SYSTEM_UPDATE" = true ]; then
		log_warn "System update skipped via --skip-system-update flag"
		return 0
	fi

	log_info "Updating package lists and ensuring essential packages (sudo, curl) are installed..."
	log_info "This may take a few minutes on first run."

	# We run as root - use apt-get directly (sudo may not exist on minimal systems)
	if ! apt-get update -qq >>"$LOG_FILE" 2>&1; then
		log_error "Failed to update package lists. Check $LOG_FILE for details."
		return 1
	fi

	# Install essential packages (sudo, curl needed by installer; others for Docker/Certbot)
	ESSENTIAL_PACKAGES="sudo curl ca-certificates apt-transport-https gnupg"
	for pkg in $ESSENTIAL_PACKAGES; do
		if ! dpkg -s "$pkg" >/dev/null 2>&1; then
			if ! run_with_spinner "Installing $pkg..." "$pkg installed." apt-get install -y -qq "$pkg"; then
				log_warn "Failed to install $pkg - continuing anyway"
			fi
		fi
	done

	# Upgrade all packages (non-interactive, with spinner - can take several minutes)
	if ! run_with_spinner "Upgrading system packages (may take 5-10 minutes)..." "System upgrade complete." true apt-get upgrade -y -qq; then
		log_warn "System upgrade had issues. Check $LOG_FILE. Continuing..."
	fi
}

# Function to check if a package is installed and install it if not
install_packages() {
	packages_to_install=()
	for pkg in "$@"; do
		if dpkg -s "$pkg" >/dev/null 2>&1; then
			log_info "$pkg is already installed. Skipping..."
		else
			packages_to_install+=("$pkg")
		fi
	done

	if [ ${#packages_to_install[@]} -gt 0 ]; then
		printf 'Installing packages: %s\n' "${packages_to_install[*]}" | sed 's/^/ /'
		log_step "Installing dependencies: ${packages_to_install[*]}"
		sudo apt-get -qq install -y "${packages_to_install[@]}" 2>&1 | tee -a "$LOG_FILE" >/dev/null || {
			log_error "Failed to install packages: ${packages_to_install[*]}"
			exit 1
		}
		log_success "Dependencies installed."
	fi
}

# Function to setup QEMU emulation for running amd64 containers on unsupported ARM systems
# Note: ARM64 (aarch64) is now natively supported, so QEMU is only needed for older ARM architectures
setup_qemu_emulation() {
	local arch=$(uname -m)

	# ARM64 is natively supported - no QEMU needed
	if [[ "$arch" == "aarch64" ]] || [[ "$arch" == "arm64" ]]; then
		log_info "ARM64 architecture detected: $arch"
		log_info "Native ARM64 Docker images are available - no emulation needed."
		return 0
	fi

	# Only setup QEMU for older unsupported ARM architectures (armv7, armv6)
	if [[ "$arch" != "armv7l" ]] && [[ "$arch" != "armv6l" ]]; then
		return 0
	fi

	log_warn "Unsupported ARM architecture detected: $arch"
	log_warn "MythicalDash only provides native images for amd64 and arm64 (aarch64)."
	log_warn "QEMU emulation will be used, which may result in reduced performance."

	# Ensure Docker is installed before setting up QEMU
	if ! command -v docker >/dev/null 2>&1; then
		log_warn "Docker is not installed yet. QEMU setup will be skipped for now."
		log_warn "QEMU will be set up after Docker installation."
		return 0
	fi

	# Wait for Docker daemon to be ready (in case it was just installed)
	log_info "Waiting for Docker daemon to be ready..."
	local max_attempts=10
	local attempt=0
	while [ $attempt -lt $max_attempts ]; do
		if sudo docker info >/dev/null 2>&1; then
			break
		fi
		attempt=$((attempt + 1))
		sleep 1
	done

	if ! sudo docker info >/dev/null 2>&1; then
		log_warn "Docker daemon is not ready. QEMU setup will be skipped."
		log_warn "You may need to manually run: docker run --rm --privileged tonistiigi/binfmt --install all"
		return 1
	fi

	log_info "Setting up QEMU emulation for running amd64 containers on $arch..."

	# Check if QEMU interpreters are already registered
	if [ -f /proc/sys/fs/binfmt_misc/qemu-x86_64 ] && [ -f /proc/sys/fs/binfmt_misc/qemu-i386 ]; then
		log_info "QEMU binfmt interpreters are already registered"
		return 0
	fi

	# Use Docker's binfmt tool to properly set up QEMU emulation
	# This is the recommended method for Docker containers
	log_info "Installing QEMU binfmt interpreters using Docker's binfmt tool..."
	if sudo docker run --rm --privileged tonistiigi/binfmt --install all >>"$LOG_FILE" 2>&1; then
		log_success "QEMU binfmt interpreters installed successfully"

		# Verify installation
		sleep 1
		if [ -f /proc/sys/fs/binfmt_misc/qemu-x86_64 ]; then
			log_info "Verified: QEMU x86_64 interpreter is registered"
		fi
		if [ -f /proc/sys/fs/binfmt_misc/qemu-i386 ]; then
			log_info "Verified: QEMU i386 interpreter is registered"
		fi
	else
		log_error "Failed to install QEMU binfmt interpreters"
		log_warn "You may need to manually run: docker run --rm --privileged tonistiigi/binfmt --install all"
		return 1
	fi

	log_success "QEMU emulation setup complete. Docker will use emulation to run amd64 containers on $arch."
	log_info "Note: Container startup may be slower due to emulation overhead."
}

# Function to stop all MythicalDash containers (including old containers that might not be in docker-compose.yml)
stop_all_mythicaldash_containers() {
	# First, try docker compose down if docker-compose.yml exists (stops containers defined in compose file)
	if [ -f /var/www/mythicaldash/docker-compose.yml ]; then
		cd /var/www/mythicaldash || true
		sudo docker compose down >>"$LOG_FILE" 2>&1 || true
	fi

	# Then stop any remaining MythicalDash containers by name (catches old containers not in compose file)
	RUNNING_CONTAINERS=$(sudo docker ps --format '{{.Names}}' 2>/dev/null | grep '^mythicaldash_' || true)

	if [ -n "$RUNNING_CONTAINERS" ]; then
		while IFS= read -r container; do
			if [ -n "$container" ]; then
				log_info "Stopping container: $container"
				sudo docker stop "$container" >>"$LOG_FILE" 2>&1 || true
			fi
		done <<<"$RUNNING_CONTAINERS"
	fi
}

# Prompt helpers that work even when the script is piped (stdin not a TTY)
prompt() {
	local message="$1"
	local __varname="$2"
	if [ -t 0 ]; then
		read -r -p "$message" "$__varname"
	else
		# Read from the real terminal
		read -r -p "$message" "$__varname" </dev/tty
	fi
}

prompt_secret() {
	local message="$1"
	local __varname="$2"
	if [ -t 0 ]; then
		read -r -s -p "$message" "$__varname"
		echo
	else
		# Read from the real terminal
		read -r -s -p "$message" "$__varname" </dev/tty
		echo
	fi
}

uninstall_cloudflare_tunnel() {
	echo "Uninstalling Cloudflare Tunnel..."
	if [ -f /var/www/mythicaldash/.env ]; then
		# shellcheck source=/dev/null
		. /var/www/mythicaldash/.env

		if [ -n "$TUNNEL_ID" ] && [ -n "$ACCOUNT_ID" ] && [ -n "$ZONE_ID" ] && [ -n "$CF_HOSTNAME" ]; then
			echo "Deleting DNS record for $CF_HOSTNAME..."
			DNS_RECORD_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records?type=CNAME&name=$CF_HOSTNAME" \
				-H "X-Auth-Email: $CF_EMAIL" \
				-H "X-Auth-Key: $CF_API_KEY" \
				-H "Content-Type: application/json" | jq -r '.result[0].id')

			if [ -n "$DNS_RECORD_ID" ] && [ "$DNS_RECORD_ID" != "null" ]; then
				curl -s -X DELETE "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records/$DNS_RECORD_ID" \
					-H "X-Auth-Email: $CF_EMAIL" \
					-H "X-Auth-Key: $CF_API_KEY" \
					-H "Content-Type: application/json" >/dev/null
				echo "DNS record deleted."
			else
				echo "Could not find DNS record for $CF_HOSTNAME or already deleted."
			fi

			echo "Deleting Cloudflare Tunnel..."
			curl -s -X DELETE "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID" \
				-H "X-Auth-Email: $CF_EMAIL" \
				-H "X-Auth-Key: $CF_API_KEY" \
				-H "Content-Type: application/json" >/dev/null
			echo "Cloudflare Tunnel deleted."
		else
			echo "Cloudflare Tunnel credentials not found or incomplete. Skipping tunnel deletion."
		fi
		# Do not remove .env; it stores user Cloudflare settings
	else
		echo "Cloudflare Tunnel credentials file not found. Skipping tunnel deletion."
	fi
}

setup_cloudflare_tunnel_full_auto() {
	echo "Starting full-automatic Cloudflare Tunnel setup..."
	install_packages jq

	ACCOUNTS_DATA=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts" \
		-H "X-Auth-Email: $CF_EMAIL" \
		-H "X-Auth-Key: $CF_API_KEY" \
		-H "Content-Type: application/json")

	ACCOUNT_COUNT=$(echo "$ACCOUNTS_DATA" | jq -r '.result | length')

	if [ "$ACCOUNT_COUNT" == "0" ]; then
		echo "Error: No Cloudflare accounts found. Please check your email and API key."
		return 1
	elif [ "$ACCOUNT_COUNT" -gt "1" ]; then
		draw_hr
		echo -e "${BOLD}Multiple Cloudflare accounts found. Please choose one:${NC}"
		draw_hr

		# Display accounts with colored menu
		local index=1
		echo "$ACCOUNTS_DATA" | jq -r '.result[] | "\(.id)|\(.name)"' | while IFS='|' read -r id name; do
			echo -e "  ${GREEN}[$index]${NC} ${BOLD}$name${NC} ${BLUE}($id)${NC}"
			index=$((index + 1))
		done

		draw_hr
		prompt "${BOLD}Enter account number${NC} ${BLUE}(1-$ACCOUNT_COUNT)${NC}: " ACCOUNT_CHOICE

		# Validate choice
		if [[ ! "$ACCOUNT_CHOICE" =~ ^[0-9]+$ ]] || [ "$ACCOUNT_CHOICE" -lt 1 ] || [ "$ACCOUNT_CHOICE" -gt "$ACCOUNT_COUNT" ]; then
			echo -e "${RED}Invalid choice. Using first account.${NC}"
			ACCOUNT_ID=$(echo "$ACCOUNTS_DATA" | jq -r '.result[0].id')
		else
			ACCOUNT_ID=$(echo "$ACCOUNTS_DATA" | jq -r ".result[$((ACCOUNT_CHOICE - 1))].id")
		fi
	else
		ACCOUNT_ID=$(echo "$ACCOUNTS_DATA" | jq -r '.result[0].id')
		log_info "Using Cloudflare account: $(echo "$ACCOUNTS_DATA" | jq -r '.result[0].name')"
	fi

	if [ "$ACCOUNT_ID" == "null" ] || [ -z "$ACCOUNT_ID" ]; then
		echo "Error: Could not get Cloudflare Account ID. Please check your email and API key."
		return 1
	fi

	# Create unique tunnel name based on hostname to avoid conflicts
	# Use hostname as part of tunnel name, sanitize it (remove dots, special chars)
	TUNNEL_NAME_SANITIZED=$(echo "$CF_HOSTNAME" | sed 's/[^a-zA-Z0-9-]/-/g' | tr '[:upper:]' '[:lower:]')
	TUNNEL_NAME="MythicalDash-${TUNNEL_NAME_SANITIZED}"

	# Check if tunnel with this name already exists
	TUNNEL_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel?name=$TUNNEL_NAME" \
		-H "X-Auth-Email: $CF_EMAIL" \
		-H "X-Auth-Key: $CF_API_KEY" \
		-H "Content-Type: application/json" | jq -r '.result[0].id')

	if [ "$TUNNEL_ID" == "null" ] || [ -z "$TUNNEL_ID" ]; then
		# Check if generic "MythicalDash" tunnel exists (for backward compatibility)
		TUNNEL_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel?name=MythicalDash" \
			-H "X-Auth-Email: $CF_EMAIL" \
			-H "X-Auth-Key: $CF_API_KEY" \
			-H "Content-Type: application/json" | jq -r '.result[0].id')

		if [ "$TUNNEL_ID" == "null" ] || [ -z "$TUNNEL_ID" ]; then
			# No existing tunnel found, create new one with unique name
			log_info "Creating Cloudflare Tunnel '$TUNNEL_NAME'..."
			TUNNEL_CREATE_DATA=$(curl -s -X POST "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel" \
				-H "X-Auth-Email: $CF_EMAIL" \
				-H "X-Auth-Key: $CF_API_KEY" \
				-H "Content-Type: application/json" \
				--data "$(jq -n --arg name "$TUNNEL_NAME" '{name:$name}')")
			TUNNEL_ID=$(echo "$TUNNEL_CREATE_DATA" | jq -r '.result.id')
			if [ "$TUNNEL_ID" == "null" ] || [ -z "$TUNNEL_ID" ]; then
				log_error "Could not create Cloudflare Tunnel."
				log_error "API Response: $TUNNEL_CREATE_DATA"
				return 1
			fi
			log_success "Created new Cloudflare Tunnel: $TUNNEL_NAME"
		else
			# Found generic tunnel, ask if user wants to reuse it or create new one
			log_warn "Found existing Cloudflare Tunnel named 'MythicalDash'."
			draw_hr
			echo -e "${BOLD}${YELLOW}Tunnel Conflict${NC}"
			draw_hr
			echo -e "${BLUE}An existing tunnel named 'MythicalDash' was found.${NC}"
			echo -e "${BLUE}Would you like to:${NC}"
			echo -e "  ${GREEN}[1]${NC} Reuse existing tunnel (recommended if this is the same server)"
			echo -e "  ${YELLOW}[2]${NC} Create new tunnel with unique name: $TUNNEL_NAME"
			draw_hr
			local tunnel_choice=""
			while [[ ! "$tunnel_choice" =~ ^[12]$ ]]; do
				prompt "${BOLD}Enter choice${NC} ${BLUE}(1/2)${NC}: " tunnel_choice
				if [[ ! "$tunnel_choice" =~ ^[12]$ ]]; then
					echo -e "${RED}Invalid input.${NC} Enter ${YELLOW}1${NC} or ${YELLOW}2${NC}."
					sleep 1
				fi
			done

			if [ "$tunnel_choice" == "2" ]; then
				# Create new tunnel with unique name
				log_info "Creating new Cloudflare Tunnel '$TUNNEL_NAME'..."
				TUNNEL_CREATE_DATA=$(curl -s -X POST "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel" \
					-H "X-Auth-Email: $CF_EMAIL" \
					-H "X-Auth-Key: $CF_API_KEY" \
					-H "Content-Type: application/json" \
					--data "$(jq -n --arg name "$TUNNEL_NAME" '{name:$name}')")
				TUNNEL_ID=$(echo "$TUNNEL_CREATE_DATA" | jq -r '.result.id')
				if [ "$TUNNEL_ID" == "null" ] || [ -z "$TUNNEL_ID" ]; then
					log_error "Could not create Cloudflare Tunnel."
					log_error "API Response: $TUNNEL_CREATE_DATA"
					return 1
				fi
				log_success "Created new Cloudflare Tunnel: $TUNNEL_NAME"
			else
				log_info "Reusing existing tunnel 'MythicalDash'."
				TUNNEL_NAME="MythicalDash"
			fi
		fi
	else
		log_info "Found existing Cloudflare Tunnel: $TUNNEL_NAME (reusing)"
	fi

	log_info "Using Tunnel ID: $TUNNEL_ID"

	CF_TUNNEL_TOKEN=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID/token" \
		-H "X-Auth-Email: $CF_EMAIL" \
		-H "X-Auth-Key: $CF_API_KEY" \
		-H "Content-Type: application/json" | jq -r '.result')

	if [ "$CF_TUNNEL_TOKEN" == "null" ] || [ -z "$CF_TUNNEL_TOKEN" ]; then
		echo "Error: Could not get Cloudflare Tunnel token. This might be due to API limitations."
		echo "Please try the semi-automatic mode."
		return 1
	fi

	ZONE_NAME=$(echo "$CF_HOSTNAME" | awk -F. '{print $(NF-1)"."$NF}')
	ZONE_ID=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones?name=$ZONE_NAME" \
		-H "X-Auth-Email: $CF_EMAIL" \
		-H "X-Auth-Key: $CF_API_KEY" \
		-H "Content-Type: application/json" | jq -r '.result[0].id')

	if [ "$ZONE_ID" == "null" ] || [ -z "$ZONE_ID" ]; then
		echo "Error: Could not get Cloudflare Zone ID for domain '$ZONE_NAME'."
		return 1
	fi

	log_info "Configuring DNS and ingress rules..."

	# Check if DNS record already exists
	EXISTING_DNS_RECORD=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records?type=CNAME&name=$CF_HOSTNAME" \
		-H "X-Auth-Email: $CF_EMAIL" \
		-H "X-Auth-Key: $CF_API_KEY" \
		-H "Content-Type: application/json" | jq -r '.result[0].id')

	TUNNEL_DOMAIN="${TUNNEL_ID}.cfargotunnel.com"

	if [ "$EXISTING_DNS_RECORD" != "null" ] && [ -n "$EXISTING_DNS_RECORD" ]; then
		# Update existing DNS record
		log_info "Updating existing DNS record for $CF_HOSTNAME..."
		curl -s -X PUT "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records/$EXISTING_DNS_RECORD" \
			-H "X-Auth-Email: $CF_EMAIL" \
			-H "X-Auth-Key: $CF_API_KEY" \
			-H "Content-Type: application/json" \
			--data "$(jq -n --arg host "$CF_HOSTNAME" --arg tunnel "$TUNNEL_DOMAIN" '{type:"CNAME",name:$host,content:$tunnel,proxied:true}')" >/dev/null
	else
		# Create new DNS record
		log_info "Creating DNS record for $CF_HOSTNAME..."
		curl -s -X POST "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/dns_records" \
			-H "X-Auth-Email: $CF_EMAIL" \
			-H "X-Auth-Key: $CF_API_KEY" \
			-H "Content-Type: application/json" \
			--data "$(jq -n --arg host "$CF_HOSTNAME" --arg tunnel "$TUNNEL_DOMAIN" '{type:"CNAME",name:$host,content:$tunnel,proxied:true}')" >/dev/null
	fi

	# Get existing tunnel configuration to merge with new ingress rule
	EXISTING_CONFIG_RESPONSE=$(curl -s -X GET "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID/configurations" \
		-H "X-Auth-Email: $CF_EMAIL" \
		-H "X-Auth-Key: $CF_API_KEY" \
		-H "Content-Type: application/json")

	EXISTING_INGRESS=$(echo "$EXISTING_CONFIG_RESPONSE" | jq '.result.config.ingress // []')

	# Check if config exists and has ingress rules
	if [ -n "$EXISTING_INGRESS" ] && [ "$EXISTING_INGRESS" != "null" ] && [ "$(echo "$EXISTING_INGRESS" | jq 'length')" -gt 0 ]; then
		# Merge existing ingress rules with new one
		log_info "Updating tunnel configuration (merging with existing rules)..."

		# Check if hostname already exists in ingress rules
		HOSTNAME_EXISTS=$(echo "$EXISTING_INGRESS" | jq -r --arg hostname "$CF_HOSTNAME" '.[] | select(.hostname == $hostname) | .hostname // empty' | head -n 1)

		if [ -n "$HOSTNAME_EXISTS" ] && [ "$HOSTNAME_EXISTS" != "null" ] && [ "$HOSTNAME_EXISTS" != "" ]; then
			# Update existing ingress rule for this hostname
			NEW_INGRESS=$(echo "$EXISTING_INGRESS" | jq --arg hostname "$CF_HOSTNAME" 'map(if .hostname == $hostname then {hostname: $hostname, service: "http://localhost:4832"} else . end)')
		else
			# Remove catch-all if it exists, add new rule, then re-add catch-all
			INGRESS_WITHOUT_CATCHALL=$(echo "$EXISTING_INGRESS" | jq 'map(select(.service != "http_status:404"))')
			NEW_INGRESS=$(echo "$INGRESS_WITHOUT_CATCHALL" | jq --arg hostname "$CF_HOSTNAME" '. + [{hostname: $hostname, service: "http://localhost:4832"}] + [{service: "http_status:404"}]')
		fi

		curl -s -X PUT "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID/configurations" \
			-H "X-Auth-Email: $CF_EMAIL" \
			-H "X-Auth-Key: $CF_API_KEY" \
			-H "Content-Type: application/json" \
			--data "$(jq -n --argjson ingress "$NEW_INGRESS" '{config:{ingress:$ingress}}')" >/dev/null
	else
		# No existing config, create new one
		log_info "Creating tunnel configuration..."
		curl -s -X PUT "https://api.cloudflare.com/client/v4/accounts/$ACCOUNT_ID/cfd_tunnel/$TUNNEL_ID/configurations" \
			-H "X-Auth-Email: $CF_EMAIL" \
			-H "X-Auth-Key: $CF_API_KEY" \
			-H "Content-Type: application/json" \
			--data "$(jq -n --arg hostname "$CF_HOSTNAME" '{config:{ingress:[{hostname:$hostname,service:"http://localhost:4832"},{service:"http_status:404"}]}}')" >/dev/null
	fi

	log_info "Full-automatic Cloudflare Tunnel setup complete."

	# Persist Cloudflare credentials to .env for future uninstall/updates
	ENV_FILE=/var/www/mythicaldash/.env
	log_info "Writing Cloudflare settings to $ENV_FILE"
	{
		printf 'CF_EMAIL="%s"\n' "$CF_EMAIL"
		printf 'CF_API_KEY="%s"\n' "$CF_API_KEY"
		printf 'ACCOUNT_ID="%s"\n' "$ACCOUNT_ID"
		printf 'TUNNEL_ID="%s"\n' "$TUNNEL_ID"
		printf 'TUNNEL_NAME="%s"\n' "$TUNNEL_NAME"
		printf 'ZONE_ID="%s"\n' "$ZONE_ID"
		printf 'CF_HOSTNAME="%s"\n' "$CF_HOSTNAME"
		printf 'CF_TUNNEL_TOKEN="%s"\n' "$CF_TUNNEL_TOKEN"
	} | sudo tee "$ENV_FILE" >/dev/null
	sudo chmod 600 "$ENV_FILE"
	log_success "Cloudflare settings saved."
}

setup_cloudflare_tunnel_client() {
	if [ -n "$CF_TUNNEL_TOKEN" ]; then
		log_info "Setting up Cloudflare Tunnel..."
		if command -v docker &>/dev/null; then
			log_info "Docker is already installed."
		else
			log_step "Installing Docker engine (this may take a minute)..."
			curl -sSL https://get.docker.com/ | CHANNEL=stable bash >>"$LOG_FILE" 2>&1
			sudo systemctl enable --now docker 2>&1 | tee -a "$LOG_FILE" >/dev/null
			sudo usermod -aG docker "$USER" 2>&1 | tee -a "$LOG_FILE" >/dev/null || true
			log_success "Docker installed. You may need to re-login for group changes to take effect."
		fi
		if ! run_with_spinner "Starting Cloudflare Tunnel container" "Cloudflare Tunnel container running." \
			docker run -d --network host --restart always cloudflare/cloudflared:latest tunnel --no-autoupdate run --token "$CF_TUNNEL_TOKEN"; then
			return 1
		fi
		log_info "Cloudflare Tunnel setup complete."
		if [ "$CF_TUNNEL_MODE" == "2" ]; then
			echo -e "\033[0;33mYou have chosen Semi-Automatic Cloudflare Tunnel setup.\033[0m"
			echo -e "\033[0;33mPlease manually create a DNS record for your hostname pointing to the tunnel in your Cloudflare dashboard.\033[0m"
			echo -e "\033[0;33mThe ingress rule should point to http://localhost:4832.\033[0m"
			echo -e "\033[0;33mMore information: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/get-started/create-remote-tunnel-api/\033[0m"
		fi
	else
		log_info "Skipping Cloudflare Tunnel setup as no token was provided or generated."
	fi
}

# SSL Certificate functions
install_certbot() {
	local webserver_type="${1:-}" # Optional parameter: "nginx", "apache", or empty for auto-detect
	log_step "Installing Certbot..."
	log_info "Certbot has many dependencies - installation may take 5-15 minutes. Please wait..."
	log_info "To monitor progress in another terminal: tail -f $LOG_FILE"

	# Update package list (muted)
	sudo apt-get update -qq >>"$LOG_FILE" 2>&1

	# Install base certbot (with spinner - can take several minutes)
	if ! run_with_spinner "Installing Certbot and dependencies..." "Certbot installed." true install_packages certbot; then
		return 1
	fi

	# Detect which web server plugins to install
	plugins_to_install=()

	# If webserver type was provided, use it directly
	if [ -n "$webserver_type" ]; then
		case $webserver_type in
		nginx)
			log_info "Installing Nginx plugin (based on your selection)..."
			plugins_to_install+=("python3-certbot-nginx")
			;;
		apache)
			log_info "Installing Apache plugin (based on your selection)..."
			plugins_to_install+=("python3-certbot-apache")
			;;
		esac
	else
		# Auto-detect web server
		# Check for Nginx
		if systemctl is-active --quiet nginx 2>/dev/null || systemctl is-enabled --quiet nginx 2>/dev/null || dpkg -l | grep -q "^ii.*nginx"; then
			log_info "Nginx detected, installing Nginx plugin..."
			plugins_to_install+=("python3-certbot-nginx")
		fi

		# Check for Apache
		if systemctl is-active --quiet apache2 2>/dev/null || systemctl is-enabled --quiet apache2 2>/dev/null || dpkg -l | grep -q "^ii.*apache2"; then
			log_info "Apache detected, installing Apache plugin..."
			plugins_to_install+=("python3-certbot-apache")
		fi

		# If no web server detected, ask user what they want
		if [ ${#plugins_to_install[@]} -eq 0 ]; then
			log_info "No web server detected. You can install plugins for future use."
			log_info "Which web server plugin would you like to install? (optional)"
			log_info "  [1] Nginx plugin"
			log_info "  [2] Apache plugin"
			log_info "  [3] Both plugins (Not recommended)"
			log_info "  [4] Skip plugins (standalone only)"
			plugin_choice=""
			prompt "${BOLD}Enter choice${NC} ${BLUE}(1/2/3/4)${NC}: " plugin_choice

			case $plugin_choice in
			1)
				plugins_to_install+=("python3-certbot-nginx")
				log_info "Installing Nginx plugin..."
				;;
			2)
				plugins_to_install+=("python3-certbot-apache")
				log_info "Installing Apache plugin..."
				;;
			3)
				plugins_to_install+=("python3-certbot-nginx" "python3-certbot-apache")
				log_info "Installing both Nginx and Apache plugins..."
				;;
			4)
				log_info "Skipping web server plugins. You can use standalone mode."
				;;
			*)
				log_warn "Invalid choice. Skipping web server plugins."
				;;
			esac
		fi
	fi

	# Install selected plugins (with spinner - can take several minutes)
	if [ ${#plugins_to_install[@]} -gt 0 ]; then
		if ! run_with_spinner "Installing web server plugin(s)..." "Web server plugins installed." true install_packages "${plugins_to_install[@]}"; then
			return 1
		fi
		log_success "Certbot installed successfully with web server plugins."
	else
		log_success "Certbot installed successfully (standalone mode available)."
	fi

	log_info "Certbot is now available for SSL certificate management."
}

create_ssl_certificate_http() {
	log_step "Creating SSL Certificate (HTTP/Standalone method)..."

	if ! command -v certbot >/dev/null 2>&1; then
		log_error "Certbot is not installed. Please install it first."
		return 1
	fi

	# Get domain from user
	local domain=""
	while [ -z "$domain" ]; do
		prompt "${BOLD}Enter domain name${NC} ${BLUE}(e.g., panel.example.com)${NC}: " domain
	done

	# Get public IP addresses for DNS guidance
	log_info "Detecting your server's public IP addresses..."
	detect_public_ips

	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	show_dns_setup_instructions "$domain"
	prompt "${BOLD}Press Enter when you have created the DNS records${NC} ${BLUE}(and waited for propagation)${NC}: " ready_to_continue

	log_info "This will be the main domain for your Panel (not a subdirectory like /panel)."

	# Check if web server is running
	local webserver=""
	if systemctl is-active --quiet nginx; then
		webserver="nginx"
	elif systemctl is-active --quiet apache2; then
		webserver="apache"
	else
		webserver="standalone"
	fi

	log_info "Detected web server: $webserver"

	# Create certificate based on detected web server
	case $webserver in
	nginx)
		# Check if Nginx plugin is available
		if dpkg -l | grep -q "^ii.*python3-certbot-nginx"; then
			log_info "Using Nginx plugin for certificate creation..."
			certbot certonly --nginx -d "$domain" --non-interactive --agree-tos --email admin@"$domain" || {
				log_error "Failed to create certificate with Nginx plugin"
				return 1
			}
		else
			log_warn "Nginx plugin not installed. Falling back to standalone method."
			log_info "Stopping Nginx temporarily to free port 80..."
			sudo systemctl stop nginx
			certbot certonly --standalone -d "$domain" --non-interactive --agree-tos --email admin@"$domain" || {
				log_error "Failed to create certificate with standalone method"
				sudo systemctl start nginx
				return 1
			}
			log_info "Restarting Nginx..."
			sudo systemctl start nginx
		fi
		;;
	apache)
		# Check if Apache plugin is available
		if dpkg -l | grep -q "^ii.*python3-certbot-apache"; then
			log_info "Using Apache plugin for certificate creation..."
			certbot certonly --apache -d "$domain" --non-interactive --agree-tos --email admin@"$domain" || {
				log_error "Failed to create certificate with Apache plugin"
				return 1
			}
		else
			log_warn "Apache plugin not installed. Falling back to standalone method."
			log_info "Stopping Apache temporarily to free port 80..."
			sudo systemctl stop apache2
			certbot certonly --standalone -d "$domain" --non-interactive --agree-tos --email admin@"$domain" || {
				log_error "Failed to create certificate with standalone method"
				sudo systemctl start apache2
				return 1
			}
			log_info "Restarting Apache..."
			sudo systemctl start apache2
		fi
		;;
	standalone)
		log_info "Using standalone method for certificate creation..."
		log_warn "Make sure port 80 is not in use by other services."
		certbot certonly --standalone -d "$domain" --non-interactive --agree-tos --email admin@"$domain" || {
			log_error "Failed to create certificate with standalone method"
			return 1
		}
		;;
	esac

	log_success "SSL certificate created successfully for $domain"
	log_info "Certificate location: /etc/letsencrypt/live/$domain/"

	# Check if reverse proxy is already configured for this domain
	local config_updated=false
	if [ -f /etc/nginx/sites-enabled/mythicaldash ] && grep -q "$domain" /etc/nginx/sites-enabled/mythicaldash 2>/dev/null; then
		log_info "Updating existing Nginx configuration to use SSL..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/ssl/nginx.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/nginx/sites-available/mythicaldash >/dev/null
		if nginx -t 2>&1 | tee -a "$LOG_FILE" >/dev/null; then
			systemctl reload nginx 2>&1 | tee -a "$LOG_FILE" >/dev/null
			log_success "Nginx SSL configuration updated and reloaded successfully"
			config_updated=true
		else
			log_error "Nginx configuration test failed. Check $LOG_FILE for details."
		fi
	elif [ -f /etc/apache2/sites-enabled/mythicaldash.conf ] && grep -q "$domain" /etc/apache2/sites-enabled/mythicaldash.conf 2>/dev/null; then
		log_info "Updating existing Apache configuration to use SSL..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/ssl/apache2.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/apache2/sites-available/mythicaldash.conf >/dev/null
		if apache2ctl configtest 2>&1 | tee -a "$LOG_FILE" >/dev/null; then
			systemctl reload apache2 2>&1 | tee -a "$LOG_FILE" >/dev/null
			log_success "Apache SSL configuration updated and reloaded successfully"
			config_updated=true
		else
			log_error "Apache configuration test failed. Check $LOG_FILE for details."
		fi
	fi

	# If no existing config was updated, check if we should set up reverse proxy automatically
	if [ "$config_updated" = false ]; then
		# Check if nginx or apache is installed/running
		local webserver_detected=""
		if command -v nginx >/dev/null 2>&1 || systemctl is-active --quiet nginx 2>/dev/null; then
			webserver_detected="nginx"
		elif command -v apache2 >/dev/null 2>&1 || systemctl is-active --quiet apache2 2>/dev/null; then
			webserver_detected="apache"
		fi

		if [ -n "$webserver_detected" ]; then
			if [ -t 1 ]; then clear; fi
			print_banner
			draw_hr
			echo -e "${BOLD}${YELLOW}Reverse Proxy Configuration${NC}"
			draw_hr
			echo -e "${BLUE}A web server ($webserver_detected) is detected but not configured for MythicalDash.${NC}"
			echo -e "${BLUE}Would you like to automatically configure it with SSL for this domain?${NC}"
			setup_reverse_proxy=""
			prompt "${BOLD}Configure $webserver_detected with SSL?${NC} ${BLUE}(y/n)${NC}: " setup_reverse_proxy

			if [[ "$setup_reverse_proxy" =~ ^[yY]$ ]]; then
				if [ "$webserver_detected" = "nginx" ]; then
					if setup_nginx_reverse_proxy "$domain" "true"; then
						log_success "Nginx reverse proxy configured with SSL for $domain"
						config_updated=true
					fi
				elif [ "$webserver_detected" = "apache" ]; then
					if setup_apache_reverse_proxy "$domain" "true"; then
						log_success "Apache reverse proxy configured with SSL for $domain"
						config_updated=true
					fi
				fi
			fi
		fi

		if [ "$config_updated" = false ]; then
			draw_hr
			log_warn "Reverse proxy not automatically configured."
			log_info "To configure your web server manually:"
			log_info "  - Certificate: /etc/letsencrypt/live/$domain/fullchain.pem"
			log_info "  - Private Key: /etc/letsencrypt/live/$domain/privkey.pem"
			log_info "  - Configure your web server to proxy to http://localhost:4832"
			draw_hr
		fi
	fi
}

create_ssl_certificate_dns() {
	log_step "Creating SSL Certificate (DNS challenge method)..."

	if ! command -v certbot >/dev/null 2>&1; then
		log_error "Certbot is not installed. Please install it first."
		return 1
	fi

	# Get domain from user
	local domain=""
	while [ -z "$domain" ]; do
		prompt "${BOLD}Enter domain name${NC} ${BLUE}(e.g., panel.example.com)${NC}: " domain
	done

	# Get public IP addresses for DNS guidance
	log_info "Detecting your server's public IP addresses..."
	detect_public_ips

	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	show_dns_setup_instructions "$domain"
	prompt "${BOLD}Press Enter when you have created the DNS records${NC} ${BLUE}(and waited for propagation)${NC}: " ready_to_continue

	log_info "This will be the main domain for your Panel (not a subdirectory like /panel)."

	log_info "Using DNS challenge method for certificate creation..."
	log_warn "This method requires you to manually create TXT DNS records."
	log_info "Certbot will pause and wait for you to create the DNS record."

	log_info "Press Enter to continue when you're ready to start the DNS challenge..."
	read -r

	# Run certbot in interactive mode for DNS challenge
	certbot -d "$domain" --manual --preferred-challenges dns certonly --agree-tos --email admin@"$domain" || {
		log_error "Failed to create certificate with DNS challenge"
		return 1
	}

	log_success "SSL certificate created successfully for $domain"
	log_info "Certificate location: /etc/letsencrypt/live/$domain/"

	# Check if reverse proxy is already configured for this domain
	local config_updated=false
	if [ -f /etc/nginx/sites-enabled/mythicaldash ] && grep -q "$domain" /etc/nginx/sites-enabled/mythicaldash 2>/dev/null; then
		log_info "Updating existing Nginx configuration to use SSL..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/ssl/nginx.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/nginx/sites-available/mythicaldash >/dev/null
		if nginx -t 2>&1 | tee -a "$LOG_FILE" >/dev/null; then
			systemctl reload nginx 2>&1 | tee -a "$LOG_FILE" >/dev/null
			log_success "Nginx SSL configuration updated and reloaded successfully"
			config_updated=true
		else
			log_error "Nginx configuration test failed. Check $LOG_FILE for details."
		fi
	elif [ -f /etc/apache2/sites-enabled/mythicaldash.conf ] && grep -q "$domain" /etc/apache2/sites-enabled/mythicaldash.conf 2>/dev/null; then
		log_info "Updating existing Apache configuration to use SSL..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/ssl/apache2.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/apache2/sites-available/mythicaldash.conf >/dev/null
		if apache2ctl configtest 2>&1 | tee -a "$LOG_FILE" >/dev/null; then
			systemctl reload apache2 2>&1 | tee -a "$LOG_FILE" >/dev/null
			log_success "Apache SSL configuration updated and reloaded successfully"
			config_updated=true
		else
			log_error "Apache configuration test failed. Check $LOG_FILE for details."
		fi
	fi

	# If no existing config was updated, check if we should set up reverse proxy automatically
	if [ "$config_updated" = false ]; then
		# Check if nginx or apache is installed/running
		local webserver_detected=""
		if command -v nginx >/dev/null 2>&1 || systemctl is-active --quiet nginx 2>/dev/null; then
			webserver_detected="nginx"
		elif command -v apache2 >/dev/null 2>&1 || systemctl is-active --quiet apache2 2>/dev/null; then
			webserver_detected="apache"
		fi

		if [ -n "$webserver_detected" ]; then
			if [ -t 1 ]; then clear; fi
			print_banner
			draw_hr
			echo -e "${BOLD}${YELLOW}Reverse Proxy Configuration${NC}"
			draw_hr
			echo -e "${BLUE}A web server ($webserver_detected) is detected but not configured for MythicalDash.${NC}"
			echo -e "${BLUE}Would you like to automatically configure it with SSL for this domain?${NC}"
			setup_reverse_proxy=""
			prompt "${BOLD}Configure $webserver_detected with SSL?${NC} ${BLUE}(y/n)${NC}: " setup_reverse_proxy

			if [[ "$setup_reverse_proxy" =~ ^[yY]$ ]]; then
				if [ "$webserver_detected" = "nginx" ]; then
					if setup_nginx_reverse_proxy "$domain" "true"; then
						log_success "Nginx reverse proxy configured with SSL for $domain"
						config_updated=true
					fi
				elif [ "$webserver_detected" = "apache" ]; then
					if setup_apache_reverse_proxy "$domain" "true"; then
						log_success "Apache reverse proxy configured with SSL for $domain"
						config_updated=true
					fi
				fi
			fi
		fi

		if [ "$config_updated" = false ]; then
			draw_hr
			log_warn "Reverse proxy not automatically configured."
			log_info "To configure your web server manually:"
			log_info "  - Certificate: /etc/letsencrypt/live/$domain/fullchain.pem"
			log_info "  - Private Key: /etc/letsencrypt/live/$domain/privkey.pem"
			log_info "  - Configure your web server to proxy to http://localhost:4832"
			draw_hr
		fi
	fi
}

setup_ssl_auto_renewal() {
	log_step "Setting up SSL certificate auto-renewal..."

	if ! command -v certbot >/dev/null 2>&1; then
		log_error "Certbot is not installed. Please install it first."
		return 1
	fi

	# Get web server type for restart command
	local restart_command=""
	if systemctl is-active --quiet nginx; then
		restart_command="systemctl restart nginx"
	elif systemctl is-active --quiet apache2; then
		restart_command="systemctl restart apache2"
	else
		restart_command="systemctl reload-or-restart nginx"
	fi

	log_info "Detected service for restart: $restart_command"

	# Add cron job for auto-renewal
	local cron_job="0 23 * * * certbot renew --quiet --deploy-hook \"$restart_command\""

	# Check if cron job already exists
	if crontab -l 2>/dev/null | grep -q "certbot renew"; then
		log_warn "SSL auto-renewal cron job already exists."
		echo "Current cron jobs:"
		crontab -l 2>/dev/null | grep "certbot renew"
		update_cron=""
		prompt "Do you want to update the existing cron job? (y/n): " update_cron
		if [[ "$update_cron" =~ ^[yY]$ ]]; then
			# Remove old certbot cron jobs
			crontab -l 2>/dev/null | grep -v "certbot renew" | crontab -
			# Add new one
			(
				crontab -l 2>/dev/null
				echo "$cron_job"
			) | crontab -
			log_success "SSL auto-renewal cron job updated."
		fi
	else
		# Add new cron job
		(
			crontab -l 2>/dev/null
			echo "$cron_job"
		) | crontab -
		log_success "SSL auto-renewal cron job added."
	fi

	log_info "Certificates will be checked for renewal daily at 23:00 (11 PM)"
	log_info "If renewed, the following command will be executed: $restart_command"
}

setup_nginx_reverse_proxy() {
	local domain="$1"
	local has_ssl="$2"

	install_packages nginx
	systemctl enable nginx 2>&1 | tee -a "$LOG_FILE" >/dev/null || true
	systemctl start nginx 2>&1 | tee -a "$LOG_FILE" >/dev/null || true

	# Create config directory if it doesn't exist
	sudo mkdir -p /etc/nginx/sites-available
	sudo mkdir -p /etc/nginx/sites-enabled

	# Download and customize nginx config
	if [ "$has_ssl" = "true" ]; then
		log_info "Downloading SSL-enabled Nginx configuration..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/ssl/nginx.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/nginx/sites-available/mythicaldash >/dev/null
	else
		log_info "Downloading HTTP-only Nginx configuration..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/plaintext/nginx.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/nginx/sites-available/mythicaldash >/dev/null
	fi

	# Enable the site
	sudo ln -sf /etc/nginx/sites-available/mythicaldash /etc/nginx/sites-enabled/

	# Test nginx configuration
	if nginx -t 2>&1 | tee -a "$LOG_FILE" >/dev/null; then
		log_success "Nginx configuration is valid"
		if ! run_with_spinner "Reloading Nginx" "Nginx reloaded." sudo systemctl reload nginx; then
			return 1
		fi
	else
		log_error "Nginx configuration test failed"
		return 1
	fi
}

setup_apache_reverse_proxy() {
	local domain="$1"
	local has_ssl="$2"

	install_packages apache2
	systemctl enable apache2 2>&1 | tee -a "$LOG_FILE" >/dev/null || true
	systemctl start apache2 2>&1 | tee -a "$LOG_FILE" >/dev/null || true

	# Enable required Apache modules
	log_info "Enabling required Apache modules..."
	a2enmod ssl proxy proxy_http proxy_wstunnel rewrite 2>&1 | tee -a "$LOG_FILE" >/dev/null || true

	# Create config directory if it doesn't exist
	sudo mkdir -p /etc/apache2/sites-available

	# Download and customize apache config
	if [ "$has_ssl" = "true" ]; then
		log_info "Downloading SSL-enabled Apache configuration..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/ssl/apache2.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/apache2/sites-available/mythicaldash.conf >/dev/null
	else
		log_info "Downloading HTTP-only Apache configuration..."
		curl -s "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/.github/docker/plaintext/apache2.conf" |
			sed "s/your-domain.com/$domain/g" |
			sudo tee /etc/apache2/sites-available/mythicaldash.conf >/dev/null
	fi

	# Enable the site
	a2ensite mythicaldash 2>&1 | tee -a "$LOG_FILE" >/dev/null || true

	# Test apache configuration
	if apache2ctl configtest 2>&1 | tee -a "$LOG_FILE" >/dev/null; then
		log_success "Apache configuration is valid"
		if ! run_with_spinner "Reloading Apache" "Apache reloaded." sudo systemctl reload apache2; then
			return 1
		fi
	else
		log_error "Apache configuration test failed"
		return 1
	fi
}

install_acme_sh() {
	log_step "Installing acme.sh (Advanced SSL certificate tool)..."

	# Install acme.sh
	curl https://get.acme.sh | sh -s email=admin@example.com || {
		log_error "Failed to install acme.sh"
		return 1
	}

	# Source acme.sh for current session
	source ~/.bashrc

	log_success "acme.sh installed successfully."
	log_info "acme.sh is now available for advanced SSL certificate management."
	log_info "For Cloudflare DNS challenge, use: acme.sh --issue --dns dns_cf -d yourdomain.com"
	log_info "For more information, visit: https://github.com/acmesh-official/acme.sh"
}

install_mythicaldash_command() {
	log_step "Installing global 'mythicaldash' command..."

	# Create the mythicaldash command script (container CLI + run-script)
	cat <<'SCRIPT' | sudo tee /usr/local/bin/mythicaldash >/dev/null
#!/bin/bash
# MythicalDash CLI wrapper - runs commands in the backend container or installer

# run-script: fetch and run the MythicalDash installer
if [ "$1" = "run-script" ]; then
    echo "Running MythicalDash installer..."
    curl -sSL "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/v3-remastered/installer/install.sh" | sudo bash
    exit $?
fi

CONTAINER_NAME="mythicaldash_v3_backend"

# Check if container exists and is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "Error: MythicalDash backend container '${CONTAINER_NAME}' is not running." >&2
    echo "Please ensure MythicalDash is installed and running." >&2
    exit 1
fi

# Use -it if stdin is a TTY, otherwise use -i only
if [ -t 0 ]; then
    docker exec -it "${CONTAINER_NAME}" php cli "$@"
else
    docker exec -i "${CONTAINER_NAME}" php cli "$@"
fi
SCRIPT

	# Make it executable
	sudo chmod +x /usr/local/bin/mythicaldash

	log_success "Global 'mythicaldash' command installed successfully."
	log_info "You can now use: mythicaldash <command> (runs in backend container)"
	log_info "  or: mythicaldash run-script (runs the installer)"
	log_info "Example: mythicaldash help"
}

# Backup management functions
create_backup() {
	log_step "Creating MythicalDash backup..."

	if [ ! -f /var/www/mythicaldash/.installed ]; then
		log_error "MythicalDash is not installed. Nothing to backup."
		return 1
	fi

	# Check if containers are running
	if ! sudo docker ps --format '{{.Names}}' 2>/dev/null | grep -q "mythicaldash_v3_backend\|mythicaldash_v3_mysql"; then
		log_error "MythicalDash containers are not running. Cannot create backup."
		return 1
	fi

	# Create backup directory
	sudo mkdir -p "$BACKUP_DIR"

	# Generate backup filename with timestamp
	BACKUP_TIMESTAMP=$(date +%Y%m%d_%H%M%S)
	BACKUP_NAME="mythicaldash_backup_${BACKUP_TIMESTAMP}.tar.gz"
	BACKUP_PATH="${BACKUP_DIR}/${BACKUP_NAME}"

	log_info "Backup will be saved to: $BACKUP_PATH"

	# Create temporary directory for backup contents
	TEMP_BACKUP_DIR=$(mktemp -d)
	trap 'rm -rf "$TEMP_BACKUP_DIR"' EXIT

	log_info "Backing up Docker volumes (volume-only backup method)..."
	# Backup volumes only - this is the safest and most reliable method
	VOLUMES_DIR="${TEMP_BACKUP_DIR}/volumes"
	mkdir -p "$VOLUMES_DIR"

	# Get actual volume names from running containers (most reliable method)
	VOLUMES_FOUND=0
	declare -a ACTUAL_VOLUMES=()

	# Get volumes directly from running containers
	for container in mythicaldash_v3_mysql mythicaldash_v3_backend mythicaldash_v3_redis; do
		if sudo docker ps --format '{{.Names}}' 2>/dev/null | grep -q "^${container}$"; then
			# Get volume names from container mounts
			while IFS= read -r volume_name; do
				if [ -n "$volume_name" ] && [[ ! " ${ACTUAL_VOLUMES[*]} " =~ " ${volume_name} " ]]; then
					ACTUAL_VOLUMES+=("$volume_name")
				fi
			done < <(sudo docker inspect "$container" --format '{{range .Mounts}}{{if eq .Type "volume"}}{{.Name}}{{println}}{{end}}{{end}}' 2>/dev/null | grep -v "^$" || true)
		fi
	done

	# If no volumes found from containers, try docker volume ls with filter
	if [ ${#ACTUAL_VOLUMES[@]} -eq 0 ]; then
		log_info "Getting volumes from docker volume list..."
		while IFS= read -r volume_name; do
			if [ -n "$volume_name" ] && [[ "$volume_name" =~ ^mythicaldash ]]; then
				if [[ ! " ${ACTUAL_VOLUMES[*]} " =~ " ${volume_name} " ]]; then
					ACTUAL_VOLUMES+=("$volume_name")
				fi
			fi
		done < <(sudo docker volume ls --format "{{.Name}}" 2>/dev/null | grep "^mythicaldash" || true)
	fi

	# Fallback: Try known volume names if still nothing found
	if [ ${#ACTUAL_VOLUMES[@]} -eq 0 ]; then
		log_warn "Could not detect volumes from containers, trying known volume names..."
		ACTUAL_VOLUMES=("mythicaldash_mariadb_data" "mythicaldash_redis_data" "mythicaldash_mythicaldash_v3_attachments" "mythicaldash_mythicaldash_v3_snapshots")
	fi

	# Backup each volume that actually exists
	for volume in "${ACTUAL_VOLUMES[@]}"; do
		if sudo docker volume inspect "$volume" >/dev/null 2>&1; then
			log_info "Backing up volume: $volume"
			if sudo docker run --rm -v "$volume":/source -v "$VOLUMES_DIR":/backup alpine tar czf "/backup/${volume}.tar.gz" -C /source . 2>>"$LOG_FILE"; then
				log_success "Volume $volume backed up"
				VOLUMES_FOUND=$((VOLUMES_FOUND + 1))
			else
				log_warn "Failed to backup volume $volume"
			fi
		fi
	done

	# Check if we got the critical mariadb_data volume
	MARIADB_BACKED_UP=false
	for volume in "${ACTUAL_VOLUMES[@]}"; do
		if [[ "$volume" =~ mariadb_data ]] && sudo docker volume inspect "$volume" >/dev/null 2>&1; then
			if [ -f "${VOLUMES_DIR}/${volume}.tar.gz" ]; then
				MARIADB_BACKED_UP=true
				break
			fi
		fi
	done

	if [ $VOLUMES_FOUND -eq 0 ]; then
		log_error "No volumes found to backup. Is MythicalDash installed and running?"
		return 1
	fi

	if [ "$MARIADB_BACKED_UP" = false ]; then
		log_error "mariadb_data volume not found or could not be backed up. This is required for database backup."
		return 1
	fi

	log_success "Backed up $VOLUMES_FOUND volume(s) including database (mariadb_data)"

	log_info "Backing up configuration files..."
	# Backup configuration files
	CONFIG_DIR="${TEMP_BACKUP_DIR}/config"
	mkdir -p "$CONFIG_DIR"

	# Copy important config files
	if [ -f /var/www/mythicaldash/docker-compose.yml ]; then
		sudo cp /var/www/mythicaldash/docker-compose.yml "$CONFIG_DIR/" 2>>"$LOG_FILE"
	fi
	if [ -f /var/www/mythicaldash/.env ]; then
		sudo cp /var/www/mythicaldash/.env "$CONFIG_DIR/" 2>>"$LOG_FILE"
	fi

	# Create backup info file
	cat >"${TEMP_BACKUP_DIR}/backup_info.txt" <<EOF
MythicalDash Backup
Created: $(date)
Backup Name: $BACKUP_NAME
Version: $(grep -oP 'image: ghcr.io/mythicalltd/mythicaldash_v3-backend:\K[^\s]+' /var/www/mythicaldash/docker-compose.yml 2>/dev/null || echo "unknown")
Backup Method: Volume-only backup (safest and most reliable)
Volumes Backed Up: $VOLUMES_FOUND
Database: Backed up via mariadb_data volume (raw files)
EOF

	log_info "Compressing backup..."
	# Create compressed archive
	if sudo tar -czf "$BACKUP_PATH" -C "$TEMP_BACKUP_DIR" . 2>>"$LOG_FILE"; then
		sudo chmod 600 "$BACKUP_PATH"
		BACKUP_SIZE=$(du -h "$BACKUP_PATH" | cut -f1)
		log_success "Backup created successfully: $BACKUP_NAME ($BACKUP_SIZE)"
		log_info "Backup location: $BACKUP_PATH"
		return 0
	else
		log_error "Failed to create backup archive"
		return 1
	fi
}

list_backups() {
	log_step "Listing MythicalDash backups..."

	if [ ! -d "$BACKUP_DIR" ]; then
		log_warn "Backup directory does not exist. No backups found."
		return 0
	fi

	mapfile -t BACKUP_FILES < <(sudo find "$BACKUP_DIR" -name "mythicaldash_backup_*.tar.gz" -type f 2>/dev/null | sort -r)

	if [ ${#BACKUP_FILES[@]} -eq 0 ]; then
		log_warn "No backups found in $BACKUP_DIR"
		return 0
	fi

	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "Available Backups" "$CYAN"
	draw_hr
	echo ""

	local index=1
	for backup_file in "${BACKUP_FILES[@]}"; do
		BACKUP_NAME=$(basename "$backup_file")
		BACKUP_SIZE=$(sudo du -h "$backup_file" | cut -f1)
		BACKUP_DATE=$(sudo stat -c %y "$backup_file" 2>/dev/null | cut -d' ' -f1,2 | cut -d'.' -f1 || echo "Unknown")

		# Extract timestamp from filename
		BACKUP_TIMESTAMP=$(echo "$BACKUP_NAME" | sed -n 's/mythicaldash_backup_\(.*\)\.tar\.gz/\1/p')

		echo -e "  ${GREEN}[$index]${NC} ${BOLD}$BACKUP_NAME${NC}"
		echo -e "     ${BLUE}• Size:${NC} $BACKUP_SIZE"
		echo -e "     ${BLUE}• Date:${NC} $BACKUP_DATE"
		echo -e "     ${BLUE}• Path:${NC} $backup_file"
		echo ""
		index=$((index + 1))
	done

	draw_hr
	log_info "Total backups: ${#BACKUP_FILES[@]}"
}

restore_backup() {
	log_step "Restoring MythicalDash from backup..."

	if [ ! -f /var/www/mythicaldash/.installed ]; then
		log_error "MythicalDash is not installed. Please install first before restoring."
		return 1
	fi

	# List backups and let user choose
	if [ ! -d "$BACKUP_DIR" ]; then
		log_error "Backup directory does not exist. No backups found."
		return 1
	fi

	mapfile -t BACKUP_FILES < <(sudo find "$BACKUP_DIR" -name "mythicaldash_backup_*.tar.gz" -type f 2>/dev/null | sort -r)

	if [ ${#BACKUP_FILES[@]} -eq 0 ]; then
		log_error "No backups found in $BACKUP_DIR"
		return 1
	fi

	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "Select Backup to Restore" "$CYAN"
	draw_hr
	echo ""

	local index=1
	declare -A BACKUP_MAP
	for backup_file in "${BACKUP_FILES[@]}"; do
		BACKUP_NAME=$(basename "$backup_file")
		BACKUP_SIZE=$(sudo du -h "$backup_file" | cut -f1)
		BACKUP_DATE=$(sudo stat -c %y "$backup_file" 2>/dev/null | cut -d' ' -f1,2 | cut -d'.' -f1 || echo "Unknown")

		echo -e "  ${GREEN}[$index]${NC} ${BOLD}$BACKUP_NAME${NC}"
		echo -e "     ${BLUE}• Size:${NC} $BACKUP_SIZE"
		echo -e "     ${BLUE}• Date:${NC} $BACKUP_DATE"
		echo ""
		BACKUP_MAP[$index]="$backup_file"
		index=$((index + 1))
	done

	draw_hr
	echo ""
	echo -e "${RED}${BOLD}⚠️  WARNING: Restoring will replace all current Panel data!${NC}"
	echo -e "${YELLOW}This operation will:${NC}"
	echo -e "  ${RED}•${NC} Stop all MythicalDash containers"
	echo -e "  ${RED}•${NC} Replace database with backup data"
	echo -e "  ${RED}•${NC} Replace all volumes with backup data"
	echo -e "  ${RED}•${NC} Restart containers with restored data"
	echo ""
	draw_hr

	backup_choice=""
	while [[ ! "$backup_choice" =~ ^[0-9]+$ ]] || [ "$backup_choice" -lt 1 ] || [ "$backup_choice" -gt ${#BACKUP_FILES[@]} ]; do
		prompt "${BOLD}Select backup to restore${NC} ${BLUE}(1-${#BACKUP_FILES[@]})${NC}: " backup_choice
		if [[ ! "$backup_choice" =~ ^[0-9]+$ ]] || [ "$backup_choice" -lt 1 ] || [ "$backup_choice" -gt ${#BACKUP_FILES[@]} ]; then
			echo -e "${RED}Invalid input.${NC} Please enter a number between ${YELLOW}1${NC} and ${YELLOW}${#BACKUP_FILES[@]}${NC}."
			sleep 1
		fi
	done

	SELECTED_BACKUP="${BACKUP_MAP[$backup_choice]}"
	BACKUP_NAME=$(basename "$SELECTED_BACKUP")

	echo ""
	draw_hr
	confirm_restore=""
	prompt "${BOLD}${RED}Are you absolutely sure you want to restore from $BACKUP_NAME?${NC} ${BLUE}(type 'yes' to confirm)${NC}: " confirm_restore
	if [ "$confirm_restore" != "yes" ]; then
		echo -e "${GREEN}Restore cancelled.${NC}"
		return 0
	fi

	log_info "Stopping MythicalDash containers..."
	if ! run_with_spinner "Stopping containers" "Containers stopped." \
		bash -c "cd /var/www/mythicaldash && sudo docker compose down"; then
		log_error "Failed to stop containers"
		return 1
	fi

	# Create temporary directory for extraction
	TEMP_RESTORE_DIR=$(mktemp -d)
	trap 'rm -rf "$TEMP_RESTORE_DIR"' EXIT

	log_info "Extracting backup..."
	if ! sudo tar -xzf "$SELECTED_BACKUP" -C "$TEMP_RESTORE_DIR" 2>>"$LOG_FILE"; then
		log_error "Failed to extract backup"
		return 1
	fi

	log_info "Restoring database from volumes (volume-only restore method)..."
	# Check if we have mariadb_data volume backup
	HAS_MARIADB_VOLUME=false
	MARIADB_VOLUME_FILE=""

	# Look for mariadb_data volume in backup (could be prefixed or standard name)
	if [ -d "${TEMP_RESTORE_DIR}/volumes" ]; then
		for volume_file in "${TEMP_RESTORE_DIR}/volumes"/*mariadb_data*.tar.gz; do
			if [ -f "$volume_file" ]; then
				HAS_MARIADB_VOLUME=true
				MARIADB_VOLUME_FILE="$volume_file"
				break
			fi
		done
	fi

	if [ "$HAS_MARIADB_VOLUME" = false ]; then
		log_error "No mariadb_data volume backup found. Cannot restore database."
		return 1
	fi

	log_info "Database will be restored from mariadb_data volume (raw files)"
	log_info "Note: Ensure MariaDB version matches between backup and restore for best compatibility"

	log_info "Restoring volumes..."
	# Restore volumes - mariadb_data must be restored first (before starting MySQL)
	if [ -d "${TEMP_RESTORE_DIR}/volumes" ]; then
		# Restore mariadb_data first (critical for database)
		if [ "$HAS_MARIADB_VOLUME" = true ] && [ -n "$MARIADB_VOLUME_FILE" ]; then
			VOLUME_NAME=$(basename "$MARIADB_VOLUME_FILE" .tar.gz)
			log_info "Restoring volume: $VOLUME_NAME (database will be restored from this)"

			# Remove existing volume if it exists
			sudo docker volume rm "$VOLUME_NAME" >/dev/null 2>&1 || true

			# Create new volume and restore data
			if sudo docker volume create "$VOLUME_NAME" >/dev/null 2>&1; then
				if sudo docker run --rm -v "$VOLUME_NAME":/target -v "$(dirname "$MARIADB_VOLUME_FILE")":/backup alpine sh -c "cd /target && tar xzf /backup/$(basename "$MARIADB_VOLUME_FILE")" 2>>"$LOG_FILE"; then
					log_success "Volume $VOLUME_NAME restored (database restored from raw files)"
				else
					log_error "Failed to restore $VOLUME_NAME volume"
					return 1
				fi
			else
				log_error "Failed to create volume $VOLUME_NAME"
				return 1
			fi
		fi

		# Restore other volumes
		for volume_file in "${TEMP_RESTORE_DIR}/volumes"/*.tar.gz; do
			if [ -f "$volume_file" ]; then
				VOLUME_NAME=$(basename "$volume_file" .tar.gz)

				# Skip mariadb_data if we already restored it above
				if [[ "$VOLUME_NAME" =~ mariadb_data ]] && [ "$HAS_MARIADB_VOLUME" = true ]; then
					continue
				fi

				log_info "Restoring volume: $VOLUME_NAME"

				# Remove existing volume if it exists
				sudo docker volume rm "$VOLUME_NAME" >/dev/null 2>&1 || true

				# Create new volume and restore data
				if sudo docker volume create "$VOLUME_NAME" >/dev/null 2>&1; then
					if sudo docker run --rm -v "$VOLUME_NAME":/target -v "$(dirname "$volume_file")":/backup alpine sh -c "cd /target && tar xzf /backup/$(basename "$volume_file")" 2>>"$LOG_FILE"; then
						log_success "Volume $VOLUME_NAME restored"
					else
						log_warn "Failed to restore volume $VOLUME_NAME"
					fi
				else
					log_warn "Failed to create volume $VOLUME_NAME"
				fi
			fi
		done
	else
		log_error "Volumes backup not found in archive"
		return 1
	fi

	log_info "Restoring configuration files..."
	# Restore config files (optional - ask user)
	if [ -d "${TEMP_RESTORE_DIR}/config" ]; then
		restore_config=""
		prompt "${BOLD}Restore configuration files (docker-compose.yml, .env)?${NC} ${BLUE}(y/n)${NC}: " restore_config
		if [[ "$restore_config" =~ ^[yY]$ ]]; then
			if [ -f "${TEMP_RESTORE_DIR}/config/docker-compose.yml" ]; then
				sudo cp "${TEMP_RESTORE_DIR}/config/docker-compose.yml" /var/www/mythicaldash/docker-compose.yml
				log_info "docker-compose.yml restored"
			fi
			if [ -f "${TEMP_RESTORE_DIR}/config/.env" ]; then
				sudo cp "${TEMP_RESTORE_DIR}/config/.env" /var/www/mythicaldash/.env
				sudo chmod 600 /var/www/mythicaldash/.env
				log_info ".env restored"
			fi
		fi
	fi

	log_info "Starting MythicalDash containers..."
	if ! run_with_spinner "Starting containers" "Containers started." \
		bash -c "cd /var/www/mythicaldash && sudo docker compose up -d"; then
		log_error "Failed to start containers"
		return 1
	fi

	log_success "Backup restored successfully from $BACKUP_NAME"
	log_warn "Please verify that the Panel is working correctly after restoration."
	return 0
}

delete_backup() {
	log_step "Deleting MythicalDash backup..."

	if [ ! -d "$BACKUP_DIR" ]; then
		log_error "Backup directory does not exist. No backups found."
		return 1
	fi

	mapfile -t BACKUP_FILES < <(sudo find "$BACKUP_DIR" -name "mythicaldash_backup_*.tar.gz" -type f 2>/dev/null | sort -r)

	if [ ${#BACKUP_FILES[@]} -eq 0 ]; then
		log_error "No backups found in $BACKUP_DIR"
		return 1
	fi

	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "Select Backup to Delete" "$CYAN"
	draw_hr
	echo ""

	local index=1
	declare -A BACKUP_MAP
	for backup_file in "${BACKUP_FILES[@]}"; do
		BACKUP_NAME=$(basename "$backup_file")
		BACKUP_SIZE=$(sudo du -h "$backup_file" | cut -f1)
		BACKUP_DATE=$(sudo stat -c %y "$backup_file" 2>/dev/null | cut -d' ' -f1,2 | cut -d'.' -f1 || echo "Unknown")

		echo -e "  ${GREEN}[$index]${NC} ${BOLD}$BACKUP_NAME${NC}"
		echo -e "     ${BLUE}• Size:${NC} $BACKUP_SIZE"
		echo -e "     ${BLUE}• Date:${NC} $BACKUP_DATE"
		echo ""
		BACKUP_MAP[$index]="$backup_file"
		index=$((index + 1))
	done

	draw_hr
	echo ""
	echo -e "${RED}${BOLD}⚠️  WARNING: This will permanently delete the backup file!${NC}"
	draw_hr

	backup_choice=""
	while [[ ! "$backup_choice" =~ ^[0-9]+$ ]] || [ "$backup_choice" -lt 1 ] || [ "$backup_choice" -gt ${#BACKUP_FILES[@]} ]; do
		prompt "${BOLD}Select backup to delete${NC} ${BLUE}(1-${#BACKUP_FILES[@]})${NC}: " backup_choice
		if [[ ! "$backup_choice" =~ ^[0-9]+$ ]] || [ "$backup_choice" -lt 1 ] || [ "$backup_choice" -gt ${#BACKUP_FILES[@]} ]; then
			echo -e "${RED}Invalid input.${NC} Please enter a number between ${YELLOW}1${NC} and ${YELLOW}${#BACKUP_FILES[@]}${NC}."
			sleep 1
		fi
	done

	SELECTED_BACKUP="${BACKUP_MAP[$backup_choice]}"
	BACKUP_NAME=$(basename "$SELECTED_BACKUP")

	echo ""
	draw_hr
	confirm_delete=""
	prompt "${BOLD}${RED}Are you sure you want to delete $BACKUP_NAME?${NC} ${BLUE}(type 'yes' to confirm)${NC}: " confirm_delete
	if [ "$confirm_delete" != "yes" ]; then
		echo -e "${GREEN}Deletion cancelled.${NC}"
		return 0
	fi

	if sudo rm -f "$SELECTED_BACKUP" 2>>"$LOG_FILE"; then
		log_success "Backup deleted: $BACKUP_NAME"
		return 0
	else
		log_error "Failed to delete backup"
		return 1
	fi
}

export_migration() {
	log_step "Creating migration package for MythicalDash..."

	if [ ! -f /var/www/mythicaldash/.installed ]; then
		log_error "MythicalDash is not installed. Nothing to export."
		return 1
	fi

	# Check if containers are running
	if ! sudo docker ps --format '{{.Names}}' 2>/dev/null | grep -q "mythicaldash_v3_backend\|mythicaldash_v3_mysql"; then
		log_error "MythicalDash containers are not running. Cannot create migration package."
		return 1
	fi

	# Create migration directory
	MIGRATION_DIR="/var/www/mythicaldash/migrations"
	sudo mkdir -p "$MIGRATION_DIR"

	# Generate migration filename with timestamp
	MIGRATION_TIMESTAMP=$(date +%Y%m%d_%H%M%S)
	MIGRATION_NAME="mythicaldash_migration_${MIGRATION_TIMESTAMP}.tar.gz"
	MIGRATION_PATH="${MIGRATION_DIR}/${MIGRATION_NAME}"

	log_info "Migration package will be saved to: $MIGRATION_PATH"

	# Create temporary directory for migration contents
	TEMP_MIGRATION_DIR=$(mktemp -d)
	trap 'rm -rf "$TEMP_MIGRATION_DIR"' EXIT

	log_info "Exporting Docker volumes (volume-only method)..."
	# Export volumes only - this is the safest and most reliable method
	VOLUMES_DIR="${TEMP_MIGRATION_DIR}/volumes"
	mkdir -p "$VOLUMES_DIR"

	# Get actual volume names from running containers (same logic as create_backup)
	VOLUMES_FOUND=0
	declare -a ACTUAL_VOLUMES=()

	# Get volumes directly from running containers
	for container in mythicaldash_v3_mysql mythicaldash_v3_backend mythicaldash_v3_redis; do
		if sudo docker ps --format '{{.Names}}' 2>/dev/null | grep -q "^${container}$"; then
			# Get volume names from container mounts
			while IFS= read -r volume_name; do
				if [ -n "$volume_name" ] && [[ ! " ${ACTUAL_VOLUMES[*]} " =~ " ${volume_name} " ]]; then
					ACTUAL_VOLUMES+=("$volume_name")
				fi
			done < <(sudo docker inspect "$container" --format '{{range .Mounts}}{{if eq .Type "volume"}}{{.Name}}{{println}}{{end}}{{end}}' 2>/dev/null | grep -v "^$" || true)
		fi
	done

	# If no volumes found from containers, try docker volume ls with filter
	if [ ${#ACTUAL_VOLUMES[@]} -eq 0 ]; then
		log_info "Getting volumes from docker volume list..."
		while IFS= read -r volume_name; do
			if [ -n "$volume_name" ] && [[ "$volume_name" =~ ^mythicaldash ]]; then
				if [[ ! " ${ACTUAL_VOLUMES[*]} " =~ " ${volume_name} " ]]; then
					ACTUAL_VOLUMES+=("$volume_name")
				fi
			fi
		done < <(sudo docker volume ls --format "{{.Name}}" 2>/dev/null | grep "^mythicaldash" || true)
	fi

	# Fallback: Try known volume names if still nothing found
	if [ ${#ACTUAL_VOLUMES[@]} -eq 0 ]; then
		log_warn "Could not detect volumes from containers, trying known volume names..."
		ACTUAL_VOLUMES=("mythicaldash_mariadb_data" "mythicaldash_redis_data" "mythicaldash_mythicaldash_v3_attachments" "mythicaldash_mythicaldash_v3_snapshots")
	fi

	# Export each volume that actually exists
	for volume in "${ACTUAL_VOLUMES[@]}"; do
		if sudo docker volume inspect "$volume" >/dev/null 2>&1; then
			log_info "Exporting volume: $volume"
			if sudo docker run --rm -v "$volume":/source -v "$VOLUMES_DIR":/backup alpine tar czf "/backup/${volume}.tar.gz" -C /source . 2>>"$LOG_FILE"; then
				log_success "Volume $volume exported"
				VOLUMES_FOUND=$((VOLUMES_FOUND + 1))
			else
				log_warn "Failed to export volume $volume"
			fi
		fi
	done

	# Check if we got the critical mariadb_data volume
	MARIADB_EXPORTED=false
	for volume in "${ACTUAL_VOLUMES[@]}"; do
		if [[ "$volume" =~ mariadb_data ]] && sudo docker volume inspect "$volume" >/dev/null 2>&1; then
			if [ -f "${VOLUMES_DIR}/${volume}.tar.gz" ]; then
				MARIADB_EXPORTED=true
				break
			fi
		fi
	done

	if [ $VOLUMES_FOUND -eq 0 ]; then
		log_error "No volumes found to export. Is MythicalDash installed and running?"
		return 1
	fi

	if [ "$MARIADB_EXPORTED" = false ]; then
		log_error "mariadb_data volume not found or could not be exported. This is required for database migration."
		return 1
	fi

	log_success "Exported $VOLUMES_FOUND volume(s) including database (mariadb_data)"

	log_info "Exporting configuration files..."
	# Export configuration files
	CONFIG_DIR="${TEMP_MIGRATION_DIR}/config"
	mkdir -p "$CONFIG_DIR"

	# Copy important config files
	if [ -f /var/www/mythicaldash/docker-compose.yml ]; then
		sudo cp /var/www/mythicaldash/docker-compose.yml "$CONFIG_DIR/" 2>>"$LOG_FILE"
	fi
	if [ -f /var/www/mythicaldash/.env ]; then
		sudo cp /var/www/mythicaldash/.env "$CONFIG_DIR/" 2>>"$LOG_FILE"
	fi

	# Create migration info file
	cat >"${TEMP_MIGRATION_DIR}/migration_info.txt" <<EOF
MythicalDash Migration Package
Created: $(date)
Migration Name: $MIGRATION_NAME
Source Server: $(hostname)
Source IP: $(curl -s ifconfig.me 2>/dev/null || echo "Unknown")
Version: $(grep -oP 'image: ghcr.io/mythicalltd/mythicaldash_v3-backend:\K[^\s]+' /var/www/mythicaldash/docker-compose.yml 2>/dev/null || echo "unknown")
Backup Method: Volume-only backup (safest and most reliable)

IMPORTANT: This is a migration package for moving MythicalDash to another server.
To import this package on the destination server:
1. Transfer this file to the destination server
2. Launch the MythicalDash installer and navigate to: Panel > Backup Manager > Import Migration
3. Follow the import wizard to complete the migration

This package contains:
- All Docker volumes (mariadb_data, attachments, config, snapshots, redis_data, etc.)
- Configuration files (docker-compose.yml, .env)
- Database is included in mariadb_data volume (raw files - safest method)
EOF

	# Create README with transfer instructions
	cat >"${TEMP_MIGRATION_DIR}/README_MIGRATION.txt" <<'EOF'
========================================
MythicalDash Migration Package
========================================

This package contains a complete export of your MythicalDash installation
that can be imported on another server.

TRANSFER METHODS:

Method 1: SCP (Recommended)
----------------------------
On the DESTINATION server, run:
  scp user@source-server:/var/www/mythicaldash/migrations/mythicaldash_migration_*.tar.gz ./

Method 2: Manual Download
-------------------------
1. Download this file from the source server using:
   - SFTP client (FileZilla, WinSCP, etc.)
   - HTTP server (if configured)
   - Cloud storage (upload from source, download on destination)

2. Transfer to destination server at:
   /var/www/mythicaldash/migrations/

Method 3: rsync
---------------
On the DESTINATION server, run:
  rsync -avz user@source-server:/var/www/mythicaldash/migrations/mythicaldash_migration_*.tar.gz ./

IMPORT INSTRUCTIONS:
--------------------
1. Ensure MythicalDash is installed on the destination server
2. Launch the MythicalDash installer
3. Navigate to: Panel > Backup Manager > Import Migration
4. Follow the import wizard to complete the migration

NOTE: The destination server must have MythicalDash installed before
importing this migration package.
EOF

	log_info "Compressing migration package..."
	# Create compressed archive
	if sudo tar -czf "$MIGRATION_PATH" -C "$TEMP_MIGRATION_DIR" . 2>>"$LOG_FILE"; then
		sudo chmod 600 "$MIGRATION_PATH"
		MIGRATION_SIZE=$(du -h "$MIGRATION_PATH" | cut -f1)
		log_success "Migration package created: $MIGRATION_NAME ($MIGRATION_SIZE)"

		# Display transfer instructions
		if [ -t 1 ]; then clear; fi
		print_banner
		draw_hr
		print_centered "Migration Package Created" "$GREEN"
		draw_hr
		echo ""
		echo -e "  ${GREEN}${BOLD}✓${NC} Migration package: ${CYAN}$MIGRATION_NAME${NC}"
		echo -e "  ${BLUE}• Size:${NC} $MIGRATION_SIZE"
		echo -e "  ${BLUE}• Location:${NC} $MIGRATION_PATH"
		echo ""
		draw_hr
		print_centered "Transfer Instructions" "$CYAN"
		draw_hr
		echo ""
		echo -e "${BOLD}To transfer this package to another server:${NC}"
		echo ""
		echo -e "${GREEN}Method 1: SCP (from destination server)${NC}"
		echo -e "  ${CYAN}scp${NC} ${YELLOW}user@$(hostname):$MIGRATION_PATH${NC} ${CYAN}./${NC}"
		echo ""
		echo -e "${GREEN}Method 2: rsync (from destination server)${NC}"
		echo -e "  ${CYAN}rsync -avz${NC} ${YELLOW}user@$(hostname):$MIGRATION_PATH${NC} ${CYAN}./${NC}"
		echo ""
		echo -e "${GREEN}Method 3: Manual Download${NC}"
		echo -e "  ${BLUE}1.${NC} Download the file from: ${CYAN}$MIGRATION_PATH${NC}"
		echo -e "  ${BLUE}2.${NC} Upload to destination server"
		echo -e "  ${BLUE}3.${NC} Place in: ${CYAN}/var/www/mythicaldash/migrations/${NC}"
		echo ""
		draw_hr
		print_centered "Import Instructions" "$YELLOW"
		draw_hr
		echo ""
		echo -e "${BOLD}On the destination server:${NC}"
		echo -e "  ${BLUE}1.${NC} Ensure MythicalDash is installed"
		echo -e "  ${BLUE}2.${NC} Launch the MythicalDash installer"
		echo -e "  ${BLUE}3.${NC} Navigate to: ${CYAN}Panel > Backup Manager > Import Migration${NC}"
		echo -e "  ${BLUE}4.${NC} Follow the import wizard"
		echo ""
		echo -e "${YELLOW}${BOLD}Note:${NC} The destination server must have MythicalDash installed before importing."
		echo ""
		draw_hr

		return 0
	else
		log_error "Failed to create migration package"
		return 1
	fi
}

import_migration() {
	log_step "Importing MythicalDash migration package..."

	# Check if already installed (optional - can import during fresh install)
	MIGRATION_DIR="/var/www/mythicaldash/migrations"
	sudo mkdir -p "$MIGRATION_DIR"

	# Look for migration packages
	mapfile -t MIGRATION_FILES < <(sudo find "$MIGRATION_DIR" -name "mythicaldash_migration_*.tar.gz" -type f 2>/dev/null | sort -r)

	# Also check current directory and common locations
	mapfile -t ADDITIONAL_FILES < <(sudo find /root /home -maxdepth 2 -name "mythicaldash_migration_*.tar.gz" -type f 2>/dev/null | head -5)
	MIGRATION_FILES+=("${ADDITIONAL_FILES[@]}")

	if [ ${#MIGRATION_FILES[@]} -eq 0 ]; then
		log_warn "No migration packages found in $MIGRATION_DIR"
		echo ""
		draw_hr
		echo -e "${YELLOW}${BOLD}Migration Package Not Found${NC}"
		draw_hr
		echo ""
		echo -e "${BLUE}Please provide the migration package:${NC}"
		echo ""
		echo -e "${GREEN}Option 1:${NC} Place the migration file in: ${CYAN}$MIGRATION_DIR${NC}"
		echo -e "${GREEN}Option 2:${NC} Provide the full path to the migration file"
		echo ""
		draw_hr
		migration_path=""
		while [ -z "$migration_path" ]; do
			prompt "${BOLD}Enter path to migration package${NC} ${BLUE}(or press Enter to cancel)${NC}: " migration_path
			if [ -z "$migration_path" ]; then
				echo -e "${GREEN}Import cancelled.${NC}"
				return 0
			fi
			if [ ! -f "$migration_path" ]; then
				echo -e "${RED}File not found: $migration_path${NC}"
				migration_path=""
			fi
		done
		SELECTED_MIGRATION="$migration_path"
	else
		if [ -t 1 ]; then clear; fi
		print_banner
		draw_hr
		print_centered "Select Migration Package" "$CYAN"
		draw_hr
		echo ""

		local index=1
		declare -A MIGRATION_MAP
		for migration_file in "${MIGRATION_FILES[@]}"; do
			MIGRATION_NAME=$(basename "$migration_file")
			MIGRATION_SIZE=$(sudo du -h "$migration_file" | cut -f1)
			MIGRATION_DATE=$(sudo stat -c %y "$migration_file" 2>/dev/null | cut -d' ' -f1,2 | cut -d'.' -f1 || echo "Unknown")

			echo -e "  ${GREEN}[$index]${NC} ${BOLD}$MIGRATION_NAME${NC}"
			echo -e "     ${BLUE}• Size:${NC} $MIGRATION_SIZE"
			echo -e "     ${BLUE}• Date:${NC} $MIGRATION_DATE"
			echo -e "     ${BLUE}• Path:${NC} $migration_file"
			echo ""
			MIGRATION_MAP[$index]="$migration_file"
			index=$((index + 1))
		done

		echo -e "  ${CYAN}[$index]${NC} ${BOLD}Specify custom path${NC}"
		echo ""
		draw_hr
		echo ""
		echo -e "${RED}${BOLD}⚠️  WARNING: Importing will replace all current Panel data!${NC}"
		echo -e "${YELLOW}This operation will:${NC}"
		if [ -f /var/www/mythicaldash/.installed ]; then
			echo -e "  ${RED}•${NC} Stop all MythicalDash containers"
			echo -e "  ${RED}•${NC} Replace database with migration data"
			echo -e "  ${RED}•${NC} Replace all volumes with migration data"
			echo -e "  ${RED}•${NC} Restart containers with imported data"
		else
			echo -e "  ${RED}•${NC} Install MythicalDash with imported data"
			echo -e "  ${RED}•${NC} Restore database and volumes from migration"
		fi
		echo ""
		draw_hr

		migration_choice=""
		while [[ ! "$migration_choice" =~ ^[0-9]+$ ]] || [ "$migration_choice" -lt 1 ] || [ "$migration_choice" -gt $index ]; do
			prompt "${BOLD}Select migration package${NC} ${BLUE}(1-$index)${NC}: " migration_choice
			if [[ ! "$migration_choice" =~ ^[0-9]+$ ]] || [ "$migration_choice" -lt 1 ] || [ "$migration_choice" -gt $index ]; then
				echo -e "${RED}Invalid input.${NC} Please enter a number between ${YELLOW}1${NC} and ${YELLOW}$index${NC}."
				sleep 1
			fi
		done

		if [ "$migration_choice" -eq $index ]; then
			migration_path=""
			while [ -z "$migration_path" ]; do
				prompt "${BOLD}Enter full path to migration package${NC}: " migration_path
				if [ -z "$migration_path" ]; then
					echo -e "${RED}Path cannot be empty.${NC}"
				elif [ ! -f "$migration_path" ]; then
					echo -e "${RED}File not found: $migration_path${NC}"
					migration_path=""
				fi
			done
			SELECTED_MIGRATION="$migration_path"
		else
			SELECTED_MIGRATION="${MIGRATION_MAP[$migration_choice]}"
		fi
	fi

	MIGRATION_NAME=$(basename "$SELECTED_MIGRATION")

	echo ""
	draw_hr
	confirm_import=""
	prompt "${BOLD}${RED}Are you absolutely sure you want to import from $MIGRATION_NAME?${NC} ${BLUE}(type 'yes' to confirm)${NC}: " confirm_import
	if [ "$confirm_import" != "yes" ]; then
		echo -e "${GREEN}Import cancelled.${NC}"
		return 0
	fi

	# Check if Panel is installed
	if [ -f /var/www/mythicaldash/.installed ]; then
		log_info "Stopping MythicalDash containers..."
		if ! run_with_spinner "Stopping containers" "Containers stopped." \
			bash -c "cd /var/www/mythicaldash && sudo docker compose down"; then
			log_error "Failed to stop containers"
			return 1
		fi
	else
		log_info "MythicalDash not installed. Will install with imported data."
		# Ensure directories exist
		sudo mkdir -p /var/www/mythicaldash
		sudo mkdir -p "$BACKUP_DIR"
	fi

	# Create temporary directory for extraction
	TEMP_IMPORT_DIR=$(mktemp -d)
	trap 'rm -rf "$TEMP_IMPORT_DIR"' EXIT

	log_info "Extracting migration package..."
	if ! sudo tar -xzf "$SELECTED_MIGRATION" -C "$TEMP_IMPORT_DIR" 2>>"$LOG_FILE"; then
		log_error "Failed to extract migration package"
		return 1
	fi

	# Check if this is a fresh install or update
	if [ ! -f /var/www/mythicaldash/.installed ]; then
		log_info "Fresh installation detected. Setting up MythicalDash first..."

		# Install Docker if not present
		if ! command -v docker &>/dev/null; then
			log_step "Installing Docker engine (required for import)..."
			curl -sSL https://get.docker.com/ | CHANNEL=stable bash >>"$LOG_FILE" 2>&1
			sudo systemctl enable --now docker 2>&1 | tee -a "$LOG_FILE" >/dev/null
			sudo usermod -aG docker "$USER" 2>&1 | tee -a "$LOG_FILE" >/dev/null || true
			log_success "Docker installed"
		fi

		# Download docker-compose.yml if not present
		if [ ! -f /var/www/mythicaldash/docker-compose.yml ]; then
			if [ -f "${TEMP_IMPORT_DIR}/config/docker-compose.yml" ]; then
				sudo cp "${TEMP_IMPORT_DIR}/config/docker-compose.yml" /var/www/mythicaldash/docker-compose.yml
				log_info "Using docker-compose.yml from migration package"
			else
				log_info "Downloading docker-compose.yml..."
				COMPOSE_URL=$(get_compose_file_url)
				if ! run_with_spinner "Downloading docker-compose.yml" "docker-compose.yml downloaded." \
					curl -fsSL -o /var/www/mythicaldash/docker-compose.yml "$COMPOSE_URL"; then
					log_error "Failed to download docker-compose.yml"
					return 1
				fi
			fi
		fi

		# Copy .env if present in migration
		if [ -f "${TEMP_IMPORT_DIR}/config/.env" ]; then
			sudo cp "${TEMP_IMPORT_DIR}/config/.env" /var/www/mythicaldash/.env
			sudo chmod 600 /var/www/mythicaldash/.env
			log_info "Restored .env from migration package"
		fi
	fi

	log_info "Restoring database..."
	# Restore database
	if [ -f "${TEMP_IMPORT_DIR}/database.sql" ]; then
		# Start MySQL container
		if ! run_with_spinner "Starting MySQL for import" "MySQL started." \
			bash -c "cd /var/www/mythicaldash && sudo docker compose up -d mysql"; then
			log_error "Failed to start MySQL container"
			return 1
		fi

		# Wait for MySQL to be ready
		log_info "Waiting for MySQL to be ready..."
		max_attempts=30
		attempt=0
		while [ $attempt -lt $max_attempts ]; do
			if sudo docker exec mythicaldash_v3_mysql mysql -u root -pmythicaldash_v3_root -e "SELECT 1" >/dev/null 2>&1; then
				break
			fi
			attempt=$((attempt + 1))
			sleep 1
		done

		if [ $attempt -eq $max_attempts ]; then
			log_error "MySQL did not become ready in time"
			return 1
		fi

		# Drop and recreate database
		log_info "Preparing database for import..."
		sudo docker exec -i mythicaldash_v3_mysql mysql -u root -pmythicaldash_v3_root <<EOF 2>>"$LOG_FILE" || true
DROP DATABASE IF EXISTS mythicaldash_v3;
CREATE DATABASE mythicaldash_v3;
EOF

		# Restore database
		if sudo docker exec -i mythicaldash_v3_mysql mysql -u root -pmythicaldash_v3_root mythicaldash_v3 <"${TEMP_IMPORT_DIR}/database.sql" 2>>"$LOG_FILE"; then
			log_success "Database imported"
		else
			log_error "Failed to import database"
			return 1
		fi
	else
		log_warn "Database backup not found in migration package"
	fi

	log_info "Restoring volumes..."
	# Restore volumes
	if [ -d "${TEMP_IMPORT_DIR}/volumes" ]; then
		for volume_file in "${TEMP_IMPORT_DIR}/volumes"/*.tar.gz; do
			if [ -f "$volume_file" ]; then
				VOLUME_NAME=$(basename "$volume_file" .tar.gz)
				log_info "Restoring volume: $VOLUME_NAME"

				# Remove existing volume if it exists
				sudo docker volume rm "$VOLUME_NAME" >/dev/null 2>&1 || true

				# Create new volume and restore data
				if sudo docker volume create "$VOLUME_NAME" >/dev/null 2>&1; then
					if sudo docker run --rm -v "$VOLUME_NAME":/target -v "$(dirname "$volume_file")":/backup alpine sh -c "cd /target && tar xzf /backup/$(basename "$volume_file")" 2>>"$LOG_FILE"; then
						log_success "Volume $VOLUME_NAME restored"
					else
						log_warn "Failed to restore volume $VOLUME_NAME"
					fi
				else
					log_warn "Failed to create volume $VOLUME_NAME"
				fi
			fi
		done
	else
		log_warn "Volumes backup not found in migration package"
	fi

	log_info "Starting MythicalDash containers..."
	if ! run_with_spinner "Starting containers" "Containers started." \
		bash -c "cd /var/www/mythicaldash && sudo docker compose up -d"; then
		log_error "Failed to start containers"
		return 1
	fi

	# Mark as installed
	sudo touch /var/www/mythicaldash/.installed

	# Install global mythicaldash command
	install_mythicaldash_command

	log_success "Migration import completed successfully from $MIGRATION_NAME"
	log_warn "Please verify that the Panel is working correctly after import."
	log_info "You may need to update DNS records and SSL certificates for the new server."

	if [ -t 1 ]; then clear; fi
	print_banner
	draw_hr
	print_centered "Migration Import Complete" "$GREEN"
	draw_hr
	echo ""
	echo -e "  ${GREEN}${BOLD}✓${NC} Migration package imported: ${CYAN}$MIGRATION_NAME${NC}"
	echo ""
	draw_hr
	print_centered "Next Steps" "$CYAN"
	draw_hr
	echo ""
	echo -e "  ${GREEN}1.${NC} ${BLUE}Wait 2-3 minutes${NC} for containers to fully start"
	echo -e "  ${GREEN}2.${NC} ${BLUE}Update DNS records${NC} to point to this server (if domain changed)"
	echo -e "  ${GREEN}3.${NC} ${BLUE}Update SSL certificates${NC} if using a different domain"
	echo -e "  ${GREEN}4.${NC} ${BLUE}Verify Panel access${NC} and test functionality"
	echo -e "  ${GREEN}5.${NC} ${BLUE}Update Wings nodes${NC} if Panel URL changed"
	echo ""
	draw_hr

	return 0
}

# Function to modify docker-compose.yml to use dev image tags
modify_compose_for_dev() {
	local compose_file="$1"
	local backend_tag="$2"
	local frontend_tag="$3"

	log_info "Modifying docker-compose.yml to use dev images..."
	log_info "Backend tag: $backend_tag"
	log_info "Frontend tag: $frontend_tag"

	# Backup original file
	if [ -f "$compose_file" ]; then
		sudo cp "$compose_file" "${compose_file}.backup"
	fi

	# Use sed to replace image tags (MythicalDash v3 images)
	sudo sed -i "s|image: ghcr.io/mythicalltd/mythicaldash_v3-backend:latest|image: ghcr.io/mythicalltd/mythicaldash_v3-backend:${backend_tag}|g" "$compose_file"
	sudo sed -i "s|image: ghcr.io/mythicalltd/mythicaldash_v3-backend:.*|image: ghcr.io/mythicalltd/mythicaldash_v3-backend:${backend_tag}|g" "$compose_file"

	sudo sed -i "s|image: ghcr.io/mythicalltd/mythicaldash_v3-frontend:latest|image: ghcr.io/mythicalltd/mythicaldash_v3-frontend:${frontend_tag}|g" "$compose_file"
	sudo sed -i "s|image: ghcr.io/mythicalltd/mythicaldash_v3-frontend:.*|image: ghcr.io/mythicalltd/mythicaldash_v3-frontend:${frontend_tag}|g" "$compose_file"

	log_success "docker-compose.yml modified for dev images"
}

# Function to determine dev image tag based on options
get_dev_image_tag() {
	local tag="dev"

	if [ -n "$DEV_BRANCH" ]; then
		# Sanitize branch name (replace / with -)
		local sanitized_branch=$(echo "$DEV_BRANCH" | sed 's/\//-/g')
		tag="dev-${sanitized_branch}"

		if [ -n "$DEV_SHA" ]; then
			# Use short SHA (first 7 characters)
			local short_sha=$(echo "$DEV_SHA" | cut -c1-7)
			tag="dev-${sanitized_branch}-${short_sha}"
		fi
	else
		# Default to v3-remastered branch if no branch specified
		tag="dev-v3-remastered"
	fi

	echo "$tag"
}

# Function to get docker-compose file URL based on dev mode
get_compose_file_url() {
	local branch="v3-remastered"
	local compose_file="docker-compose.yml"

	# If dev mode is enabled, use docker-compose.v2.dev.yml if it exists; else keep docker-compose.yml
	if [ "$USE_DEV" = true ]; then
		# Use DEV_BRANCH if set, otherwise default to v3-remastered
		if [ -n "$DEV_BRANCH" ]; then
			branch="$DEV_BRANCH"
		fi
		# MythicalDash may use docker-compose.v2.dev.yml for dev; fallback to docker-compose.yml
		compose_file="docker-compose.yml"
	fi

	echo "https://raw.githubusercontent.com/MythicalLTD/MythicalDash/refs/heads/${branch}/${compose_file}"
}

# Docker-only flow
uninstall_docker() {
	if [ ! -f /var/www/mythicaldash/.installed ]; then
		log_warn "MythicalDash does not appear to be installed. Nothing to uninstall."
		support_hint
		return 0
	fi
	echo "Uninstalling MythicalDash (Docker)..."
	uninstall_cloudflare_tunnel
	# Stop and remove any cloudflared containers started by this installer
	if command -v docker >/dev/null 2>&1; then
		log_step "Removing Cloudflare Tunnel docker container(s) if present..."
		# Try by common name
		(docker rm -f cloudflared >/dev/null 2>&1 && log_info "Removed container 'cloudflared'") || true
		# Fallback: remove containers from the official image
		CF_IDS=$(docker ps -aq --filter ancestor=cloudflare/cloudflared:latest || true)
		if [ -n "$CF_IDS" ]; then
			docker rm -f "$CF_IDS" >/dev/null 2>&1 && log_info "Removed cloudflared container(s) by image"
		fi
	fi
	if [ -f /var/www/mythicaldash/docker-compose.yml ]; then
		log_step "Stopping and removing Docker containers..."
		(cd /var/www/mythicaldash && sudo docker compose down -v) >>"$LOG_FILE" 2>&1 || true
	fi
	# Remove secrets and sensitive files
	if [ -f /var/www/mythicaldash/.env ]; then
		echo "Removing .env file containing secrets..."
		sudo rm -f /var/www/mythicaldash/.env
	fi

	# Remove global mythicaldash command
	if [ -f /usr/local/bin/mythicaldash ]; then
		log_info "Removing global 'mythicaldash' command..."
		sudo rm -f /usr/local/bin/mythicaldash
	fi

	rm -rf /var/www/mythicaldash
	echo "Docker-based uninstallation complete."
}

ensure_env_cloudflare() {
	ENV_FILE=/var/www/mythicaldash/.env
	if [ -f "$ENV_FILE" ]; then
		log_info ".env already exists at /var/www/mythicaldash/.env. Skipping creation."
		return 0
	fi
	log_info "Creating /var/www/mythicaldash/.env for Cloudflare settings..."
	cat <<EOF | sudo tee "$ENV_FILE" >/dev/null
# Cloudflare settings used by the installer/uninstaller
CF_EMAIL=""
CF_API_KEY=""
CF_HOSTNAME=""
CF_TUNNEL_TOKEN=""
# These will be filled automatically if you choose Full Automatic mode:
ACCOUNT_ID=""
TUNNEL_ID=""
TUNNEL_NAME=""
ZONE_ID=""
EOF
	sudo chmod 600 "$ENV_FILE"
	log_info ".env created for Cloudflare."
}

# Function to check EOL dates and warn users
check_eol_status() {
	local os="$1"
	local version="$2"
	local current_date=$(date +%s)
	local eol_date=""
	local eol_extended_date=""
	local eol_name=""
	local eol_extended_name=""
	local status="supported"

	# Skip EOL check if OS or version is unknown
	if [ -z "$os" ] || [ "$os" = "unknown" ] || [ -z "$version" ] || [ "$version" = "unknown" ]; then
		EOL_STATUS="supported"
		EOL_DATE=""
		EOL_EXTENDED_DATE=""
		EOL_NAME=""
		EOL_EXTENDED_NAME=""
		return 0
	fi

	# Define EOL dates (Unix timestamps)
	# Use GNU date format (works on Debian/Ubuntu)
	case "$os" in
	debian)
		case "$version" in
		11)
			eol_date=$(date -d "2024-08-14" +%s 2>/dev/null || echo "")
			eol_extended_date=$(date -d "2026-08-31" +%s 2>/dev/null || echo "")
			eol_name="Standard Support"
			eol_extended_name="Extended LTS Support"
			;;
		12)
			eol_date=$(date -d "2026-06-10" +%s 2>/dev/null || echo "")
			eol_extended_date=$(date -d "2028-06-30" +%s 2>/dev/null || echo "")
			eol_name="Standard Support"
			eol_extended_name="Extended LTS Support"
			;;
		13)
			eol_date=$(date -d "2028-08-09" +%s 2>/dev/null || echo "")
			eol_extended_date=$(date -d "2030-06-30" +%s 2>/dev/null || echo "")
			eol_name="Standard Support"
			eol_extended_name="Extended LTS Support"
			;;
		esac
		;;
	ubuntu | ubuntu-server)
		case "$version" in
		22.04)
			eol_date=$(date -d "2027-04-01" +%s 2>/dev/null || echo "")
			eol_extended_date=$(date -d "2032-04-01" +%s 2>/dev/null || echo "")
			eol_name="Standard LTS Support"
			eol_extended_name="Extended Security Maintenance"
			;;
		24.04)
			eol_date=$(date -d "2029-04-01" +%s 2>/dev/null || echo "")
			eol_extended_date=$(date -d "2034-04-01" +%s 2>/dev/null || echo "")
			eol_name="Standard LTS Support"
			eol_extended_name="Extended Security Maintenance"
			;;
		25.04)
			eol_date=$(date -d "2026-01-01" +%s 2>/dev/null || echo "")
			eol_extended_date=""
			eol_name="Standard Support"
			eol_extended_name=""
			;;
		esac
		;;
	esac

	# Check EOL status (only if dates were successfully parsed)
	if [ -n "$eol_date" ] && [ "$eol_date" != "" ] && [ "$eol_date" -gt 0 ] 2>/dev/null; then
		if [ "$current_date" -ge "$eol_date" ] 2>/dev/null; then
			# Past standard EOL
			if [ -n "$eol_extended_date" ] && [ "$eol_extended_date" != "" ] && [ "$eol_extended_date" -gt 0 ] 2>/dev/null; then
				if [ "$current_date" -lt "$eol_extended_date" ] 2>/dev/null; then
					status="extended"
				else
					status="eol"
				fi
			else
				status="eol"
			fi
		else
			# Calculate days until EOL
			days_until_eol=$(((eol_date - current_date) / 86400)) 2>/dev/null || days_until_eol=999999
			if [ "$days_until_eol" -lt 90 ] && [ "$days_until_eol" -gt 0 ]; then
				status="warning"
			fi
		fi
	fi

	# Return status via global variables (bash limitation)
	EOL_STATUS="$status"
	EOL_DATE="$eol_date"
	EOL_EXTENDED_DATE="$eol_extended_date"
	EOL_NAME="$eol_name"
	EOL_EXTENDED_NAME="$eol_extended_name"
}

if [ -f /etc/os-release ]; then
	# shellcheck source=/dev/null
	. /etc/os-release
	OS="${ID:-unknown}"
	OS_VERSION="${VERSION_ID:-unknown}"

	# Check if OS and version are supported
	SUPPORTED=false
	if [ "$OS" = "debian" ]; then
		if [ "$OS_VERSION" = "11" ] || [ "$OS_VERSION" = "12" ] || [ "$OS_VERSION" = "13" ]; then
			SUPPORTED=true
		fi
	elif [ "$OS" = "ubuntu" ] || [ "$OS" = "ubuntu-server" ]; then
		# Support Ubuntu 22.04 LTS (Jammy), 24.04 LTS (Noble), and 25.04
		if [ "$OS_VERSION" = "22.04" ] || [ "$OS_VERSION" = "24.04" ] || [ "$OS_VERSION" = "25.04" ]; then
			SUPPORTED=true
		fi
	fi

	# Handle unsupported OS or missing/invalid OS information
	if [ "$SUPPORTED" = false ] || [ "$OS" = "unknown" ] || [ "$OS_VERSION" = "unknown" ]; then
		if [ "$SKIP_OS_CHECK" = true ]; then
			log_warn "OS check skipped via --skip-os-check flag"
			echo ""
			draw_hr
			echo -e "${YELLOW}${BOLD}⚠️  Warning: OS Check Skipped${NC}"
			draw_hr
			if [ "$OS" != "unknown" ] && [ "$OS_VERSION" != "unknown" ]; then
				echo -e "${YELLOW}You are using an unsupported OS: $OS $OS_VERSION${NC}"
			else
				echo -e "${YELLOW}Could not determine OS information from /etc/os-release${NC}"
				echo -e "${YELLOW}OS: ${OS:-not set}, Version: ${OS_VERSION:-not set}${NC}"
			fi
			echo -e "${YELLOW}This installer officially supports:${NC}"
			echo -e "  ${GREEN}•${NC} Debian 11, 12, or 13"
			echo -e "  ${GREEN}•${NC} Ubuntu 22.04 LTS, 24.04 LTS, or 25.04"
			echo ""
			echo -e "${BLUE}Continuing with installation at your own risk...${NC}"
			echo ""
			draw_hr
			sleep 3
			# Still check EOL status even if skipping OS check (if we have valid OS info)
			if [ "$OS" != "unknown" ] && [ "$OS_VERSION" != "unknown" ]; then
				check_eol_status "$OS" "$OS_VERSION" || true
			fi
		else
			log_error "Unsupported OS or version: $OS $OS_VERSION"
			echo -e "${RED}${BOLD}This installer only supports:${NC}"
			echo -e "  ${GREEN}•${NC} Debian 11, 12, or 13"
			echo -e "  ${GREEN}•${NC} Ubuntu 22.04 LTS, 24.04 LTS, or 25.04"
			echo -e ""
			if [ "$OS" != "unknown" ] && [ "$OS_VERSION" != "unknown" ]; then
				echo -e "${YELLOW}Your system: $OS $OS_VERSION${NC}"
			else
				echo -e "${YELLOW}Could not determine OS information from /etc/os-release${NC}"
				echo -e "${YELLOW}OS: ${OS:-not set}, Version: ${OS_VERSION:-not set}${NC}"
			fi
			echo ""
			echo -e "${BLUE}To bypass this check, use: ${BOLD}--skip-os-check${NC}"
			support_hint
			exit 1
		fi
	else
		# Check EOL status for supported OS
		check_eol_status "$OS" "$OS_VERSION"
	fi

	# Display EOL warnings if needed
	if [ "$EOL_STATUS" = "eol" ]; then
		echo ""
		draw_hr
		echo -e "${RED}${BOLD}⚠️  CRITICAL WARNING: End of Life Operating System${NC}"
		draw_hr
		echo -e "${YELLOW}Your system ($OS $OS_VERSION) has reached End of Life (EOL).${NC}"
		echo -e "${YELLOW}This means:${NC}"
		echo -e "  ${RED}•${NC} No security updates or patches are available"
		echo -e "  ${RED}•${NC} Your system is vulnerable to security issues"
		echo -e "  ${RED}•${NC} MythicalDash may not work correctly"
		echo ""
		echo -e "${BOLD}${RED}We strongly recommend upgrading to a supported OS version.${NC}"
		echo ""
		draw_hr
		eol_continue=""
		prompt "${BOLD}${RED}Do you want to continue anyway?${NC} ${BLUE}(NOT RECOMMENDED - type 'yes' to continue)${NC}: " eol_continue
		if [ "$eol_continue" != "yes" ]; then
			echo -e "${GREEN}Installation cancelled. Please upgrade your OS first.${NC}"
			exit 0
		fi
		log_warn "User chose to continue with EOL OS: $OS $OS_VERSION"
	elif [ "$EOL_STATUS" = "extended" ]; then
		echo ""
		draw_hr
		echo -e "${YELLOW}${BOLD}⚠️  Warning: Extended Support Period${NC}"
		draw_hr
		echo -e "${YELLOW}Your system ($OS $OS_VERSION) is past standard support but still in extended support.${NC}"
		echo -e "${BLUE}Extended support provides security updates but limited feature updates.${NC}"
		echo -e "${BLUE}Consider upgrading to a newer version when possible.${NC}"
		echo ""
		draw_hr
		sleep 2
	elif [ "$EOL_STATUS" = "warning" ]; then
		echo ""
		draw_hr
		echo -e "${YELLOW}${BOLD}⚠️  Notice: Approaching End of Life${NC}"
		draw_hr
		if [ -n "$EOL_EXTENDED_DATE" ]; then
			echo -e "${YELLOW}Your system ($OS $OS_VERSION) will reach End of Life soon.${NC}"
			echo -e "${BLUE}Standard support ends soon, but extended support is available.${NC}"
		else
			echo -e "${YELLOW}Your system ($OS $OS_VERSION) will reach End of Life soon.${NC}"
			echo -e "${BLUE}Consider upgrading to a newer version.${NC}"
		fi
		echo ""
		draw_hr
		sleep 2
	fi

	log_success "Supported OS detected: $OS $OS_VERSION"

	# Update system and ensure essential packages (sudo, curl, etc.) are installed
	ensure_system_ready || {
		log_warn "System preparation had issues. Installation may fail. Continuing..."
	}

	# Check if Docker is already installed
	check_existing_docker() {
		local docker_installed=false
		local docker_daemon_running=false
		local docker_compose_works=false
		local docker_version=""
		local compose_version=""

		# Check if docker command exists
		if command -v docker >/dev/null 2>&1; then
			docker_installed=true
			docker_version=$(sudo docker --version 2>/dev/null || echo "unknown")

			# Check if Docker daemon is running
			if sudo docker info >/dev/null 2>&1; then
				docker_daemon_running=true
			fi
		fi

		# Check if docker compose works (requires daemon to be running)
		if [ "$docker_installed" = true ] && [ "$docker_daemon_running" = true ]; then
			# Try docker compose (v2 plugin)
			if sudo docker compose version >/dev/null 2>&1; then
				docker_compose_works=true
				compose_version=$(sudo docker compose version 2>/dev/null | head -n1 || echo "unknown")
			# Fallback: try docker-compose (v1 standalone)
			elif command -v docker-compose >/dev/null 2>&1 && sudo docker-compose version >/dev/null 2>&1; then
				docker_compose_works=true
				compose_version=$(sudo docker-compose version 2>/dev/null | head -n1 || echo "unknown")
			fi
		elif [ "$docker_installed" = true ] && [ "$docker_daemon_running" != true ]; then
			# Docker is installed but daemon is not running - we can't test compose
			docker_compose_works=false
		fi

		if [ "$docker_installed" = true ]; then
			if [ -t 1 ]; then clear; fi
			print_banner
			draw_hr
			echo -e "${YELLOW}${BOLD}⚠️  Existing Docker Installation Detected${NC}"
			draw_hr
			echo ""
			echo -e "${YELLOW}${BOLD}Looks like Docker was already installed on this system.${NC}"
			echo ""
			if [ -n "$docker_version" ] && [ "$docker_version" != "unknown" ]; then
				echo -e "${BLUE}Docker Version:${NC} ${CYAN}$docker_version${NC}"
			else
				echo -e "${BLUE}Docker:${NC} ${CYAN}Installed${NC}"
			fi

			if [ "$docker_daemon_running" = true ]; then
				echo -e "${BLUE}Docker Daemon:${NC} ${GREEN}Running${NC}"
			else
				echo -e "${BLUE}Docker Daemon:${NC} ${RED}Not running${NC}"
				echo -e "${YELLOW}  The Docker daemon must be running for MythicalDash to work.${NC}"
			fi

			if [ "$docker_daemon_running" = true ]; then
				if [ "$docker_compose_works" = true ]; then
					echo -e "${BLUE}Docker Compose:${NC} ${GREEN}Working${NC}"
					if [ -n "$compose_version" ] && [ "$compose_version" != "unknown" ]; then
						echo -e "${BLUE}  Version:${NC} ${CYAN}$compose_version${NC}"
					fi
				else
					echo -e "${BLUE}Docker Compose:${NC} ${RED}Not working or outdated${NC}"
					echo -e "${YELLOW}  Docker Compose may need to be updated or installed.${NC}"
					echo -e "${YELLOW}  This may cause installation or update failures.${NC}"
				fi
			else
				echo -e "${BLUE}Docker Compose:${NC} ${YELLOW}Cannot test (daemon not running)${NC}"
			fi

			echo ""
			draw_hr
			echo -e "${RED}${BOLD}Important Notice:${NC}"
			echo ""
			echo -e "${YELLOW}${BOLD}MythicalDash and MythicalSystems are NOT obligated to provide${NC}"
			echo -e "${YELLOW}${BOLD}support for installations on systems with existing Docker installations.${NC}"
			echo ""
			echo -e "${BLUE}${BOLD}We strongly recommend installing MythicalDash on a clean VM${NC}"
			echo -e "${BLUE}${BOLD}without any pre-existing Docker setup.${NC}"
			echo ""
			if [ "$docker_compose_works" != true ]; then
				echo -e "${YELLOW}Additionally, Docker Compose appears to be missing or not working properly,${NC}"
				echo -e "${YELLOW}which may cause installation or update issues.${NC}"
				echo ""
			fi
			echo -e "${CYAN}This check is enforced to prevent support ticket spam and ensure${NC}"
			echo -e "${CYAN}reliable installations on clean environments.${NC}"
			echo ""
			draw_hr
			echo ""
			continue_install=""
			prompt "${BOLD}${RED}Do you want to continue anyway?${NC} ${BLUE}(NOT RECOMMENDED - type 'yes' to continue)${NC}: " continue_install
			if [ "$continue_install" != "yes" ]; then
				echo -e "${GREEN}Installation cancelled.${NC}"
				echo -e "${BLUE}Please set up a clean VM and run the installer again.${NC}"
				exit 0
			fi
			log_warn "User chose to continue with existing Docker installation"
			if [ "$docker_compose_works" != true ]; then
				log_warn "Docker Compose is not working - installation may fail"
			fi
		fi
	}

	# Check virtualization compatibility for Docker
	check_virtualization_compatibility() {
		# Only check if virtualization check is not skipped
		if [ "$SKIP_VIRT_CHECK" = true ]; then
			log_warn "Virtualization check skipped via --skip-virt-check flag"
			return 0
		fi

		local virt_type=""
		local system_manufacturer=""
		local incompatible_virt=false
		local virt_warning=""

		# First, try systemd-detect-virt (most reliable method)
		if command -v systemd-detect-virt >/dev/null 2>&1; then
			virt_type=$(systemd-detect-virt 2>/dev/null || echo "none")

			# Check for incompatible virtualization types
			case "$virt_type" in
			openvz | ovz | openvz7)
				incompatible_virt=true
				virt_warning="OpenVZ/OVZ"
				;;
			lxc | lxc-libvirt)
				incompatible_virt=true
				virt_warning="LXC"
				;;
			vz)
				# Could be Virtuozzo or OpenVZ
				incompatible_virt=true
				virt_warning="Virtuozzo/OpenVZ"
				;;
			none)
				# No virtualization detected (bare metal or dedicated server)
				log_info "No virtualization detected - running on bare metal or dedicated hardware"
				;;
			*)
				# Other types like kvm, vmware, xen, etc. are compatible
				log_info "Virtualization type detected: $virt_type (compatible with Docker)"
				;;
			esac
		else
			# Fallback: Try dmidecode if systemd-detect-virt is not available
			log_warn "systemd-detect-virt not available, using fallback detection method..."

			# Try alternative detection methods
			# Check for OpenVZ indicators
			if [ -d /proc/vz ] && [ ! -d /proc/bc ]; then
				incompatible_virt=true
				virt_warning="OpenVZ"
				virt_type="openvz"
			# Check for LXC indicators
			elif [ -f /.dockerenv ] || [ -n "${container:-}" ]; then
				# This could be Docker itself or LXC, so we need to be careful
				# Only flag if we're in an LXC container (not Docker)
				if [ "${container:-}" = "lxc" ]; then
					incompatible_virt=true
					virt_warning="LXC"
					virt_type="lxc"
				fi
			fi

			# Try dmidecode for additional info (if available and running as root)
			if command -v dmidecode >/dev/null 2>&1; then
				system_manufacturer=$(dmidecode -s system-manufacturer 2>/dev/null | head -n1 || echo "")

				if [ -n "$system_manufacturer" ]; then
					log_info "System manufacturer: $system_manufacturer"
				fi
			fi

			# If we still couldn't detect anything reliably, log it
			if [ "$incompatible_virt" != true ]; then
				log_info "Could not reliably detect virtualization type - assuming compatible"
			fi
		fi

		# If incompatible virtualization detected, show warning
		if [ "$incompatible_virt" = true ]; then
			if [ -t 1 ]; then clear; fi
			print_banner
			draw_hr
			echo -e "${RED}${BOLD}⚠️  Incompatible Virtualization Detected${NC}"
			draw_hr
			echo ""
			echo -e "${YELLOW}${BOLD}Your system appears to be running on ${virt_warning} virtualization.${NC}"
			echo ""
			echo -e "${RED}${BOLD}WARNING:${NC} ${YELLOW}MythicalDash requires Docker, which typically does NOT work${NC}"
			echo -e "${YELLOW}on systems using ${virt_warning} virtualization.${NC}"
			echo ""
			echo -e "${BLUE}System Requirements:${NC}"
			echo -e "  ${CYAN}•${NC} Docker containers require proper containerization support"
			echo -e "  ${CYAN}•${NC} ${virt_warning} uses container-based virtualization that conflicts with Docker"
			echo -e "  ${CYAN}•${NC} Most providers using ${virt_warning} have not enabled nested virtualization"
			echo ""
			echo -e "${BLUE}Compatible Virtualization Types:${NC}"
			echo -e "  ${GREEN}✓${NC} KVM (guaranteed to work)"
			echo -e "  ${GREEN}✓${NC} VMware"
			echo -e "  ${GREEN}✓${NC} Xen"
			echo -e "  ${GREEN}✓${NC} Hyper-V"
			echo -e "  ${GREEN}✓${NC} Bare metal / Dedicated servers"
			echo ""
			echo -e "${BLUE}Incompatible Virtualization Types:${NC}"
			echo -e "  ${RED}✗${NC} OpenVZ / OVZ"
			echo -e "  ${RED}✗${NC} Virtuozzo"
			echo -e "  ${RED}✗${NC} LXC (unless specifically configured for nested virtualization)"
			echo ""
			echo -e "${YELLOW}Detection Details:${NC}"
			if [ -n "$virt_type" ]; then
				echo -e "  ${BLUE}Virtualization Type:${NC} ${CYAN}$virt_type${NC}"
			fi
			if [ -n "$system_manufacturer" ]; then
				echo -e "  ${BLUE}System Manufacturer:${NC} ${CYAN}$system_manufacturer${NC}"
			fi
			echo ""
			draw_hr
			echo -e "${CYAN}${BOLD}What to do:${NC}"
			echo ""
			echo -e "${BLUE}1.${NC} Contact your VPS provider's support team"
			echo -e "   ${YELLOW}Ask if they support Docker or nested virtualization${NC}"
			echo -e "   ${YELLOW}Some providers have made necessary changes for Docker support${NC}"
			echo ""
			echo -e "${BLUE}2.${NC} Consider migrating to a KVM-based VPS"
			echo -e "   ${YELLOW}KVM is guaranteed to work with Docker${NC}"
			echo ""
			echo -e "${BLUE}3.${NC} If you're certain your provider supports Docker on ${virt_warning},"
			echo -e "   ${YELLOW}you can bypass this check with: ${BOLD}--skip-virt-check${NC}"
			echo ""
			draw_hr
			echo ""
			echo -e "${RED}${BOLD}MythicalDash and MythicalSystems are NOT obligated to provide${NC}"
			echo -e "${RED}${BOLD}support for installations on incompatible virtualization platforms.${NC}"
			echo ""
			draw_hr
			echo ""
			continue_anyway=""
			prompt "${BOLD}${RED}Do you want to continue anyway?${NC} ${BLUE}(NOT RECOMMENDED - type 'yes' to continue)${NC}: " continue_anyway
			if [ "$continue_anyway" != "yes" ]; then
				echo -e "${GREEN}Installation cancelled.${NC}"
				echo -e "${BLUE}Please use a compatible virtualization platform or contact your provider.${NC}"
				exit 0
			fi
			log_warn "User chose to continue with incompatible virtualization: $virt_warning"
			log_warn "Installation may fail - Docker may not work on this platform"
		fi
	}

	# Environment overrides for non-interactive mode
	case "${FP_COMPONENT:-}" in
	panel) COMPONENT_TYPE="1" ;;
	ssl) COMPONENT_TYPE="2" ;;
	*) COMPONENT_TYPE="" ;;
	esac

	while [[ ! "$COMPONENT_TYPE" =~ ^[12]$ ]]; do
		show_main_menu
		prompt "${BOLD}Enter component${NC} ${BLUE}(1/2)${NC}: " COMPONENT_TYPE
		if [[ ! "$COMPONENT_TYPE" =~ ^[12]$ ]]; then
			echo -e "${RED}Invalid input.${NC} Please enter ${YELLOW}1${NC} or ${YELLOW}2${NC}."
			sleep 1
		fi
	done

	# Show appropriate menu based on component selection
	if [ "$COMPONENT_TYPE" = "1" ]; then
		# Panel operations
		while [[ ! "$INST_TYPE" =~ ^[1-4]$ ]]; do
			show_panel_menu
			echo ""
			prompt "${BOLD}${CYAN}Select operation${NC} ${BLUE}(1/2/3/4)${NC}: " INST_TYPE
			if [[ ! "$INST_TYPE" =~ ^[1-4]$ ]]; then
				echo ""
				echo -e "${RED}${BOLD}✗ Invalid input!${NC}"
				echo -e "${YELLOW}Please enter ${BOLD}1${NC} (Install), ${BOLD}2${NC} (Uninstall), ${BOLD}3${NC} (Update), or ${BOLD}4${NC} (Backup)${NC}"
				echo ""
				sleep 2
			fi
		done

		# Add confirmation for destructive operations
		if [ "$INST_TYPE" = "2" ]; then
			echo ""
			draw_hr
			echo -e "${RED}${BOLD}⚠️  WARNING: Uninstall Operation${NC}"
			draw_hr
			echo -e "${YELLOW}This will permanently delete:${NC}"
			echo -e "  ${RED}•${NC} All MythicalDash Docker containers"
			echo -e "  ${RED}•${NC} All Panel data and configuration"
			echo -e "  ${RED}•${NC} Installation files"
			echo ""
			draw_hr
			confirm_uninstall=""
			prompt "${BOLD}${RED}Are you absolutely sure you want to uninstall?${NC} ${BLUE}(type 'yes' to confirm)${NC}: " confirm_uninstall
			if [ "$confirm_uninstall" != "yes" ]; then
				echo -e "${GREEN}Uninstallation cancelled.${NC}"
				exit 0
			fi
		fi
	elif [ "$COMPONENT_TYPE" = "2" ]; then
		# SSL operations
		while [[ ! "$INST_TYPE" =~ ^[1-5]$ ]]; do
			show_ssl_menu
			echo ""
			prompt "${BOLD}${CYAN}Select operation${NC} ${BLUE}(1/2/3/4/5)${NC}: " INST_TYPE
			if [[ ! "$INST_TYPE" =~ ^[1-5]$ ]]; then
				echo ""
				echo -e "${RED}${BOLD}✗ Invalid input!${NC}"
				echo -e "${YELLOW}Please enter ${BOLD}1${NC} (Install Certbot), ${BOLD}2${NC} (HTTP Cert), ${BOLD}3${NC} (DNS Cert), ${BOLD}4${NC} (Auto-Renewal), or ${BOLD}5${NC} (acme.sh)${NC}"
				echo ""
				sleep 2
			fi
		done
	else
		log_error "Invalid component selected."
		exit 1
	fi

	# Environment overrides for non-interactive mode
	case "${FP_ACTION:-}" in
	install) INST_TYPE="1" ;;
	uninstall) INST_TYPE="2" ;;
	update) INST_TYPE="3" ;;
	*) ;;
	esac

	# Environment overrides for dev mode
	if [ -n "${FP_DEV:-}" ] && [ "${FP_DEV}" = "true" ]; then
		USE_DEV=true
	fi
	if [ -n "${FP_DEV_BRANCH:-}" ]; then
		USE_DEV=true
		DEV_BRANCH="${FP_DEV_BRANCH}"
	fi
	if [ -n "${FP_DEV_SHA:-}" ]; then
		USE_DEV=true
		DEV_SHA="${FP_DEV_SHA}"
	fi

	reinstall="n"
	CF_TUNNEL_SETUP=""
	CF_TUNNEL_TOKEN=""
	CF_TUNNEL_MODE=""
	CF_API_KEY=""
	CF_EMAIL=""
	CF_HOSTNAME=""
	confirm="n"
	has_ssl="false"
	panel_domain=""

	# Handle operations based on component and action
	if [ "$COMPONENT_TYPE" = "1" ] && [ "$INST_TYPE" = "1" ]; then
		# Panel Install
		# Check if MythicalDash is already installed (unless skip flag is set)
		if [ "$SKIP_INSTALL_CHECK" = false ]; then
			INSTALLED=false

			# Check for .installed file
			if [ -f /var/www/mythicaldash/.installed ]; then
				INSTALLED=true
			fi

			# Check for docker-compose.yml
			if [ -f /var/www/mythicaldash/docker-compose.yml ]; then
				INSTALLED=true
			fi

			# Check if containers are running
			if command -v docker >/dev/null 2>&1; then
				if sudo docker ps --format '{{.Names}}' 2>/dev/null | grep -q "mythicaldash_v3_backend\|mythicaldash_v3_frontend\|mythicaldash_v3_mysql\|mythicaldash_v3_redis"; then
					INSTALLED=true
				fi
			fi

			if [ "$INSTALLED" = true ]; then
				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				echo -e "${YELLOW}${BOLD}⚠️  MythicalDash Already Installed${NC}"
				draw_hr
				echo ""
				echo -e "${BLUE}MythicalDash appears to be already installed on this system.${NC}"
				echo ""
				echo -e "${BLUE}Detected installation indicators:${NC}"
				[ -f /var/www/mythicaldash/.installed ] && echo -e "  ${GREEN}✓${NC} Installation marker file exists"
				[ -f /var/www/mythicaldash/docker-compose.yml ] && echo -e "  ${GREEN}✓${NC} docker-compose.yml found"
				if command -v docker >/dev/null 2>&1 && sudo docker ps --format '{{.Names}}' 2>/dev/null | grep -q "mythicaldash"; then
					echo -e "  ${GREEN}✓${NC} MythicalDash containers are running"
				fi
				echo ""
				echo -e "${YELLOW}What would you like to do?${NC}"
				echo -e "  ${GREEN}[1]${NC} Update existing installation (recommended)"
				echo -e "  ${YELLOW}[2]${NC} Reinstall (will stop and remove existing containers)"
				echo -e "  ${RED}[3]${NC} Exit and keep current installation"
				echo ""
				draw_hr
				reinstall_choice=""
				while [[ ! "$reinstall_choice" =~ ^[123]$ ]]; do
					prompt "${BOLD}Enter choice${NC} ${BLUE}(1/2/3)${NC}: " reinstall_choice
					if [[ ! "$reinstall_choice" =~ ^[123]$ ]]; then
						echo -e "${RED}Invalid input.${NC} Please enter ${YELLOW}1${NC}, ${YELLOW}2${NC}, or ${YELLOW}3${NC}."
						sleep 1
					fi
				done

				case $reinstall_choice in
				1)
					echo ""
					echo -e "${GREEN}To update MythicalDash, please run the installer again and select:${NC}"
					echo -e "  ${CYAN}•${NC} Component: ${BOLD}Panel${NC} (option 1)"
					echo -e "  ${CYAN}•${NC} Operation: ${BOLD}Update Panel${NC} (option 2)"
					echo ""
					echo -e "${BLUE}Or use: ${BOLD}FP_COMPONENT=panel FP_ACTION=update $0${NC}"
					echo ""
					exit 0
					;;
				2)
					echo ""
					log_warn "Reinstalling will stop and remove existing containers."
					confirm_reinstall=""
					prompt "${BOLD}${RED}Are you sure you want to reinstall?${NC} ${BLUE}(type 'yes' to confirm)${NC}: " confirm_reinstall
					if [ "$confirm_reinstall" != "yes" ]; then
						echo -e "${GREEN}Reinstallation cancelled.${NC}"
						exit 0
					fi
					log_info "Proceeding with reinstallation..."
					# Stop and remove existing containers before reinstalling
					if [ -f /var/www/mythicaldash/docker-compose.yml ] && command -v docker >/dev/null 2>&1; then
						log_info "Stopping existing MythicalDash containers..."
						cd /var/www/mythicaldash && sudo docker compose down -v >/dev/null 2>&1 || true
					fi
					# Remove .installed marker to allow fresh installation
					sudo rm -f /var/www/mythicaldash/.installed
					# Continue with installation (will overwrite)
					;;
				3)
					echo -e "${GREEN}Exiting. Current installation will remain unchanged.${NC}"
					exit 0
					;;
				esac
			fi
		else
			log_warn "Installation check skipped via --skip-install-check flag"
		fi

		# Release type selection (only if not already set via CLI/env)
		if [ "$USE_DEV" != "true" ] && [ -z "${FP_DEV:-}" ]; then
			RELEASE_TYPE=""
			while [[ ! "$RELEASE_TYPE" =~ ^[1-3]$ ]]; do
				show_release_type_menu
				prompt "${BOLD}Enter release type${NC} ${BLUE}(1/2/3)${NC}: " RELEASE_TYPE
				if [[ ! "$RELEASE_TYPE" =~ ^[1-3]$ ]]; then
					echo -e "${RED}Invalid input.${NC} Please enter ${YELLOW}1${NC}, ${YELLOW}2${NC}, or ${YELLOW}3${NC}."
					sleep 1
				fi
			done

			case $RELEASE_TYPE in
			1)
				USE_DEV=false
				log_info "Stable release selected"
				;;
			2)
				USE_DEV=true
				DEV_BRANCH="v3-remastered"
				log_info "Development build from v3-remastered branch selected"
				;;
			3)
				USE_DEV=true
				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				echo -e "${BOLD}${CYAN}Custom Development Build${NC}"
				draw_hr
				echo ""
				echo -e "${BLUE}Enter the branch name (default: v3-remastered)${NC}"
				prompt "${BOLD}Branch name${NC} ${BLUE}(e.g., v3-remastered, develop)${NC}: " DEV_BRANCH
				if [ -z "$DEV_BRANCH" ]; then
					DEV_BRANCH="v3-remastered"
				fi

				echo ""
				echo -e "${BLUE}Enter a specific commit SHA (optional)${NC}"
				echo -e "${BLUE}Leave empty to use the latest commit from the branch${NC}"
				prompt "${BOLD}Commit SHA${NC} ${BLUE}(7+ characters, optional)${NC}: " DEV_SHA
				if [ -n "$DEV_SHA" ] && [ ${#DEV_SHA} -lt 7 ]; then
					log_warn "Commit SHA should be at least 7 characters. Using latest from branch instead."
					DEV_SHA=""
				fi
				log_info "Custom dev build selected: branch=$DEV_BRANCH, sha=${DEV_SHA:-latest}"
				;;
			esac
		fi

		# Unified access method selection
		ACCESS_METHOD=""
		# Env override for access method
		if [ -n "${FP_ACCESS_METHOD:-}" ]; then
			ACCESS_METHOD="$FP_ACCESS_METHOD"
		fi

		while [[ ! "$ACCESS_METHOD" =~ ^[1-4]$ ]]; do
			show_access_method_menu
			prompt "${BOLD}Enter access method${NC} ${BLUE}(1/2/3/4)${NC}: " ACCESS_METHOD
			if [[ ! "$ACCESS_METHOD" =~ ^[1-4]$ ]]; then
				echo -e "${RED}Invalid input.${NC} Please enter ${YELLOW}1${NC}, ${YELLOW}2${NC}, ${YELLOW}3${NC} or ${YELLOW}4${NC}."
				sleep 1
			fi
		done

		case $ACCESS_METHOD in
		1)
			# Cloudflare Tunnel
			CF_TUNNEL_SETUP="y"
			REVERSE_PROXY_TYPE="none"

			# Env override
			if [ -n "${FP_CF_MODE:-}" ]; then CF_TUNNEL_MODE="$FP_CF_MODE"; fi
			while [[ ! "$CF_TUNNEL_MODE" =~ ^[12]$ ]]; do
				show_cf_mode_menu
				prompt "${BOLD}Enter mode${NC} ${BLUE}(1/2)${NC}: " CF_TUNNEL_MODE
				if [[ ! "$CF_TUNNEL_MODE" =~ ^[12]$ ]]; then
					echo -e "${RED}Invalid input.${NC} Enter ${YELLOW}1${NC} or ${YELLOW}2${NC}."
					sleep 1
				fi
			done

			if [ "$CF_TUNNEL_MODE" == "1" ]; then
				# Show Cloudflare Zero Trust requirements
				print_info_box "Cloudflare Zero Trust Requirements" \
					"⚠️  IMPORTANT: Before proceeding, ensure you have:" \
					"" \
					"  ${GREEN}✓${NC} Set up Cloudflare Zero Trust in your Cloudflare dashboard" \
					"  ${GREEN}✓${NC} Added a valid billing address to your Cloudflare account" \
					"  ${GREEN}✓${NC} Verified your Cloudflare account email" \
					"" \
					"${YELLOW}Note:${NC} Cloudflare Tunnels require Zero Trust to be enabled" \
					"${YELLOW}Note:${NC} A valid billing address is required for tunnel creation"

				cf_ready=""
				prompt "${BOLD}${CYAN}Have you set up Cloudflare Zero Trust and added a billing address?${NC} ${BLUE}(y/n)${NC}: " cf_ready
				if [[ ! "$cf_ready" =~ ^[yY]$ ]]; then
					echo -e "${YELLOW}Please set up Cloudflare Zero Trust and add a billing address first.${NC}"
					echo -e "${BLUE}Visit: https://one.dash.cloudflare.com/${NC}"
					echo -e "${BLUE}Then run this installer again.${NC}"
					exit 0
				fi

				echo ""
				log_info "Entering Full Automatic setup for Cloudflare Tunnel."
				[ -n "${FP_EMAIL:-}" ] && CF_EMAIL="$FP_EMAIL"
				[ -n "${FP_API_KEY:-}" ] && CF_API_KEY="$FP_API_KEY"
				[ -n "${FP_HOSTNAME:-}" ] && CF_HOSTNAME="$FP_HOSTNAME"
				while [ -z "$CF_EMAIL" ]; do
					prompt "${BOLD}Cloudflare Email${NC}: " CF_EMAIL
				done
				while [ -z "$CF_API_KEY" ]; do
					prompt_secret "${BOLD}Cloudflare Global API Key${NC}: " CF_API_KEY
				done
				while [ -z "$CF_HOSTNAME" ]; do
					prompt "${BOLD}Hostname${NC} ${BLUE}(e.g., panel.example.com)${NC}: " CF_HOSTNAME
				done
			else
				echo -e "${YELLOW}Semi-Automatic mode selected.${NC}"
				[ -n "${FP_TUNNEL_TOKEN:-}" ] && CF_TUNNEL_TOKEN="$FP_TUNNEL_TOKEN"
				while [ -z "$CF_TUNNEL_TOKEN" ]; do
					prompt_secret "${BOLD}Cloudflare Tunnel Token${NC}: " CF_TUNNEL_TOKEN
				done
			fi
			;;
		2)
			# Nginx Reverse Proxy
			CF_TUNNEL_SETUP="n"
			REVERSE_PROXY_TYPE="nginx"
			log_info "Nginx reverse proxy selected."

			# Get domain immediately
			if [ -t 1 ]; then clear; fi
			print_banner
			draw_hr
			echo -e "${BOLD}${CYAN}Domain Configuration${NC}"
			draw_hr
			while [ -z "$panel_domain" ]; do
				prompt "${BOLD}Enter Panel domain name${NC} ${BLUE}(e.g., panel.example.com or subdomain.example.com)${NC}: " panel_domain
				if [ -z "$panel_domain" ]; then
					echo -e "${RED}Domain name cannot be empty.${NC}"
				fi
			done
			log_info "Domain set to: $panel_domain"
			;;
		3)
			# Apache2 Reverse Proxy
			CF_TUNNEL_SETUP="n"
			REVERSE_PROXY_TYPE="apache"
			log_info "Apache2 reverse proxy selected."

			# Get domain immediately
			if [ -t 1 ]; then clear; fi
			print_banner
			draw_hr
			echo -e "${BOLD}${CYAN}Domain Configuration${NC}"
			draw_hr
			while [ -z "$panel_domain" ]; do
				prompt "${BOLD}Enter Panel domain name${NC} ${BLUE}(e.g., panel.example.com or subdomain.example.com)${NC}: " panel_domain
				if [ -z "$panel_domain" ]; then
					echo -e "${RED}Domain name cannot be empty.${NC}"
				fi
			done
			log_info "Domain set to: $panel_domain"
			;;
		4)
			# Direct Access (home hosting / no domain)
			CF_TUNNEL_SETUP="n"
			REVERSE_PROXY_TYPE="none"
			log_info "Direct access selected – no domain or SSL needed."
			log_info "Access the Panel at http://YOUR_IP:4832 (open port 4832 in your firewall if needed)."
			;;
		esac

		# Check virtualization compatibility before installing Docker
		check_virtualization_compatibility

		install_packages curl unzip jq
		if command -v docker &>/dev/null; then
			log_info "Docker is already installed."
		else
			log_step "Installing Docker engine (this may take a minute)..."
			curl -sSL https://get.docker.com/ | CHANNEL=stable bash >>"$LOG_FILE" 2>&1
			sudo systemctl enable --now docker 2>&1 | tee -a "$LOG_FILE" >/dev/null
			sudo usermod -aG docker "$USER" 2>&1 | tee -a "$LOG_FILE" >/dev/null || true
			log_success "Docker installed. You may need to re-login for group changes to take effect."
		fi

		# Check ARM architecture and handle accordingly
		ARCH=$(uname -m)
		if [[ "$ARCH" == "aarch64" ]] || [[ "$ARCH" == "arm64" ]]; then
			log_info "ARM64 architecture detected: $ARCH"
			log_success "Native ARM64 Docker images are available - no emulation needed."
		elif [[ "$ARCH" == "armv7l" ]] || [[ "$ARCH" == "armv6l" ]]; then
			if [ "$FORCE_ARM" = true ]; then
				log_warn "Unsupported ARM architecture detected: $ARCH (--force-arm flag set)"
			else
				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				echo -e "${YELLOW}${BOLD}⚠️  Unsupported ARM Architecture Detected${NC}"
				draw_hr
				echo ""
				echo -e "${YELLOW}${BOLD}IMPORTANT NOTICE:${NC}"
				echo -e "${BLUE}MythicalDash provides native images for ${BOLD}amd64${NC} and ${BOLD}arm64 (aarch64)${NC} only.${NC}"
				echo -e "${BLUE}Your system architecture (${BOLD}$ARCH${NC}) is not natively supported.${NC}"
				echo ""
				echo -e "${BLUE}To allow MythicalDash to run on your ARM system, the installer will:${NC}"
				echo -e "  ${CYAN}•${NC} Install QEMU virtualization and emulation packages"
				echo -e "  ${CYAN}•${NC} Configure Docker to use emulation for amd64 containers"
				echo -e "  ${CYAN}•${NC} Run MythicalDash containers through emulation"
				echo ""
				echo -e "${YELLOW}${BOLD}Performance Notice:${NC}"
				echo -e "${YELLOW}Running MythicalDash through emulation will result in:${NC}"
				echo -e "  ${YELLOW}•${NC} Slower container startup times"
				echo -e "  ${YELLOW}•${NC} Higher CPU and memory usage"
				echo -e "  ${YELLOW}•${NC} Reduced overall performance compared to native systems"
				echo ""
				echo -e "${GREEN}${BOLD}Recommendation:${NC}"
				echo -e "${GREEN}For better performance, please use an AMD64/x86_64 or ARM64 (aarch64) CPU.${NC}"
				echo ""
				draw_hr
				echo ""
				log_info "Unsupported ARM architecture detected: $ARCH"
				log_info "Proceeding with QEMU emulation setup..."
			fi
			setup_qemu_emulation
		fi

		sudo mkdir -p /var/www/mythicaldash
		sudo mkdir -p "$BACKUP_DIR"
		cd /var/www/mythicaldash || exit 1

		# Only create Cloudflare .env if Cloudflare Tunnel is selected
		if [[ "$CF_TUNNEL_SETUP" =~ ^[yY]$ ]]; then
			ensure_env_cloudflare
		fi

		if [ ! -f /var/www/mythicaldash/docker-compose.yml ]; then
			COMPOSE_URL=$(get_compose_file_url)
			if ! run_with_spinner "Downloading docker-compose.yml for MythicalDash" "docker-compose.yml downloaded." \
				curl -fsSL -o /var/www/mythicaldash/docker-compose.yml "$COMPOSE_URL"; then
				exit 1
			fi
		fi

		# Modify docker-compose.yml for dev images if dev mode is enabled
		# (Only show confirmation if not already confirmed via GUI)
		if [ "$USE_DEV" = true ]; then
			DEV_TAG=$(get_dev_image_tag)
			log_info "Using dev release mode with tag: $DEV_TAG"

			# Only show confirmation if this wasn't selected via GUI menu
			# (We can detect this by checking if RELEASE_TYPE was set)
			if [ -z "${RELEASE_TYPE:-}" ]; then
				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				echo -e "${YELLOW}${BOLD}⚠️  Development Release Mode${NC}"
				draw_hr
				echo ""
				echo -e "${YELLOW}You are installing a ${BOLD}development release${NC} of MythicalDash.${NC}"
				echo ""
				echo -e "${BLUE}Dev Release Information:${NC}"
				if [ -n "$DEV_BRANCH" ]; then
					echo -e "  ${CYAN}•${NC} Branch: ${BOLD}$DEV_BRANCH${NC}"
				else
					echo -e "  ${CYAN}•${NC} Branch: ${BOLD}v3-remastered${NC} (default)"
				fi
				if [ -n "$DEV_SHA" ]; then
					echo -e "  ${CYAN}•${NC} Commit: ${BOLD}$DEV_SHA${NC}"
				fi
				echo -e "  ${CYAN}•${NC} Image Tag: ${BOLD}$DEV_TAG${NC}"
				echo ""
				echo -e "${YELLOW}${BOLD}Warning:${NC} Development releases may be unstable and are not recommended for production use.${NC}"
				echo ""
				draw_hr
				dev_confirm=""
				prompt "${BOLD}Continue with dev release installation?${NC} ${BLUE}(y/n)${NC}: " dev_confirm
				if [[ ! "$dev_confirm" =~ ^[yY]$ ]]; then
					echo -e "${GREEN}Installation cancelled.${NC}"
					exit 0
				fi
			fi

			modify_compose_for_dev "/var/www/mythicaldash/docker-compose.yml" "$DEV_TAG" "$DEV_TAG"
		fi

		print_banner

		# Check ARM architecture (native ARM64 is supported, no emulation needed)
		ARCH=$(uname -m)
		if [[ "$ARCH" == "aarch64" ]] || [[ "$ARCH" == "arm64" ]]; then
			log_info "ARM64 architecture detected: $ARCH - native images available"
		elif [[ "$ARCH" == "armv7l" ]] || [[ "$ARCH" == "armv6l" ]]; then
			if [ "$FORCE_ARM" = true ]; then
				log_warn "Unsupported ARM architecture detected: $ARCH (--force-arm flag set)"
			else
				log_info "Unsupported ARM architecture detected: $ARCH - QEMU emulation will be used"
			fi
		fi

		# Stop all existing MythicalDash containers (including old v1 containers) before starting
		stop_all_mythicaldash_containers

		if ! run_with_spinner "Starting MythicalDash stack" "MythicalDash stack started." sudo docker compose up -d; then
			log_error "Failed to start MythicalDash stack"
			echo ""
			draw_hr
			echo -e "${RED}${BOLD}Container Start Failure${NC}"
			draw_hr

			# Check Docker logs for common errors
			log_info "Checking Docker container logs..."
			if command -v docker >/dev/null 2>&1; then
				cd /var/www/mythicaldash || true
				CONTAINER_LOGS=$(sudo docker compose logs --tail=50 2>&1 || sudo docker-compose logs --tail=50 2>&1 || echo "")

				if echo "$CONTAINER_LOGS" | grep -qi "exec format error"; then
					echo -e "${RED}${BOLD}Detected: Exec Format Error${NC}"
					echo -e "${YELLOW}This typically means QEMU emulation is not properly configured.${NC}"
					ARCH=$(uname -m)
					echo -e "${YELLOW}Your system architecture: ${BOLD}$ARCH${NC}"
					echo ""
					echo -e "${BLUE}Solution:${NC} Ensure QEMU and binfmt-support are installed and properly configured."
					echo -e "${BLUE}Try:${NC} sudo apt-get install -y qemu qemu-user-static binfmt-support"
				elif echo "$CONTAINER_LOGS" | grep -qi "no space left"; then
					echo -e "${RED}${BOLD}Detected: No Space Left on Device${NC}"
					echo -e "${YELLOW}Your system is out of disk space.${NC}"
					echo -e "${BLUE}Solution:${NC} Free up disk space and try again."
				elif echo "$CONTAINER_LOGS" | grep -qi "permission denied"; then
					echo -e "${RED}${BOLD}Detected: Permission Denied${NC}"
					echo -e "${YELLOW}Docker permission issue detected.${NC}"
					echo -e "${BLUE}Solution:${NC} Ensure Docker is properly configured and you have permissions."
				else
					echo -e "${YELLOW}Container logs (last 20 lines):${NC}"
					echo "$CONTAINER_LOGS" | tail -20
				fi
			fi

			echo ""
			draw_hr
			echo -e "${BLUE}For more details, check:${NC}"
			echo -e "  ${CYAN}•${NC} Docker logs: ${BOLD}sudo docker compose -f /var/www/mythicaldash/docker-compose.yml logs${NC}"
			echo -e "  ${CYAN}•${NC} Container status: ${BOLD}sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps${NC}"
			echo -e "  ${CYAN}•${NC} Installation log: ${BOLD}$LOG_FILE${NC}"
			draw_hr
			upload_logs_on_fail
			exit 1
		fi

		# Verify containers are actually running
		sleep 2
		if ! sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps | grep -q "Up"; then
			log_error "Containers started but are not running"
			echo ""
			draw_hr
			echo -e "${RED}${BOLD}Container Status Check Failed${NC}"
			draw_hr
			log_info "Container status:"
			sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps
			echo ""
			log_info "Recent container logs:"
			sudo docker compose -f /var/www/mythicaldash/docker-compose.yml logs --tail=30
			echo ""
			draw_hr
			upload_logs_on_fail
			exit 1
		fi

		if [[ "$CF_TUNNEL_SETUP" =~ ^[yY]$ ]]; then
			if [ "$CF_TUNNEL_MODE" == "1" ]; then
				if ! setup_cloudflare_tunnel_full_auto; then
					CF_TUNNEL_TOKEN=""
				fi
			fi
			setup_cloudflare_tunnel_client
		fi

		# Setup reverse proxy if selected and not using Cloudflare Tunnel
		if [ -n "$REVERSE_PROXY_TYPE" ] && [ "$REVERSE_PROXY_TYPE" != "none" ]; then
			log_step "Setting up reverse proxy..."

			# Domain should already be set from earlier prompt, but check just in case
			if [ -z "$panel_domain" ]; then
				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				echo -e "${BOLD}${CYAN}Domain Configuration${NC}"
				draw_hr
				while [ -z "$panel_domain" ]; do
					prompt "${BOLD}Enter Panel domain name${NC} ${BLUE}(e.g., panel.example.com or subdomain.example.com)${NC}: " panel_domain
					if [ -z "$panel_domain" ]; then
						echo -e "${RED}Domain name cannot be empty.${NC}"
					fi
				done
			fi

			log_info "Using domain: $panel_domain"
			log_info "This will be the main domain for your MythicalDash (not a subdirectory like /panel)."

			# Ask if user wants to set up SSL certificate
			setup_ssl_during_install=""
			if [ -t 1 ]; then clear; fi
			print_banner
			draw_hr
			echo -e "${BOLD}${YELLOW}SSL Certificate Setup${NC}"
			draw_hr
			echo -e "${BLUE}Would you like to create an SSL certificate for $panel_domain now?${NC}"
			echo -e "${BLUE}This will set up HTTPS access automatically.${NC}"
			prompt "${BOLD}Create SSL certificate?${NC} ${BLUE}(y/n)${NC}: " setup_ssl_during_install

			ssl_created=false
			has_ssl="false"

			if [[ "$setup_ssl_during_install" =~ ^[yY]$ ]]; then
				# Check if certbot is installed
				if ! command -v certbot >/dev/null 2>&1; then
					log_info "Certbot is not installed. Installing Certbot..."
					# Pass the reverse proxy type to auto-install the correct plugin
					install_certbot "$REVERSE_PROXY_TYPE"
				fi

				# Get public IP addresses for DNS guidance
				log_info "Detecting your server's public IP addresses..."
				detect_public_ips

				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				show_dns_setup_instructions "$panel_domain"
				prompt "${BOLD}Press Enter when you have created the DNS records${NC} ${BLUE}(and waited for propagation)${NC}: " ready_to_continue

				# Try to create SSL certificate using HTTP/Standalone method
				log_step "Creating SSL certificate for $panel_domain..."

				# Check if web server is running (we'll stop it temporarily if needed)
				webserver=""
				if systemctl is-active --quiet nginx; then
					webserver="nginx"
				elif systemctl is-active --quiet apache2; then
					webserver="apache"
				else
					webserver="standalone"
				fi

				# Create certificate
				case $webserver in
				nginx)
					if dpkg -l | grep -q "^ii.*python3-certbot-nginx"; then
						log_info "Using Nginx plugin for certificate creation..."
						if certbot certonly --nginx -d "$panel_domain" --non-interactive --agree-tos --email admin@"$panel_domain" >>"$LOG_FILE" 2>&1; then
							ssl_created=true
							has_ssl="true"
						fi
					else
						log_warn "Nginx plugin not installed. Using standalone method..."
						log_info "Stopping Nginx temporarily to free port 80..."
						sudo systemctl stop nginx
						if certbot certonly --standalone -d "$panel_domain" --non-interactive --agree-tos --email admin@"$panel_domain" >>"$LOG_FILE" 2>&1; then
							ssl_created=true
							has_ssl="true"
						fi
						log_info "Restarting Nginx..."
						sudo systemctl start nginx
					fi
					;;
				apache)
					if dpkg -l | grep -q "^ii.*python3-certbot-apache"; then
						log_info "Using Apache plugin for certificate creation..."
						if certbot certonly --apache -d "$panel_domain" --non-interactive --agree-tos --email admin@"$panel_domain" >>"$LOG_FILE" 2>&1; then
							ssl_created=true
							has_ssl="true"
						fi
					else
						log_warn "Apache plugin not installed. Using standalone method..."
						log_info "Stopping Apache temporarily to free port 80..."
						sudo systemctl stop apache2
						if certbot certonly --standalone -d "$panel_domain" --non-interactive --agree-tos --email admin@"$panel_domain" >>"$LOG_FILE" 2>&1; then
							ssl_created=true
							has_ssl="true"
						fi
						log_info "Restarting Apache..."
						sudo systemctl start apache2
					fi
					;;
				standalone)
					log_info "Using standalone method for certificate creation..."
					if certbot certonly --standalone -d "$panel_domain" --non-interactive --agree-tos --email admin@"$panel_domain" >>"$LOG_FILE" 2>&1; then
						ssl_created=true
						has_ssl="true"
					fi
					;;
				esac

				if [ "$ssl_created" = true ]; then
					log_success "SSL certificate created successfully for $panel_domain"
				else
					log_warn "SSL certificate creation failed. Continuing with HTTP-only setup."
					log_info "You can create an SSL certificate later using the SSL Certificate menu."
					has_ssl="false"
				fi
			fi

			# Set up reverse proxy with or without SSL
			if [ "$REVERSE_PROXY_TYPE" = "nginx" ]; then
				if setup_nginx_reverse_proxy "$panel_domain" "$has_ssl"; then
					if [ "$has_ssl" = "true" ]; then
						log_success "Nginx reverse proxy configured with SSL for $panel_domain"
					else
						log_success "Nginx reverse proxy configured for $panel_domain"
					fi
				fi
			elif [ "$REVERSE_PROXY_TYPE" = "apache" ]; then
				if setup_apache_reverse_proxy "$panel_domain" "$has_ssl"; then
					if [ "$has_ssl" = "true" ]; then
						log_success "Apache reverse proxy configured with SSL for $panel_domain"
					else
						log_success "Apache reverse proxy configured for $panel_domain"
					fi
				fi
			fi

			# Ensure Panel is running
			if ! sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps | grep -q "Up"; then
				log_info "Ensuring MythicalDash containers are running..."

				# Check architecture - ARM64 is natively supported
				ARCH=$(uname -m)
				if [[ "$ARCH" == "aarch64" ]] || [[ "$ARCH" == "arm64" ]]; then
					log_info "ARM64 architecture detected: $ARCH - native images available"
					# Try to start with native images
					if ! run_with_spinner "Starting MythicalDash stack" "MythicalDash stack started." \
						bash -c "cd /var/www/mythicaldash && sudo docker compose up -d"; then
						log_warn "Failed to start MythicalDash. Reverse proxy configured but Panel is not running."
					fi
				elif [[ "$ARCH" == "armv7l" ]] || [[ "$ARCH" == "armv6l" ]]; then
					if [ "$FORCE_ARM" = true ]; then
						log_warn "Unsupported ARM architecture detected: $ARCH (--force-arm flag set)"
					else
						log_info "Unsupported ARM architecture detected: $ARCH - QEMU emulation will be used"
					fi
					# Try to start anyway (QEMU should be configured)
					if ! run_with_spinner "Starting MythicalDash stack" "MythicalDash stack started." \
						bash -c "cd /var/www/mythicaldash && sudo docker compose up -d"; then
						log_warn "Failed to start MythicalDash. Reverse proxy configured but Panel is not running."
						log_info "Ensure QEMU emulation is properly configured."
					fi
				else
					if ! run_with_spinner "Starting MythicalDash stack" "MythicalDash stack started." \
						bash -c "cd /var/www/mythicaldash && sudo docker compose up -d"; then
						log_error "Failed to start MythicalDash stack"
						echo ""
						draw_hr
						echo -e "${RED}${BOLD}Container Start Failure${NC}"
						draw_hr

						# Check Docker logs for common errors
						log_info "Checking Docker container logs..."
						cd /var/www/mythicaldash || true
						CONTAINER_LOGS=$(sudo docker compose logs --tail=50 2>&1 || sudo docker-compose logs --tail=50 2>&1 || echo "")

						if echo "$CONTAINER_LOGS" | grep -qi "exec format error"; then
							echo -e "${RED}${BOLD}Detected: Exec Format Error${NC}"
							echo -e "${YELLOW}This typically means QEMU emulation is not properly configured.${NC}"
							echo -e "${YELLOW}Your system architecture: ${BOLD}$ARCH${NC}"
							echo -e "${BLUE}Solution:${NC} Ensure QEMU and binfmt-support are installed."
						elif echo "$CONTAINER_LOGS" | grep -qi "no space left"; then
							echo -e "${RED}${BOLD}Detected: No Space Left on Device${NC}"
							echo -e "${YELLOW}Your system is out of disk space.${NC}"
						else
							echo -e "${YELLOW}Container logs (last 20 lines):${NC}"
							echo "$CONTAINER_LOGS" | tail -20
						fi

						echo ""
						draw_hr
						log_warn "Failed to start MythicalDash. Reverse proxy is configured but Panel is not running."
						log_info "Check logs: sudo docker compose -f /var/www/mythicaldash/docker-compose.yml logs"
					else
						# Verify containers are actually running
						sleep 2
						if ! sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps | grep -q "Up"; then
							log_error "Containers started but are not running"
							log_info "Container status:"
							sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps
							log_warn "Panel containers failed to start. Check Docker logs for details."
						fi
					fi
				fi
			else
				log_info "MythicalDash containers are already running."
			fi

			draw_hr
			if [ "$has_ssl" = "true" ]; then
				log_info "Reverse proxy configured with SSL. You can access MythicalDash at https://$panel_domain"
			else
				log_info "Reverse proxy configured. You can access MythicalDash at http://$panel_domain"
				log_info "To add SSL later, use the SSL Certificate options in the main menu."
			fi
			draw_hr
		fi

		sudo touch /var/www/mythicaldash/.installed

		# Install global mythicaldash command
		install_mythicaldash_command

		# Get public IP for access information
		PUBLIC_IP=$(curl -s ifconfig.me 2>/dev/null || curl -s ipinfo.io/ip 2>/dev/null || echo "Unable to detect")

		if [ -t 1 ]; then clear; fi
		print_banner
		draw_hr
		print_centered "🎉 Installation Complete!" "$GREEN"
		draw_hr
		echo ""

		log_success "Panel installation completed successfully!"
		if [ "$USE_DEV" = true ]; then
			log_warn "DEVELOPMENT RELEASE: This is a dev build and may be unstable."
		fi
		log_warn "IMPORTANT: The Panel may take up to 5 minutes to fully initialize."
		log_info "Please wait at least 5 minutes before trying to access the Panel."

		echo ""
		draw_hr
		print_centered "Panel Access Information" "$CYAN"
		draw_hr
		echo ""

		if [ "$USE_DEV" = true ]; then
			DEV_TAG=$(get_dev_image_tag)
			echo -e "  ${YELLOW}${BOLD}⚠️  Development Release${NC}"
			echo -e "     ${BLUE}• Using dev images with tag: ${CYAN}$DEV_TAG${NC}"
			if [ -n "$DEV_BRANCH" ]; then
				echo -e "     ${BLUE}• Branch: ${CYAN}$DEV_BRANCH${NC}"
			fi
			if [ -n "$DEV_SHA" ]; then
				echo -e "     ${BLUE}• Commit: ${CYAN}$DEV_SHA${NC}"
			fi
			echo ""
		fi

		if [[ "$CF_TUNNEL_SETUP" =~ ^[yY]$ ]]; then
			echo -e "  ${GREEN}${BOLD}✓${NC} ${BOLD}Cloudflare Tunnel:${NC} ${CYAN}https://$CF_HOSTNAME${NC}"
			echo -e "     ${BLUE}• Secure HTTPS access via Cloudflare${NC}"
			echo -e "     ${BLUE}• No port forwarding required${NC}"
		elif [ -n "$REVERSE_PROXY_TYPE" ] && [ "$REVERSE_PROXY_TYPE" != "none" ]; then
			if [ "${has_ssl:-false}" = "true" ]; then
				echo -e "  ${GREEN}${BOLD}✓${NC} ${BOLD}Reverse Proxy with SSL:${NC} ${CYAN}https://$panel_domain${NC}"
				echo -e "     ${BLUE}• Secure HTTPS access enabled${NC}"
				echo -e "     ${BLUE}• SSL certificate configured automatically${NC}"
			else
				echo -e "  ${GREEN}${BOLD}✓${NC} ${BOLD}Reverse Proxy:${NC} ${CYAN}http://$panel_domain${NC}"
				echo -e "     ${BLUE}• Add SSL certificate later via SSL menu${NC}"
			fi
			echo -e "     ${BLUE}• Configure DNS to point to your server${NC}"
		else
			echo -e "  ${GREEN}${BOLD}✓${NC} ${BOLD}Direct Access (home hosting / no domain):${NC}"
			echo -e "     ${BLUE}• No domain or SSL required${NC}"
			echo -e "     ${BLUE}• Local: ${CYAN}http://localhost:4832${NC}"
			if [ "$PUBLIC_IP" != "Unable to detect" ]; then
				echo -e "     ${BLUE}• On your network: ${CYAN}http://$PUBLIC_IP:4832${NC}"
				echo -e "     ${YELLOW}• Open port 4832 in your router/firewall if accessing from other devices${NC}"
			else
				echo -e "     ${BLUE}• On your network: ${CYAN}http://YOUR_SERVER_IP:4832${NC}"
				echo -e "     ${YELLOW}• Replace with your machine's IP; open port 4832 if needed${NC}"
			fi
		fi

		echo ""
		draw_hr
		print_centered "👤 Administrator Account" "$YELLOW"
		draw_hr
		echo ""
		echo -e "  ${BOLD}${CYAN}IMPORTANT:${NC} ${YELLOW}The first user to register will automatically become the administrator.${NC}"
		echo -e "  ${BLUE}Make sure you are the first person to create an account!${NC}"
		echo ""
		draw_hr
		print_centered "📋 Next Steps" "$CYAN"
		draw_hr
		echo ""
		echo -e "  ${GREEN}1.${NC} ${BLUE}Wait 5 minutes${NC} for the Panel to fully initialize"
		echo -e "  ${GREEN}2.${NC} ${BLUE}Open the Panel URL${NC} in your web browser"
		echo -e "  ${GREEN}3.${NC} ${BLUE}Register the first account${NC} (this will be the administrator)"
		echo -e "  ${GREEN}4.${NC} ${BLUE}Complete the initial setup${NC} in the Panel interface"
		if [[ ! "$CF_TUNNEL_SETUP" =~ ^[yY]$ ]] && { [ -z "$REVERSE_PROXY_TYPE" ] || [ "$REVERSE_PROXY_TYPE" = "none" ]; }; then
			echo -e "  ${GREEN}5.${NC} ${BLUE}Optional: add SSL later${NC} via main menu → SSL Certificates (not required for home hosting)"
		fi
		echo ""
		draw_hr

		log_info "Installation log saved at: $LOG_FILE"
	elif [ "$COMPONENT_TYPE" = "1" ] && [ "$INST_TYPE" = "2" ]; then
		# Panel Uninstall
		if [ ! -f /var/www/mythicaldash/.installed ]; then
			echo "MythicalDash does not appear to be installed. Nothing to uninstall."
			exit 0
		fi
		prompt "Are you sure you want to uninstall the Docker-based installation? (y/n): " confirm
		if [ "$confirm" = "y" ]; then
			uninstall_docker
		else
			echo "Uninstallation cancelled."
			exit 0
		fi
	elif [ "$COMPONENT_TYPE" = "1" ] && [ "$INST_TYPE" = "3" ]; then
		# Panel Update
		if [ ! -f /var/www/mythicaldash/.installed ]; then
			echo "MythicalDash does not appear to be installed. Nothing to update."
			exit 0
		fi

		# Check current installation type BEFORE doing anything
		CURRENT_IS_DEV=false
		if [ -f /var/www/mythicaldash/docker-compose.yml ]; then
			if grep -q "mythicaldash_v3-backend:dev" /var/www/mythicaldash/docker-compose.yml 2>/dev/null; then
				CURRENT_IS_DEV=true
			fi
		fi

		# Release type selection for updates (only if not already set via CLI/env)
		if [ "$USE_DEV" != "true" ] && [ -z "${FP_DEV:-}" ]; then
			if [ "$CURRENT_IS_DEV" = true ]; then
				# Currently on dev, ask if they want to stay on dev or switch to release
				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				echo -e "${BOLD}${YELLOW}Current Installation Type${NC}"
				draw_hr
				echo ""
				echo -e "${BLUE}Your current installation is using ${BOLD}development builds${NC}.${NC}"
				echo ""
				echo -e "${BOLD}What would you like to do?${NC}"
				echo -e "  ${GREEN}[1]${NC} ${BOLD}Stay on Development Builds${NC} ${BLUE}(Update to latest dev)${NC}"
				echo -e "  ${YELLOW}[2]${NC} ${BOLD}Switch to Stable Release${NC} ${BLUE}(Recommended for production)${NC}"
				draw_hr
				update_choice=""
				while [[ ! "$update_choice" =~ ^[12]$ ]]; do
					prompt "${BOLD}Enter choice${NC} ${BLUE}(1/2)${NC}: " update_choice
					if [[ ! "$update_choice" =~ ^[12]$ ]]; then
						echo -e "${RED}Invalid input.${NC} Please enter ${YELLOW}1${NC} or ${YELLOW}2${NC}."
						sleep 1
					fi
				done

				if [ "$update_choice" = "1" ]; then
					USE_DEV=true
					# Try to detect current branch from compose file
					if grep -q "mythicaldash_v3-backend:dev-v3-remastered" /var/www/mythicaldash/docker-compose.yml 2>/dev/null; then
						DEV_BRANCH="v3-remastered"
					elif grep -q "mythicaldash_v3-backend:dev-" /var/www/mythicaldash/docker-compose.yml 2>/dev/null; then
						# Extract branch from tag
						EXTRACTED_BRANCH=$(grep "mythicaldash_v3-backend:dev-" /var/www/mythicaldash/docker-compose.yml | sed -n 's/.*mythicaldash_v3-backend:dev-\([^-]*\).*/\1/p' | head -1)
						if [ -n "$EXTRACTED_BRANCH" ]; then
							DEV_BRANCH="$EXTRACTED_BRANCH"
						else
							DEV_BRANCH="v3-remastered"
						fi
					else
						DEV_BRANCH="v3-remastered"
					fi
					log_info "Staying on development builds (branch: $DEV_BRANCH)"
				else
					USE_DEV=false
					log_info "Switching to stable release"
				fi
			else
				# Currently on release, ask if they want to switch to dev
				if [ -t 1 ]; then clear; fi
				print_banner
				draw_hr
				echo -e "${BOLD}${CYAN}Update Type${NC}"
				draw_hr
				echo ""
				echo -e "${BLUE}Your current installation is using ${BOLD}stable release${NC}.${NC}"
				echo ""
				echo -e "${BOLD}What would you like to do?${NC}"
				echo -e "  ${GREEN}[1]${NC} ${BOLD}Update to Latest Stable Release${NC} ${BLUE}(Recommended)${NC}"
				echo -e "  ${YELLOW}[2]${NC} ${BOLD}Switch to Development Build${NC} ${BLUE}(Latest from v3-remastered branch)${NC}"
				echo -e "  ${CYAN}[3]${NC} ${BOLD}Switch to Custom Development Build${NC} ${BLUE}(Specific branch/commit)${NC}"
				draw_hr
				update_choice=""
				while [[ ! "$update_choice" =~ ^[1-3]$ ]]; do
					prompt "${BOLD}Enter choice${NC} ${BLUE}(1/2/3)${NC}: " update_choice
					if [[ ! "$update_choice" =~ ^[1-3]$ ]]; then
						echo -e "${RED}Invalid input.${NC} Please enter ${YELLOW}1${NC}, ${YELLOW}2${NC}, or ${YELLOW}3${NC}."
						sleep 1
					fi
				done

				case $update_choice in
				1)
					USE_DEV=false
					log_info "Updating to latest stable release"
					;;
				2)
					USE_DEV=true
					DEV_BRANCH="v3-remastered"
					log_info "Switching to development build from v3-remastered branch"
					;;
				3)
					USE_DEV=true
					if [ -t 1 ]; then clear; fi
					print_banner
					draw_hr
					echo -e "${BOLD}${CYAN}Custom Development Build${NC}"
					draw_hr
					echo ""
					echo -e "${BLUE}Enter the branch name (default: v3-remastered)${NC}"
					prompt "${BOLD}Branch name${NC} ${BLUE}(e.g., v3-remastered, develop)${NC}: " DEV_BRANCH
					if [ -z "$DEV_BRANCH" ]; then
						DEV_BRANCH="v3-remastered"
					fi

					echo ""
					echo -e "${BLUE}Enter a specific commit SHA (optional)${NC}"
					echo -e "${BLUE}Leave empty to use the latest commit from the branch${NC}"
					prompt "${BOLD}Commit SHA${NC} ${BLUE}(7+ characters, optional)${NC}: " DEV_SHA
					if [ -n "$DEV_SHA" ] && [ ${#DEV_SHA} -lt 7 ]; then
						log_warn "Commit SHA should be at least 7 characters. Using latest from branch instead."
						DEV_SHA=""
					fi
					log_info "Switching to custom dev build: branch=$DEV_BRANCH, sha=${DEV_SHA:-latest}"
					;;
				esac
			fi
		fi

		print_banner
		log_step "Updating MythicalDash components..."
		COMPOSE_URL=$(get_compose_file_url)
		if [ ! -f /var/www/mythicaldash/docker-compose.yml ]; then
			if ! run_with_spinner "Downloading docker-compose.yml for MythicalDash" "docker-compose.yml downloaded." \
				curl -fsSL -o /var/www/mythicaldash/docker-compose.yml "$COMPOSE_URL"; then
				upload_logs_on_fail
				exit 1
			fi
		else
			if ! run_with_spinner "Refreshing docker-compose.yml from upstream" "docker-compose.yml refreshed." \
				curl -fsSL -o /var/www/mythicaldash/docker-compose.yml "$COMPOSE_URL"; then
				log_warn "Could not refresh compose file; keeping existing copy."
			fi
		fi

		# Modify docker-compose.yml for dev images if dev mode is enabled
		if [ "$USE_DEV" = true ]; then
			DEV_TAG=$(get_dev_image_tag)
			log_info "Using dev release mode with tag: $DEV_TAG"
			modify_compose_for_dev "/var/www/mythicaldash/docker-compose.yml" "$DEV_TAG" "$DEV_TAG"
		elif [ -f /var/www/mythicaldash/docker-compose.yml ] && grep -q "mythicaldash_v3-backend:dev" /var/www/mythicaldash/docker-compose.yml 2>/dev/null; then
			# Switching from dev to release - restore to latest
			log_info "Switching to release images (latest)"
			modify_compose_for_dev "/var/www/mythicaldash/docker-compose.yml" "latest" "latest"
		fi

		# Stop all existing MythicalDash containers first (including old v1 containers)
		if ! run_with_spinner "Stopping all MythicalDash containers" "All containers stopped." stop_all_mythicaldash_containers; then
			log_warn "Some containers may not have stopped cleanly, continuing..."
		fi

		if ! run_with_spinner "Pulling MythicalDash Docker images" "Docker images updated." bash -c "cd /var/www/mythicaldash && sudo docker compose pull"; then
			upload_logs_on_fail
			exit 1
		fi

		# Check ARM architecture - ARM64 is natively supported
		ARCH=$(uname -m)
		if [[ "$ARCH" == "aarch64" ]] || [[ "$ARCH" == "arm64" ]]; then
			log_info "ARM64 architecture detected: $ARCH"
			log_success "Native ARM64 Docker images are available - no emulation needed."
		elif [[ "$ARCH" == "armv7l" ]] || [[ "$ARCH" == "armv6l" ]]; then
			if [ "$FORCE_ARM" = true ]; then
				log_warn "Unsupported ARM architecture detected: $ARCH (--force-arm flag set)"
			else
				log_info "Unsupported ARM architecture detected: $ARCH - using QEMU emulation"
				log_warn "Note: MythicalDash runs through emulation on unsupported ARM. Consider using AMD64/x86_64 or ARM64 for better performance."
			fi
			setup_qemu_emulation
		fi

		if ! run_with_spinner "Starting MythicalDash stack" "MythicalDash stack started." bash -c "cd /var/www/mythicaldash && sudo docker compose up -d"; then
			log_error "Failed to start MythicalDash stack"
			echo ""
			draw_hr
			echo -e "${RED}${BOLD}Container Start Failure${NC}"
			draw_hr

			# Check Docker logs for common errors
			log_info "Checking Docker container logs..."
			cd /var/www/mythicaldash || true
			CONTAINER_LOGS=$(sudo docker compose logs --tail=50 2>&1 || sudo docker-compose logs --tail=50 2>&1 || echo "")

			if echo "$CONTAINER_LOGS" | grep -qi "exec format error"; then
				echo -e "${RED}${BOLD}Detected: Exec Format Error${NC}"
				echo -e "${YELLOW}This typically means QEMU emulation is not properly configured.${NC}"
				ARCH=$(uname -m)
				echo -e "${YELLOW}Your system architecture: ${BOLD}$ARCH${NC}"
				echo ""
				echo -e "${BLUE}Solution:${NC} Ensure QEMU and binfmt-support are installed and properly configured."
				echo -e "${BLUE}Try:${NC} sudo apt-get install -y qemu qemu-user-static binfmt-support"
			elif echo "$CONTAINER_LOGS" | grep -qi "no space left"; then
				echo -e "${RED}${BOLD}Detected: No Space Left on Device${NC}"
				echo -e "${YELLOW}Your system is out of disk space.${NC}"
				echo -e "${BLUE}Solution:${NC} Free up disk space and try again."
			elif echo "$CONTAINER_LOGS" | grep -qi "permission denied"; then
				echo -e "${RED}${BOLD}Detected: Permission Denied${NC}"
				echo -e "${YELLOW}Docker permission issue detected.${NC}"
				echo -e "${BLUE}Solution:${NC} Ensure Docker is properly configured and you have permissions."
			else
				echo -e "${YELLOW}Container logs (last 20 lines):${NC}"
				echo "$CONTAINER_LOGS" | tail -20
			fi

			echo ""
			draw_hr
			echo -e "${BLUE}For more details, check:${NC}"
			echo -e "  ${CYAN}•${NC} Docker logs: ${BOLD}sudo docker compose -f /var/www/mythicaldash/docker-compose.yml logs${NC}"
			echo -e "  ${CYAN}•${NC} Container status: ${BOLD}sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps${NC}"
			echo -e "  ${CYAN}•${NC} Installation log: ${BOLD}$LOG_FILE${NC}"
			draw_hr
			upload_logs_on_fail
			exit 1
		fi

		# Verify containers are actually running
		sleep 2
		if ! sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps | grep -q "Up"; then
			log_error "Containers started but are not running"
			echo ""
			draw_hr
			echo -e "${RED}${BOLD}Container Status Check Failed${NC}"
			draw_hr
			log_info "Container status:"
			sudo docker compose -f /var/www/mythicaldash/docker-compose.yml ps
			echo ""
			log_info "Recent container logs:"
			sudo docker compose -f /var/www/mythicaldash/docker-compose.yml logs --tail=30
			echo ""
			draw_hr
			upload_logs_on_fail
			exit 1
		fi

		# Always ensure global mythicaldash command is installed/updated
		install_mythicaldash_command

		log_success "MythicalDash updated successfully."
		exit 0
	elif [ "$COMPONENT_TYPE" = "1" ] && [ "$INST_TYPE" = "4" ]; then
		# Panel Backup Manager
		if [ ! -f /var/www/mythicaldash/.installed ]; then
			log_error "MythicalDash is not installed. Nothing to backup."
			exit 1
		fi

		BACKUP_ACTION=""
		while [[ ! "$BACKUP_ACTION" =~ ^[1-6]$ ]]; do
			show_backup_menu
			echo ""
			prompt "${BOLD}${CYAN}Select backup operation${NC} ${BLUE}(1/2/3/4/5/6)${NC}: " BACKUP_ACTION
			if [[ ! "$BACKUP_ACTION" =~ ^[1-6]$ ]]; then
				echo ""
				echo -e "${RED}${BOLD}✗ Invalid input!${NC}"
				echo -e "${YELLOW}Please enter ${BOLD}1${NC} (Create), ${BOLD}2${NC} (List), ${BOLD}3${NC} (Restore), ${BOLD}4${NC} (Delete), ${BOLD}5${NC} (Export), or ${BOLD}6${NC} (Import)${NC}"
				echo ""
				sleep 2
			fi
		done

		case $BACKUP_ACTION in
		1)
			# Create Backup
			if create_backup; then
				log_success "Backup operation completed. See log at $LOG_FILE"
			else
				log_error "Backup operation failed. See log at $LOG_FILE"
				exit 1
			fi
			;;
		2)
			# List Backups
			list_backups
			log_info "Backup listing completed. See log at $LOG_FILE"
			;;
		3)
			# Restore Backup
			if restore_backup; then
				log_success "Backup restore completed. See log at $LOG_FILE"
			else
				log_error "Backup restore failed. See log at $LOG_FILE"
				exit 1
			fi
			;;
		4)
			# Delete Backup
			if delete_backup; then
				log_success "Backup deletion completed. See log at $LOG_FILE"
			else
				log_error "Backup deletion failed. See log at $LOG_FILE"
				exit 1
			fi
			;;
		5)
			# Export for Migration
			if export_migration; then
				log_success "Migration export completed. See log at $LOG_FILE"
			else
				log_error "Migration export failed. See log at $LOG_FILE"
				exit 1
			fi
			;;
		6)
			# Import Migration
			if import_migration; then
				log_success "Migration import completed. See log at $LOG_FILE"
			else
				log_error "Migration import failed. See log at $LOG_FILE"
				exit 1
			fi
			;;
		esac
	elif [ "$COMPONENT_TYPE" = "2" ] && [ "$INST_TYPE" = "1" ]; then
		# SSL - Install Certbot
		install_certbot
		log_success "SSL certificate tools installation finished. See log at $LOG_FILE"
	elif [ "$COMPONENT_TYPE" = "2" ] && [ "$INST_TYPE" = "2" ]; then
		# SSL - Create Certificate (HTTP/Standalone)
		if create_ssl_certificate_http; then
			log_success "SSL certificate creation finished. See log at $LOG_FILE"
		else
			log_error "SSL certificate creation failed. See log at $LOG_FILE"
			exit 1
		fi
	elif [ "$COMPONENT_TYPE" = "2" ] && [ "$INST_TYPE" = "3" ]; then
		# SSL - Create Certificate (DNS)
		if create_ssl_certificate_dns; then
			log_success "SSL certificate creation finished. See log at $LOG_FILE"
		else
			log_error "SSL certificate creation failed. See log at $LOG_FILE"
			exit 1
		fi
	elif [ "$COMPONENT_TYPE" = "2" ] && [ "$INST_TYPE" = "4" ]; then
		# SSL - Setup Auto-Renewal
		if setup_ssl_auto_renewal; then
			log_success "SSL auto-renewal setup finished. See log at $LOG_FILE"
		else
			log_error "SSL auto-renewal setup failed. See log at $LOG_FILE"
			exit 1
		fi
	elif [ "$COMPONENT_TYPE" = "2" ] && [ "$INST_TYPE" = "5" ]; then
		# SSL - Install acme.sh
		install_acme_sh
		log_success "acme.sh installation finished. See log at $LOG_FILE"
	else
		log_error "Invalid component or operation selected."
		exit 1
	fi
else
	# /etc/os-release not found
	if [ "$SKIP_OS_CHECK" = true ]; then
		log_warn "OS check skipped via --skip-os-check flag"
		echo ""
		draw_hr
		echo -e "${YELLOW}${BOLD}⚠️  Warning: OS Check Skipped${NC}"
		draw_hr
		echo -e "${YELLOW}Cannot determine OS - /etc/os-release not found${NC}"
		echo -e "${YELLOW}This installer officially supports:${NC}"
		echo -e "  ${GREEN}•${NC} Debian 11, 12, or 13"
		echo -e "  ${GREEN}•${NC} Ubuntu 22.04 LTS, 24.04 LTS, or 25.04"
		echo ""
		echo -e "${BLUE}Continuing with installation at your own risk...${NC}"
		echo ""
		draw_hr
		sleep 3
		# Set default values for OS variables to prevent errors later
		OS="unknown"
		OS_VERSION="unknown"
	else
		log_error "Cannot determine OS - /etc/os-release not found"
		echo -e "${RED}${BOLD}This installer only supports:${NC}"
		echo -e "  ${GREEN}•${NC} Debian 11, 12, or 13"
		echo -e "  ${GREEN}•${NC} Ubuntu 22.04 LTS, 24.04 LTS, or 25.04"
		echo ""
		echo -e "${BLUE}To bypass this check, use: ${BOLD}--skip-os-check${NC}"
		support_hint
		exit 1
	fi
fi