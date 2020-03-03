<?php
namespace GoSearch;

/**
 * Daemon HTTP Server
 */
class HttpServer
{

    /**
     * init
     *
     * @param \React\EventLoop\Factory $loop    event loop
     * @param array                    $routing routing array
     * @param int                      $port    http port
     *
     * @return void
     */
    public static function init($loop, array $routing, int $port)
    {
        $server = new \React\Http\Server(
            function (\Psr\Http\Message\ServerRequestInterface $request) use ($routing) {
                $action = $request->getUri()->getPath();
                if (isset($routing[$action])) {
                    $status = 200;
                    $message = $routing[$action]($request->getUri()->getQuery());
                } else {
                    $status = 404;
                    // $message = json_encode($routing);
                    $message = "$action not found\n";
                }

                return new \React\Http\Response($status, ['Content-Type' => 'text/plain'], $message);
            }
        );

        $socket = new \React\Socket\Server($port, $loop);
        $server->listen($socket);

        \GoSearch\Helper\Message::debugLog("[HttpServer] Server running at http://127.0.0.1:{$port}}}");
    }
}
