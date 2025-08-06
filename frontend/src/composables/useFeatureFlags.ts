import { computed } from 'vue';
import { useSettingsStore } from '@/stores/settings';

export function useFeatureFlags() {
    const settings = useSettingsStore();

    // Core application features
    const isDiscordEnabled = computed(() => settings.getBooleanSetting('discord_enabled'));
    const isGithubEnabled = computed(() => settings.getBooleanSetting('github_enabled'));
    const isPaypalEnabled = computed(() => settings.getBooleanSetting('enable_paypal'));
    const isStripeEnabled = computed(() => settings.getBooleanSetting('enable_stripe'));
    const isSMTPEnabled = computed(() => settings.getBooleanSetting('smtp_enabled'));
    const isTelemetryEnabled = computed(() => settings.getBooleanSetting('telemetry_enabled'));

    // Authentication features
    const isForce2FA = computed(() => settings.getBooleanSetting('force_2fa'));
    const isForceDiscordLink = computed(() => settings.getBooleanSetting('force_discord_link'));
    const isForceMailLink = computed(() => settings.getBooleanSetting('force_mail_link'));
    const isForceGithubLink = computed(() => settings.getBooleanSetting('force_github_link'));

    // User features
    const isAFKEnabled = computed(() => settings.getBooleanSetting('afk_enabled'));
    const isAllowCoinsSharing = computed(() => settings.getBooleanSetting('allow_coins_sharing'));
    const isAllowPublicProfiles = computed(() => settings.getBooleanSetting('allow_public_profiles'));
    const isAllowServers = computed(() => settings.getBooleanSetting('allow_servers'));
    const isAllowTickets = computed(() => settings.getBooleanSetting('allow_tickets'));
    const isCodeRedemptionEnabled = computed(() => settings.getBooleanSetting('code_redemption_enabled'));
    const isCreditsRechargeEnabled = computed(() => settings.getBooleanSetting('credits_recharge_enabled'));
    const isReferralsEnabled = computed(() => settings.getBooleanSetting('referrals_enabled'));
    const isLeaderboardEnabled = computed(() => settings.getBooleanSetting('leaderboard_enabled'));

    // Server features
    const isServerRenewEnabled = computed(() => settings.getBooleanSetting('server_renew_enabled'));
    const isShowNodePing = computed(() => settings.getBooleanSetting('show_node_ping'));

    // Store features
    const isStoreEnabled = computed(() => settings.getBooleanSetting('store_enabled'));
    const isBlockBackups = computed(() => settings.getBooleanSetting('block_backups'));
    const isBlockCPU = computed(() => settings.getBooleanSetting('block_cpu'));
    const isBlockDatabases = computed(() => settings.getBooleanSetting('block_databases'));
    const isBlockDisk = computed(() => settings.getBooleanSetting('block_disk'));
    const isBlockPorts = computed(() => settings.getBooleanSetting('block_ports'));
    const isBlockRAM = computed(() => settings.getBooleanSetting('block_ram'));
    const isBlockServerSlots = computed(() => settings.getBooleanSetting('block_server_slots'));

    // Image hosting features
    const isImageHostingEnabled = computed(() => settings.getBooleanSetting('image_hosting_enabled'));
    const isImageHostingCoinsPerImageEnabled = computed(() =>
        settings.getBooleanSetting('image_hosting_coins_per_image_enabled'),
    );

    // Earning features
    const isJ4REnabled = computed(() => settings.getBooleanSetting('j4r_enabled'));
    const isL4REnabled = computed(() => settings.getBooleanSetting('l4r_enabled'));

    // L4R specific features
    const isL4RGyanilinksEnabled = computed(() => settings.getBooleanSetting('l4r_gyanilinks_enabled'));
    const isL4RLinkadvertiseEnabled = computed(() => settings.getBooleanSetting('l4r_linkadvertise_enabled'));
    const isL4RLinkpaysEnabled = computed(() => settings.getBooleanSetting('l4r_linkpays_enabled'));
    const isL4RShareusEnabled = computed(() => settings.getBooleanSetting('l4r_shareus_enabled'));

    // Security features
    const isAntiAdblockerEnabled = computed(() => settings.getBooleanSetting('anti_adblocker_enabled'));
    const isFirewallEnabled = computed(() => settings.getBooleanSetting('firewall_enabled'));
    const isFirewallBlockAlts = computed(() => settings.getBooleanSetting('firewall_block_alts'));
    const isFirewallBlockVPN = computed(() => settings.getBooleanSetting('firewall_block_vpn'));
    const isZeroTrustEnabled = computed(() => settings.getBooleanSetting('zero_trust_enabled'));
    const isZeroTrustBlockTorEnabled = computed(() => settings.getBooleanSetting('zero_trust_block_tor_enabled'));
    const isZeroTrustEnhancedLoggingEnabled = computed(() =>
        settings.getBooleanSetting('zero_trust_enhanced_logging_enabled'),
    );
    const isZeroTrustServerScanToolEnabled = computed(() =>
        settings.getBooleanSetting('zero_trust_server_scan_tool_enabled'),
    );
    const isZeroTrustWhitelistIPsEnabled = computed(() =>
        settings.getBooleanSetting('zero_trust_whitelist_ips_enabled'),
    );

    // Early supporters features
    const isEarlySupportersEnabled = computed(() => settings.getBooleanSetting('early_supporters_enabled'));

    // Backup features
    const isDailyBackupEnabled = computed(() => settings.getBooleanSetting('daily_backup_enabled'));

    // Turnstile features
    const isTurnstileEnabled = computed(() => settings.getBooleanSetting('turnstile_enabled'));

    // Google Ads features
    const isGoogleAdsEnabled = computed(() => settings.getBooleanSetting('google_ads_enabled'));

    // Discord specific features
    const isDiscordForceJoinServer = computed(() => settings.getBooleanSetting('discord_force_join_server'));
    const isDiscordLinkAllowed = computed(() => settings.getBooleanSetting('discord_link_allowed'));

    // GitHub specific features
    const isGithubLinkAllowed = computed(() => settings.getBooleanSetting('github_link_allowed'));

    // Image hosting specific features
    const isImageHostingAllowDomains = computed(() => settings.getBooleanSetting('image_hosting_allow_domains'));

    // Helper function to check if any L4R service is enabled
    const isAnyL4RServiceEnabled = computed(
        () =>
            isL4RGyanilinksEnabled.value ||
            isL4RLinkadvertiseEnabled.value ||
            isL4RLinkpaysEnabled.value ||
            isL4RShareusEnabled.value,
    );

    // Helper function to check if any payment method is enabled
    const isAnyPaymentMethodEnabled = computed(() => isPaypalEnabled.value || isStripeEnabled.value);

    // Helper function to check if any social login is enabled
    const isAnySocialLoginEnabled = computed(() => isDiscordEnabled.value || isGithubEnabled.value);

    // Helper function to check if any security feature is enabled
    const isAnySecurityFeatureEnabled = computed(
        () => isFirewallEnabled.value || isZeroTrustEnabled.value || isAntiAdblockerEnabled.value,
    );

    // Helper function to check if any earning method is enabled
    const isAnyEarningMethodEnabled = computed(
        () => isAFKEnabled.value || isJ4REnabled.value || isL4REnabled.value || isReferralsEnabled.value,
    );

    return {
        // Core features
        isDiscordEnabled,
        isGithubEnabled,
        isPaypalEnabled,
        isStripeEnabled,
        isSMTPEnabled,
        isTelemetryEnabled,

        // Authentication features
        isForce2FA,
        isForceDiscordLink,
        isForceMailLink,
        isForceGithubLink,

        // User features
        isAFKEnabled,
        isAllowCoinsSharing,
        isAllowPublicProfiles,
        isAllowServers,
        isAllowTickets,
        isCodeRedemptionEnabled,
        isCreditsRechargeEnabled,
        isReferralsEnabled,
        isLeaderboardEnabled,

        // Server features
        isServerRenewEnabled,
        isShowNodePing,

        // Store features
        isStoreEnabled,
        isBlockBackups,
        isBlockCPU,
        isBlockDatabases,
        isBlockDisk,
        isBlockPorts,
        isBlockRAM,
        isBlockServerSlots,

        // Image hosting features
        isImageHostingEnabled,
        isImageHostingCoinsPerImageEnabled,

        // Earning features
        isJ4REnabled,
        isL4REnabled,

        // L4R specific features
        isL4RGyanilinksEnabled,
        isL4RLinkadvertiseEnabled,
        isL4RLinkpaysEnabled,
        isL4RShareusEnabled,

        // Security features
        isAntiAdblockerEnabled,
        isFirewallEnabled,
        isFirewallBlockAlts,
        isFirewallBlockVPN,
        isZeroTrustEnabled,
        isZeroTrustBlockTorEnabled,
        isZeroTrustEnhancedLoggingEnabled,
        isZeroTrustServerScanToolEnabled,
        isZeroTrustWhitelistIPsEnabled,

        // Early supporters features
        isEarlySupportersEnabled,

        // Backup features
        isDailyBackupEnabled,

        // Turnstile features
        isTurnstileEnabled,

        // Google Ads features
        isGoogleAdsEnabled,

        // Discord specific features
        isDiscordForceJoinServer,
        isDiscordLinkAllowed,

        // GitHub specific features
        isGithubLinkAllowed,

        // Image hosting specific features
        isImageHostingAllowDomains,

        // Helper computed properties
        isAnyL4RServiceEnabled,
        isAnyPaymentMethodEnabled,
        isAnySocialLoginEnabled,
        isAnySecurityFeatureEnabled,
        isAnyEarningMethodEnabled,
    };
}
