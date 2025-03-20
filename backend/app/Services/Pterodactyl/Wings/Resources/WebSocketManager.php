<?php

namespace MythicalDash\Services\Pterodactyl\Wings\Resources;

use MythicalDash\Services\Pterodactyl\Wings\WingsClient;
use Ratchet\Client\WebSocket;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;

class WebSocketManager extends WingsClient
{
    /**
     * Connect to the WebSocket server
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
     * Send a command through WebSocket
     *
     * @param WebSocket $conn WebSocket connection
     * @param string $command Command to send
     * @return void
     */
    public function sendWebSocketCommand(WebSocket $conn, string $command): void
    {
        $conn->send(json_encode([
            'event' => 'console:command',
            'data' => $command,
        ]));
    }

    /**
     * Send a power signal through WebSocket
     *
     * @param WebSocket $conn WebSocket connection
     * @param string $signal Power signal (start, stop, restart, kill)
     * @return void
     */
    public function sendWebSocketPowerSignal(WebSocket $conn, string $signal): void
    {
        $conn->send(json_encode([
            'event' => 'power:signal',
            'data' => $signal,
        ]));
    }

    /**
     * Subscribe to server stats
     *
     * @param WebSocket $conn WebSocket connection
     * @return void
     */
    public function subscribeToStats(WebSocket $conn): void
    {
        $conn->send(json_encode([
            'event' => 'stats:subscribe',
        ]));
    }

    /**
     * Unsubscribe from server stats
     *
     * @param WebSocket $conn WebSocket connection
     * @return void
     */
    public function unsubscribeFromStats(WebSocket $conn): void
    {
        $conn->send(json_encode([
            'event' => 'stats:unsubscribe',
        ]));
    }
} 