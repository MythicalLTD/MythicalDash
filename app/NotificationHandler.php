<?php
namespace MythicalDash;

class NotificationHandler
{
    /**
     * Get notifications from the JSON file.
     *
     * @return array
     */
    private static function getNotifications(): array
    {
        $file = '../caches/notifications.json';
        if (file_exists($file)) {
            $data = file_get_contents($file);
            return json_decode($data, true);
        } else {
            return [];
        }
    }

    /**
     * Save notifications to the JSON file.
     *
     * @param array $notifications
     *
     * @return void
     */
    private static function saveNotifications(array $notifications): void
    {
        $file = '../caches/notifications.json';
        file_put_contents($file, json_encode($notifications, JSON_PRETTY_PRINT));
    }

    /**
     * Create a new notification.
     *
     * @param string $user_id The user id.
     * @param string $name The notification name.
     * @param string $description The notification description.
     *
     * @return int The id of the created notification.
     */
    public static function create(string $user_id, string $name, string $description): int
    {
        $notifications = self::getNotifications();
        $notificationID = count($notifications) + 1;
        $notification = [
            'id' => $notificationID,
            'user_id' => $user_id,
            'name' => $name,
            'description' => $description,
            'date' => date('Y-m-d H:i'),
        ];

        $notifications[] = $notification;
        self::saveNotifications($notifications);
        return $notificationID;
    }

    /**
     * Edit an existing notification by ID.
     *
     * @param int $id The id of the notification to edit.
     * @param string $name The new notification name.
     * @param string $description The new notification description.
     *
     * @return void
     */
    public static function edit(int $id, string $name, string $description): void
    {
        $notifications = self::getNotifications();

        foreach ($notifications as &$notification) {
            if ($notification['id'] === $id) {
                $notification['name'] = $name;
                $notification['description'] = $description;
                break;
            }
        }

        self::saveNotifications($notifications);
    }

    /**
     * Delete a notification by ID.
     *
     * @param int $id The id of the notification to delete.
     *
     * @return void
     */
    public static function delete(int $id): void
    {
        $notifications = self::getNotifications();

        $notifications = array_filter($notifications, function ($notification) use ($id) {
            return $notification['id'] !== $id;
        });

        self::saveNotifications(array_values($notifications));
    }

    /**
     * Delete all notifications.
     *
     * @return void
     */
    public static function deleteAll(): void
    {
        self::saveNotifications([]);
    }

    /**
     * Get a single notification by ID.
     *
     * @param int $id The id of the notification to retrieve.
     *
     * @return array|null The notification data or null if not found.
     */
    public static function getOne(int $id): ?array
    {
        $notifications = self::getNotifications();

        foreach ($notifications as $notification) {
            if ($notification['id'] === $id) {
                return $notification;
            }
        }

        return null;
    }

    /**
     * Get all notifications.
     *
     * @return array All notifications.
     */
    public static function getAll(): array
    {
        return self::getNotifications();
    }

    /**
     * Get all notifications sorted by ID in descending order.
     *
     * @return array Sorted notifications.
     */
    public static function getAllSortedById(): array
    {
        $notifications = self::getNotifications();

        usort($notifications, function ($a, $b) {
            return $b['id'] - $a['id'];
        });

        return $notifications;
    }

    /**
     * Get all notifications sorted by date in descending order.
     *
     * @return array Sorted notifications.
     */
    public static function getAllSortedByDate(): array
    {
        $notifications = self::getNotifications();

        usort($notifications, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $notifications;
    }

    /**
     * Get notifications filtered by user ID.
     *
     * @param string $user_id The user id to filter by.
     *
     * @return array Filtered notifications.
     */
    public static function getByUserId(string $user_id): array
    {
        $notifications = self::getNotifications();

        $filteredNotifications = array_filter($notifications, function ($notification) use ($user_id) {
            return $notification['user_id'] === $user_id;
        });

        return $filteredNotifications;
    }
}
?>