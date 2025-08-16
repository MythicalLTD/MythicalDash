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

namespace MythicalDash\Plugins;

class PluginEvents
{
    public static $_instance;
    /**
     * All listeners for the specified event.
     */
    protected array $listeners = [];

    /**
     * Returns the current instance of the PluginEvent class.
     */
    public static function getInstance(): PluginEvents
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Adds a listener for the specified event.
     *
     * @param string $event the name of the event
     * @param callable $listener the listener function to be added
     *
     * @return static returns the current instance of the PluginEvent class
     */
    public function on(string $event, callable $listener): static
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;

        return $this;
    }

    /**
     * Removes a listener for the specified event.
     *
     * @param string $event the name of the event
     * @param callable $listener the listener function to be removed
     */
    public function removeListener(string $event, callable $listener): void
    {
        if (isset($this->listeners[$event])) {
            $index = array_search($listener, $this->listeners[$event], true);

            if ($index !== false) {
                unset($this->listeners[$event][$index]);

                if (count($this->listeners[$event]) === 0) {
                    unset($this->listeners[$event]);
                }
            }
        }
    }

    /**
     * Removes all listeners for the specified event or all events if no event is specified.
     *
     * @param string|null $event the name of the event (optional)
     */
    public function removeAllListeners(?string $event = null): void
    {
        if ($event !== null) {
            unset($this->listeners[$event]);
        } else {
            $this->listeners = [];
        }
    }

    /**
     * Removes all listeners for the specified event or all events if no event is specified.
     *
     * @param string|null $event the name of the event (optional)
     */
    public function listeners(?string $event = null): array
    {
        if ($event === null) {
            $events = [];
            $eventNames = array_unique(
                array_merge(
                    array_keys($this->listeners),
                )
            );

            foreach ($eventNames as $eventName) {
                $events[$eventName] = array_merge(
                    $this->listeners[$eventName] ?? [],
                );
            }

            return $events;
        }

        return array_merge(
            $this->listeners[$event] ?? [],
        );
    }

    /**
     * Emits the specified event and triggers all associated listeners.
     *
     * @param string $event the name of the event
     * @param array $arguments the arguments to be passed to the listeners (optional)
     */
    public function emit(string $event, array $arguments = []): void
    {
        $listeners = [];
        if (isset($this->listeners[$event])) {
            $listeners = array_values($this->listeners[$event]);
        }

        if ($listeners !== []) {
            foreach ($listeners as $listener) {
                // Convert associative array to indexed array for spread operator
                $args = array_values($arguments);
                $listener(...$args);
            }
        }
    }
}
