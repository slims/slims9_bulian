<?php
// prevent the server from timing out
set_time_limit(0);

// call configuration 
define('INDEX_AUTH', '1');
require 'sysconfig.inc.php';

// include the web sockets server script (the server is started at the far bottom of this file)
require 'lib/phpwebsocket.php';

// when a client sends data to the server
function wsOnMessage($clientID, $message, $messageLength, $binary) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	// check if message length is 0
	if ($messageLength == 0) {
		$Server->wsClose($clientID);
		return;
	}

	//The speaker is the only person in the room. Don't let them feel lonely.
	if ( sizeof($Server->wsClients) == 1 )
		$Server->wsSend($clientID, "There isn't anyone else in the room, but I'll still listen to you. --Your Trusty Server");
	else
		//Send the message to everyone but the person who said it
		foreach ( $Server->wsClients as $id => $client )
			if ( $id != $clientID ) {
				$_message = explode("|", $message);
				$Server->wsSend($id, '<strong class="c'.$clientID.'">'.$_message[0].":</strong> ".$_message[1]);
			}
}

// when a client connects
function wsOnOpen($clientID)
{
	echo $clientID;
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log("Someone has connected." );

	//Send a join notice to everyone but the person who joined
	foreach ( $Server->wsClients as $id => $client )
		if ( $id != $clientID )
			$Server->wsSend($id, "Someone has joined the room.");
}

// when a client closes or lost connection
function wsOnClose($clientID, $status) {
	global $Server;
	$ip = long2ip( $Server->wsClients[$clientID][6] );

	$Server->log( "Someone has disconnected." );

	//Send a user left notice to everyone in the room
	foreach ( $Server->wsClients as $id => $client )
		$Server->wsSend($id, "Someone has left the room.");
}

// start the server
$Server = new PHPWebSocket();
$Server->bind('message', 'wsOnMessage');
$Server->bind('open', 'wsOnOpen');
$Server->bind('close', 'wsOnClose');
// for other computers to connect, you will probably need to change this to your LAN IP or external IP,
// alternatively use: gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME']))
$Server->wsStartServer($sysconf['chat_system']['server'], $sysconf['chat_system']['server_port']);