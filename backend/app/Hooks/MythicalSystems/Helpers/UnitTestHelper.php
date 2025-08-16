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
 * Make sure to read the docs before making any changes. And note that any changes you make will be overwritten by the next update.
 *
 * Be careful with the code you write, and make sure to test it before committing it.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
 */

namespace MythicalDash\Hooks\MythicalSystems\Helpers;

class UnitTestHelper
{
    /**
     * Init a unit test.
     *
     * @param string $name The unit test name
     * @param int $id The unit test id
     *
     * @return bool Failed or success?
     */
    public static function init(string $name, int $id): bool
    {
        if (isset($name) && !$name == null) {
            if (isset($id) && !$id == null) {
                return true;
            }

            return false;

        }

        return false;

    }

    /**
     * Start an unit test.
     *
     * @param int $id The id of the test
     *
     * @return bool Failed or success?
     */
    public static function up(int $id): bool
    {
        if (isset($id) && !$id == null) {
            return true;
        }

        return false;

    }

    /**
     * Stop an unit test.
     *
     * @return bool Failed or success?
     */
    public static function down(int $id): bool
    {
        if (isset($id) && !$id == null) {
            return true;
        }

        return false;

    }

    /**
     * Check if a unit test failed or not.
     *
     * @return bool Failed or success?
     */
    public static function check(int $id): bool
    {
        if (isset($id) && !$id == null) {
            return true;
        }

        return false;

    }
}
