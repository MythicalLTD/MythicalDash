<?php

/*
 * This file is part of MythicalDash.
 * Please view the LICENSE file that was distributed with this source code.
 *
 * # MythicalSystems License v2.0
 *
 * ## Copyright (c) 2021–2025 MythicalSystems and Cassian Gherman
 *
 * Breaking any of the following rules will result in a permanent ban from the MythicalSystems community and all of its services.
 */

namespace MythicalDash\Chat\User;

use MythicalDash\Chat\Database;
use MythicalDash\Chat\Earn\ShareUS;
use MythicalDash\Chat\Earn\LinkPays;
use MythicalDash\Chat\Servers\Server;
use MythicalDash\Chat\Earn\GyaniLinks;
use MythicalDash\Chat\Earn\Linkvertise;
use MythicalDash\Chat\columns\UserColumns;
use MythicalDash\Chat\Referral\ReferralUses;
use MythicalDash\Chat\Referral\ReferralCodes;
use MythicalDash\Chat\interface\LeaderboardTypes;

class Leaderboard extends Database
{
    /**
     * Get the leaderboard for a given type.
     *
     * @param int $limit The number of users to return
     * @param LeaderboardTypes|string $type The type of leaderboard to get
     *
     * @return array The leaderboard data
     */
    public static function getLeaderboard(int $limit = 15, LeaderboardTypes|string $type = LeaderboardTypes::COINS)
    {
        try {
            $con = self::getPdoConnection();
            $leaderboard = [];

            switch ($type) {
                case LeaderboardTypes::$COINS:
                    $stmt = $con->prepare('
						SELECT u.uuid, u.username, u.avatar, u.role, u.' . UserColumns::CREDITS . ' as credits_count, COUNT(*) as rank 
						FROM ' . User::TABLE_NAME . ' u
						WHERE u.deleted = "false"
						GROUP BY u.' . UserColumns::UUID . '
						ORDER BY u.' . UserColumns::CREDITS . ' DESC
						LIMIT :limit
					');
                    break;

                case LeaderboardTypes::$SERVERS:
                    $stmt = $con->prepare('
						SELECT u.uuid, u.username, u.avatar, u.role, COUNT(s.id) as server_count
						FROM ' . User::TABLE_NAME . ' u
						LEFT JOIN ' . Server::TABLE_NAME . ' s ON u.uuid = s.user
						WHERE u.deleted = "false" AND s.deleted = "false"
						GROUP BY u.uuid
						ORDER BY server_count DESC
						LIMIT :limit
					');
                    break;

                case LeaderboardTypes::$MINUTES_AFK:
                    $stmt = $con->prepare('
						SELECT u.uuid, u.username, u.avatar, u.role, u.' . UserColumns::MINUTES_AFK . ' as minutes_afk_count, COUNT(*) as rank 
						FROM ' . User::TABLE_NAME . ' u
						WHERE u.deleted = "false"
						GROUP BY u.' . UserColumns::UUID . '
						ORDER BY u.' . UserColumns::MINUTES_AFK . ' DESC
						LIMIT :limit
					');
                    break;

                case LeaderboardTypes::$LINKVERTISE:
                    $stmt = $con->prepare('
						SELECT u.uuid, u.username, u.avatar, u.role, COUNT(l.id) as link_count, COUNT(*) as rank 
						FROM ' . User::TABLE_NAME . ' u
						LEFT JOIN ' . Linkvertise::getTableName() . ' l ON u.uuid = l.user
						WHERE u.deleted = "false" AND l.deleted = "false"
						GROUP BY u.uuid
						ORDER BY link_count DESC
						LIMIT :limit
					');
                    break;

                case LeaderboardTypes::$SHAREUS:
                    $stmt = $con->prepare('
						SELECT u.uuid, u.username, u.avatar, u.role, COUNT(s.id) as link_count, COUNT(*) as rank 
						FROM ' . User::TABLE_NAME . ' u
						LEFT JOIN ' . ShareUS::getTableName() . ' s ON u.uuid = s.user
						WHERE u.deleted = "false" AND s.deleted = "false"
						GROUP BY u.uuid
						ORDER BY link_count DESC
						LIMIT :limit
					');
                    break;

                case LeaderboardTypes::$GYANILINKS:
                    $stmt = $con->prepare('
						SELECT u.uuid, u.username, u.avatar, u.role, COUNT(g.id) as link_count, COUNT(*) as rank 
						FROM ' . User::TABLE_NAME . ' u
						LEFT JOIN ' . GyaniLinks::getTableName() . ' g ON u.uuid = g.user
						WHERE u.deleted = "false" AND g.deleted = "false"
						GROUP BY u.uuid
						ORDER BY link_count DESC
						LIMIT :limit
					');
                    break;

                case LeaderboardTypes::$LINKPAYS:
                    $stmt = $con->prepare('
						SELECT u.uuid, u.username, u.avatar, u.role, COUNT(l.id) as link_count, COUNT(*) as rank 
						FROM ' . User::TABLE_NAME . ' u
						LEFT JOIN ' . LinkPays::getTableName() . ' l ON u.uuid = l.user
						WHERE u.deleted = "false" AND l.deleted = "false"
						GROUP BY u.uuid
						ORDER BY link_count DESC
						LIMIT :limit
					');
                    break;

                case LeaderboardTypes::$REFERRALS:
                    $stmt = $con->prepare('	
						SELECT u.uuid, u.username, u.avatar, u.role, COUNT(r.id) as referral_count, COUNT(*) as rank 
						FROM ' . User::TABLE_NAME . ' u
						LEFT JOIN ' . ReferralCodes::getTableName() . ' ref ON u.uuid = ref.user
						LEFT JOIN ' . ReferralUses::getTableName() . ' r ON ref.id = r.referral_code_id 
						WHERE u.deleted = "false" AND r.deleted = "false" AND ref.deleted = "false"
						GROUP BY u.uuid
						ORDER BY referral_count DESC
						LIMIT :limit
					');
                    break;

                default:
                    throw new \Exception('Invalid leaderboard type');
            }

            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            $stmt->execute();
            $leaderboard = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Add ranking to each user
            $rank = 1;
            foreach ($leaderboard as &$user) {
                $user['rank'] = $rank++;
            }

            return $leaderboard;
        } catch (\Exception $e) {
            Database::db_Error('Failed to get leaderboard: ' . $e->getMessage());

            return [];
        }
    }
}
