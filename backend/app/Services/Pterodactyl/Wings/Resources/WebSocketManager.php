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

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use React\EventLoop\Loop;
use Ratchet\Client\WebSocket;
use React\Promise\PromiseInterface;
use MythicalDash\Services\Pterodactyl\Wings\WingsClient;

class WebSocketManager extends WingsClient
{
    /**
     * Connect to the WebSocket server.
     *
     * @param string $serverId Server identifier
     * @param callable $onMessage Callback for handling messages
     * @param callable $onError Callback for handling errors
     *
     * @return PromiseInterface<WebSocket>
     */
    public function connect(string $serverId, callable $onMessage, callable $onError): PromiseInterface
    {
        $wsUrl = str_replace('http', 'ws', $this->url) . "/api/servers/{$serverId}/ws";

        return \Ratchet\Client\connect($wsUrl, [], [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
            ],
        ])->then(
            function (WebSocket $conn) use ($onMessage, $onError) {
                $conn->on('message', $onMessage);
                $conn->on('error', $onError);
                $conn->on('close', function () {
                    Loop::stop();
                });

                return $conn;
            },
            $onError
        );
    }

    /**
     * Send a command through WebSocket.
     *
     * @param WebSocket $conn WebSocket connection
     * @param string $command Command to send
     */
    public function sendWebSocketCommand(WebSocket $conn, string $command): void
    {
        $conn->send(json_encode([
            'event' => 'console:command',
            'data' => $command,
        ]));
    }

    /**
     * Send a power signal through WebSocket.
     *
     * @param WebSocket $conn WebSocket connection
     * @param string $signal Power signal (start, stop, restart, kill)
     */
    public function sendWebSocketPowerSignal(WebSocket $conn, string $signal): void
    {
        $conn->send(json_encode([
            'event' => 'power:signal',
            'data' => $signal,
        ]));
    }

    /**
     * Subscribe to server stats.
     *
     * @param WebSocket $conn WebSocket connection
     */
    public function subscribeToStats(WebSocket $conn): void
    {
        $conn->send(json_encode([
            'event' => 'stats:subscribe',
        ]));
    }

    /**
     * Unsubscribe from server stats.
     *
     * @param WebSocket $conn WebSocket connection
     */
    public function unsubscribeFromStats(WebSocket $conn): void
    {
        $conn->send(json_encode([
            'event' => 'stats:unsubscribe',
        ]));
    }
}
