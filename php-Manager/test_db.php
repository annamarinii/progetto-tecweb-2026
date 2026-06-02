<?php

require_once 'db_connection.php';

echo "<h1>Test di Connessione</h1>";

$test_conn = DBConnection::getConnessione();

if ($test_conn) {
    echo "<p class='success-msg'>✅ Test superato! Ora sei Gay.</p>";

    echo "<p>Info Server: " . $test_conn->server_info . "</p>";

    $test_conn->close();
}
