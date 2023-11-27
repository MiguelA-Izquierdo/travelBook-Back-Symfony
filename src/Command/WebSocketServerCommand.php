<?php

namespace App\Command;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Controller\WebSocketController;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebSocketServerCommand extends Command
{
    protected static $defaultName = 'websocket:serve';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketController()
                )
            ),
            8080 // Puerto en el que escuchará el servidor WebSocket
        );

        $output->writeln('Servidor WebSocket iniciado en localhost:8080');

        $server->run();

        return 0;
    }
}
