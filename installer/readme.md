# Mythical Dash installer (Beta)

## Installer Overview
The Mythical Dash installer is a comprehensive script designed to automate the installation, management, and uninstallation of MythicalDash, a web application. It supports both Docker-based and bare-metal installations on Debian and Ubuntu-based systems, handling dependencies, configuration, and optional services like Cloudflare Tunnel.

## Prerequisites
*   **Operating System:** Debian or Ubuntu-based distribution (e.g., Ubuntu 20.04+, Debian 10+).
*   **Required Tools:** `curl`, `wget`, `apt-transport-https`, `lsb-release`, `ca-certificates`, `gnupg`, `jq`, `make`, `unzip`, `tar`, `git`, `zip`, `redis-server`, `dos2unix`. These will be installed automatically by the script if missing.
*   **Permissions:** The script requires `sudo` privileges to install packages and configure system services.
*   **Docker (for Docker installation):** If choosing the Docker installation, Docker will be installed automatically if not present.
*   **Internet Connectivity:** Required for downloading packages and dependencies.

## Usage Examples

### Running the Installer
To start the interactive installer, simply execute the `install.sh` script:
```bash
chmod +x ./install.sh
./install.sh
```
The script will guide you through the installation process with a series of prompts.

### Docker Installation
When prompted, select the Docker installation option. The script will set up MythicalDash within Docker containers.

### Non-Docker (Bare Metal) Installation
When prompted, select the non-Docker installation option. The script will install all required services (PHP, MariaDB, Redis, Nginx) directly on your system.

### Common Flags/Options
The `install.sh` script is primarily interactive and does not currently support command-line flags for installation options. All choices are made via prompts during execution.

## Optional Flows

*   **Cloudflare Tunnel Integration:** The installer offers both "Full Automatic" and "Semi-Automatic" Cloudflare Tunnel setup.
    *   **Full Automatic:** Requires Cloudflare API Key, email, and desired hostname. The script will create the tunnel and configure DNS records automatically.
    *   **Semi-Automatic:** Requires a pre-generated Cloudflare Tunnel token. You will need to manually configure DNS records and ingress rules in your Cloudflare dashboard.
*   **Pterodactyl Panel Integration:** The installer can configure Pterodactyl panel settings (URL and API key) during installation. This can also be done manually after installation.

## Post-Installation

*   **Verification:**
    *   Check if MythicalDash is accessible via your configured domain or IP address.
    *   Verify that all services (MariaDB, Redis, Nginx/Docker containers) are running.
    *   For non-Docker installations, check cron jobs using `crontab -l`.
*   **Logs:**
    *   System logs: `journalctl -u <service_name>` (e.g., `journalctl -u mariadb`, `journalctl -u redis-server`, `journalctl -u nginx`).
    *   MythicalDash application logs: Located within the `/var/www/mythicaldash-v3/backend/storage/logs` directory.
*   **Troubleshooting:**
    *   Refer to the MythicalDash official documentation for common issues: [https://docs.mythical.systems/](https://docs.mythical.systems/)
    *   Ensure all prerequisites are met.
    *   Check firewall settings if you are unable to access the application.
*   **Further Documentation:**
    *   MythicalDash Official Documentation: [https://docs.mythical.systems/](https://docs.mythical.systems/)
    *   Pterodactyl Panel Documentation: [https://pterodactyl.io/](https://pterodactyl.io/)