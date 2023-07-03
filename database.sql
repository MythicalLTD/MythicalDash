/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `client`
--

-- --------------------------------------------------------

--
-- Table structure for table `adfoc`
--

CREATE TABLE `adfoc` (
  `id` int(11) NOT NULL,
  `sckey` text NOT NULL,
  `created` date NOT NULL DEFAULT current_timestamp(),
  `claim` text NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `skey` text NOT NULL,
  `uid` text NOT NULL,
  `created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` text NOT NULL,
  `coins` text NOT NULL DEFAULT '0',
  `ram` text NOT NULL DEFAULT '0',
  `disk` text NOT NULL DEFAULT '0',
  `cpu` text NOT NULL DEFAULT '0',
  `slots` text NOT NULL DEFAULT '0',
  `dbs` text NOT NULL DEFAULT '0',
  `alloc` text NOT NULL DEFAULT '0',
  `bks` text NOT NULL DEFAULT '0',
  `hmtcliams` text NOT NULL DEFAULT '1' COMMENT 'How many times can a user claim a coupon code!',
  `createdon` text NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eggs`
--

CREATE TABLE `eggs` (
  `id` int(11) NOT NULL,
  `egg` int(255) NOT NULL,
  `nest` int(255) NOT NULL DEFAULT 1,
  `category` text NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `icon` text NOT NULL,
  `locationid` int(255) NOT NULL,
  `status` text NOT NULL,
  `slots` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `ipaddr` text NOT NULL,
  `userid` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` text NOT NULL,
  `username` text NOT NULL,
  `ticket_id` text NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `token` text NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_claims`
--

CREATE TABLE `referral_claims` (
  `id` int(11) NOT NULL,
  `code` text NOT NULL,
  `uid` text NOT NULL,
  `timestamp` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referral_codes`
--

CREATE TABLE `referral_codes` (
  `id` int(11) NOT NULL,
  `uid` text NOT NULL,
  `referral` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL DEFAULT 'Member',
  `canlogin` text NOT NULL DEFAULT 'true',
  `cancreate` text NOT NULL DEFAULT 'true',
  `canafk` text NOT NULL DEFAULT 'true',
  `canlinkvertise` text NOT NULL DEFAULT 'true',
  `canredeem` text NOT NULL DEFAULT 'true',
  `canearn` text NOT NULL DEFAULT 'true',
  `canseeprofile` text NOT NULL DEFAULT 'true',
  `canbuy` text NOT NULL DEFAULT 'true',
  `canbypassmaintenance` text NOT NULL DEFAULT 'false',
  `canseeadminhomepage` text NOT NULL DEFAULT 'false' COMMENT 'Can see admin home page ',
  `canseeusers` text NOT NULL DEFAULT 'false',
  `candeleteusers` text NOT NULL DEFAULT 'false' COMMENT 'Can edit users',
  `caneditusers` text NOT NULL DEFAULT 'false' COMMENT 'Can delete users',
  `canseeservers` text NOT NULL DEFAULT 'false' COMMENT 'Can see all servers',
  `caneditservers` text NOT NULL DEFAULT 'false' COMMENT 'Can edit all servers',
  `candeleteservers` text NOT NULL DEFAULT 'false' COMMENT 'Can delete all servers',
  `caneditappsettings` text NOT NULL DEFAULT 'false' COMMENT 'Can user edit application settings (WARNING DONT GIVE THIS PERMISIONS TO SOME 1 THAT YOU DONT TRUST THIS PERMISION HAS FULL ACCES TO THE DASHBOARD)',
  `issupport` text NOT NULL DEFAULT 'false' COMMENT 'Dose this role have acces to to see tickets and talk into them?',
  `fullperm` text NOT NULL DEFAULT 'false' COMMENT 'THIS PERM HAS FULL ACCES TO EVERYTHING ON THE DASH '
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `pid` int(255) NOT NULL,
  `suspended` enum('false','true') NOT NULL DEFAULT 'false',
  `uid` varchar(255) NOT NULL,
  `location` int(255) NOT NULL,
  `timestamp` varchar(255) NOT NULL,
  `created` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servers_queue`
--

CREATE TABLE `servers_queue` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `ram` int(255) NOT NULL,
  `disk` int(255) NOT NULL,
  `cpu` decimal(65,0) NOT NULL,
  `xtra_ports` int(255) NOT NULL,
  `databases` int(255) NOT NULL,
  `backuplimit` int(255) NOT NULL,
  `location` int(255) NOT NULL,
  `ownerid` varchar(255) NOT NULL,
  `type` int(255) NOT NULL,
  `egg` int(255) NOT NULL,
  `puid` varchar(255) NOT NULL,
  `created` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL DEFAULT 'MythicalNodes' COMMENT 'Name of your host',
  `logo` text NOT NULL DEFAULT 'https://avatars.githubusercontent.com/u/117385445',
  `seo_description` text NOT NULL DEFAULT 'Reliable free Minecraft hosting services powered by Intel processors for developers, new and experienced Minecraft server owners! Start your adventure using MythicalNodes to connect with players and friends around the globe!',
  `seo_keywords` text NOT NULL DEFAULT 'Free Minecraft Server Hosting, Free Hosting, Free minecraft hosting, free game hosting, mcworld.cc, mcworld, mcworld free hosting, mcworld hosting, minecraft hosting, mythicalnodes, Mythical, Mythic, MythicHosting, NaysHostin',
  `seo_color` text NOT NULL DEFAULT '#335BFF',
  `website` text NOT NULL DEFAULT 'https://mythicalnodes.xyz' COMMENT 'Main website link, not client',
  `statuspage` text NOT NULL DEFAULT 'https://status.mythicalnodes.xyz' COMMENT 'Status page link',
  `discordserver` text NOT NULL DEFAULT 'https://discord.mythicalnodes.xyz' COMMENT ' Discord server invite link',
  `enable_mainwebsite` text NOT NULL DEFAULT 'true',
  `enable_discord` text DEFAULT 'true',
  `enable_phpmyadmin` text NOT NULL DEFAULT 'true',
  `enable_status` text NOT NULL DEFAULT 'true',
  `privacypolicy` text NOT NULL DEFAULT 'https://mythicalnodes.xyz/tos.html' COMMENT 'Privacy policy - If you want to start an host, please do this or you''ll get drama.gg''ed ;)',
  `termsofservice` text NOT NULL DEFAULT 'https://mythicalnodes.xyz/tos.html' COMMENT 'Terms of service - NOT RULES! :) - If you want to start an host, please do this or you''ll get drama.gg''ed ;)',
  `phpmyadmin` text NOT NULL DEFAULT 'https://pma.yourlunk.net',
  `linkvertise_status` text NOT NULL DEFAULT 'true',
  `linkvertise` text NOT NULL DEFAULT '  <script src="https://publisher.linkvertise.com/cdn/linkvertise.js"></script><script>linkvertise(583258, {whitelist: ["panel.mythicalnodes.xyz","status.mythicalnodes.xyz","phpmyadmin.mythicalnodes.xyz","mythicalnodes.xyz","discord.mythicalnodes.xyz"], blacklist: ["my.mythicalnodes.xyz","panel.f1xmc.ro","deploy.mythicalnodes.xyz"]});</script>',
  `home_background` text NOT NULL DEFAULT 'https://cdn.mythicalnodes.xyz/minecraft.jpg' COMMENT 'The background of the home page',
  `homeNews_show` text NOT NULL DEFAULT 'false',
  `homeNews_title` text DEFAULT 'Join our discord',
  `homeNews_content` text NOT NULL DEFAULT 'Join our discord to know everything what\'s going to happen with our host',
  `homeNews_bgimage` text DEFAULT NULL COMMENT '// Leave empty for none | we recommend a darken background image, for better text reading on light images',
  `homeNews_bgcolor` text DEFAULT NULL COMMENT '// Leave empty for the default color',
  `homeNews_buttonLink` text NOT NULL DEFAULT 'https://discord.mythicalnodes.xyz',
  `homeNews_buttonText` text NOT NULL DEFAULT 'Join',
  `vipqueue` text NOT NULL DEFAULT '30' COMMENT 'Price of the vip queue',
  `coinsref` text NOT NULL DEFAULT '10' COMMENT 'Coins per referral',
  `disable_earning` text NOT NULL DEFAULT 'false',
  `enable_afk` text NOT NULL DEFAULT 'true',
  `afk_coins_per_min` text NOT NULL DEFAULT '1',
  `afk_min` text NOT NULL DEFAULT '1',
  `proto` text NOT NULL DEFAULT 'https://' COMMENT 'protocol for the client area. Must be http or https with the :// at the end.',
  `ptero_url` text NOT NULL DEFAULT 'https://panel.mythicalnodes.xyz' COMMENT 'The url to your pterodactyl web server. This will be used for API.',
  `ptero_apikey` text NOT NULL DEFAULT 'dasdasdasdaskdasödlk' COMMENT ' [!] Must be an application api key with all rights.',
  `adfoc_coins` text NOT NULL DEFAULT '5',
  `def_memory` text NOT NULL DEFAULT '1024' COMMENT 'Change this for default plan	',
  `def_disk_space` text NOT NULL DEFAULT '2048' COMMENT 'Change this for default plan	',
  `def_cpu` text NOT NULL DEFAULT '100' COMMENT 'Change this for default plan	',
  `def_server_limit` text NOT NULL DEFAULT '1' COMMENT 'Change this for default plan	',
  `def_port` text NOT NULL,
  `def_data` text NOT NULL,
  `def_back` text NOT NULL,
  `cpuprice` text NOT NULL DEFAULT '450',
  `ramprice` text NOT NULL DEFAULT '350',
  `diskprice` text NOT NULL DEFAULT '250',
  `svslotprice` text NOT NULL DEFAULT '300',
  `backupprice` text NOT NULL DEFAULT '100',
  `databaseprice` text NOT NULL DEFAULT '100',
  `portsprice` text NOT NULL DEFAULT '100',
  `webhook` text DEFAULT 'https://discord.webhook/test',
  `smtpHost` text NOT NULL,
  `smtpPort` text NOT NULL,
  `smtpSecure` enum('ssl','tls') NOT NULL,
  `smtpUsername` text NOT NULL,
  `smtpPassword` text NOT NULL,
  `fromEmail` text NOT NULL,
  `version` text NOT NULL DEFAULT '1.0.0' COMMENT 'DONT CHANGE THIS',
  `maintenance` text NOT NULL DEFAULT 'false',
  `isinstalled` text NOT NULL DEFAULT 'true' COMMENT 'DONT CHANGE THIS'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` text NOT NULL,
  `username` text NOT NULL,
  `content` text NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `panel_id` int(11) NOT NULL,
  `user_id` text NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `first_name` text NOT NULL DEFAULT 'Client',
  `last_name` text NOT NULL DEFAULT 'Client',
  `email` varchar(255) DEFAULT NULL,
  `password` text NOT NULL,
  `discord_id` text DEFAULT NULL,
  `discord_username` text DEFAULT NULL,
  `discord_discriminator` text DEFAULT NULL,
  `discord_email` text DEFAULT NULL,
  `session_id` text DEFAULT NULL,
  `visibility` text NOT NULL DEFAULT 'Public',
  `avatar` text NOT NULL,
  `background` text NOT NULL DEFAULT 'https://wallpaper.dog/large/999992.jpg',
  `aboutme` text NOT NULL DEFAULT 'Hello and welcome to my profile page!',
  `role` text NOT NULL DEFAULT 'Member',
  `minutes_idle` int(111) DEFAULT NULL,
  `last_seen` bigint(111) NOT NULL DEFAULT 0,
  `coins` decimal(65,2) NOT NULL DEFAULT 0.00 COMMENT 'Change this for default plan',
  `memory` int(255) NOT NULL DEFAULT 2048 COMMENT 'Change this for default plan',
  `disk_space` int(255) NOT NULL DEFAULT 10000 COMMENT 'Change this for default plan',
  `ports` int(255) DEFAULT 1 COMMENT 'Change this for default plan',
  `databases` int(255) DEFAULT 1 COMMENT 'Change this for default plan',
  `cpu` varchar(255) NOT NULL DEFAULT '60' COMMENT 'Change this for default plan',
  `server_limit` int(255) NOT NULL DEFAULT 2 COMMENT 'Change this for default plan',
  `backup_limit` int(255) NOT NULL COMMENT 'Change this for default plan	',
  `register_ip` text NOT NULL,
  `lastlogin_ip` text NOT NULL,
  `last_login` text NOT NULL,
  `banned` tinyint(4) NOT NULL DEFAULT 0,
  `banned_reason` longtext DEFAULT NULL,
  `staff` tinyint(4) NOT NULL DEFAULT 0,
  `registered` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adfoc`
--
ALTER TABLE `adfoc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eggs`
--
ALTER TABLE `eggs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_claims`
--
ALTER TABLE `referral_claims`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_codes`
--
ALTER TABLE `referral_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servers_queue`
--
ALTER TABLE `servers_queue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adfoc`
--
ALTER TABLE `adfoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eggs`
--
ALTER TABLE `eggs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_claims`
--
ALTER TABLE `referral_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referral_codes`
--
ALTER TABLE `referral_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `servers_queue`
--
ALTER TABLE `servers_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
