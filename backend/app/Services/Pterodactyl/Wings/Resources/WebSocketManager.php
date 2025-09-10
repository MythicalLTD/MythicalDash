<?php

/*
 * This file is part of MythicalDash.
 *
 * MIT License
 *
 * Copyright (c) 2020-2025 MythicalSystems
 * Copyright (c) 2020-2025 Cassian Gherman (NaysKutzu)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * Please rather than modifying the dashboard code try to report the thing you wish on our github or write a plugin
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
