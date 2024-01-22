ALTER TABLE mythicaldash_settings
ADD COLUMN `FEEDBACK_1_NAME` text NOT NULL DEFAULT 'Roido' AFTER `show_snow`,
ADD COLUMN `FEEDBACK_1_ROLE` text NOT NULL DEFAULT 'Member' AFTER `FEEDBACK_1_NAME`,
ADD COLUMN `FEEDBACK_1_AVATAR` text NOT NULL DEFAULT 'https://www.gravatar.com/avatar/default' AFTER `FEEDBACK_1_ROLE`,
ADD COLUMN `FEEDBACK_1_DESCRIPTION` text NOT NULL DEFAULT "Pretty good, easy to make themes and edit its interface. I've been using it for half a month now, seems pretty good ❤️ " AFTER `FEEDBACK_1_AVATAR`,
ADD COLUMN `FEEDBACK_2_NAME` text NOT NULL DEFAULT 'NaysKutzu' AFTER `FEEDBACK_1_DESCRIPTION`,
ADD COLUMN `FEEDBACK_2_ROLE` text NOT NULL DEFAULT 'Member' AFTER `FEEDBACK_2_NAME`,
ADD COLUMN `FEEDBACK_2_AVATAR` text NOT NULL DEFAULT 'https://www.gravatar.com/avatar/default' AFTER `FEEDBACK_2_ROLE`,
ADD COLUMN `FEEDBACK_2_DESCRIPTION` text NOT NULL DEFAULT '5-star service! The server performance is outstanding, and the user-friendly control panel makes managing my server a breeze. Highly recommended for all gamers!' AFTER `FEEDBACK_2_AVATAR`,
ADD COLUMN `FEEDBACK_3_NAME` text NOT NULL DEFAULT 'KolombisGaiming' AFTER `FEEDBACK_2_DESCRIPTION`,
ADD COLUMN `FEEDBACK_3_ROLE` text NOT NULL DEFAULT 'Member' AFTER `FEEDBACK_3_NAME`,
ADD COLUMN `FEEDBACK_3_AVATAR` text NOT NULL DEFAULT 'https://www.gravatar.com/avatar/default' AFTER `FEEDBACK_3_ROLE`,
ADD COLUMN `FEEDBACK_3_DESCRIPTION` text NOT NULL DEFAULT 'Top-tier hosting for Minecraft! The server response time is impressive, and the available resources allow for a smooth gaming experience. Great value for the quality of service provided.' AFTER `FEEDBACK_3_AVATAR`;
