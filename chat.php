<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

    // Make sure composer dependencies have been installed
    require __DIR__ . '/vendor/autoload.php';

/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class MyChat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";;
        
    }

   public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        $data = json_decode($msg);
        echo sprintf('Connection %s sending message "%s" to %d other connection%s' . "\n"
            , $data->name1,$data->message, $numRecv, $numRecv == 1 ? '' : 's');

            
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($data->name1.": ".$data->message);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        
        foreach ($this->clients as $client) {
            
                // The sender is not the receiver, send to each client connected
                $client->send("User ".$conn->resourceId." Leaved");
          
        }
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
    private function dispaly_user_name(ConnectionInterface $from, $msg)
    {
        
        $data = json_decode($msg);
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                
                $client->send("User $data->name1 joins the chat\n",$from);
            }
        }
    }
    // set connection name
    private function setConnectionName(ConnectionInterface $from, $name)
    {
        return $this->clients->offsetSet($from, $name);
    }
}

    // Run the server application through the WebSocket protocol on port 8080
    $app = new Ratchet\App('localhost', 8000);
    $app->route('/chat', new MyChat, array('*'));
    $app->route('/echo', new Ratchet\Server\EchoServer, array('*'));
    $app->run();