## Recipes

### JDBC example

Demonstrate usage of JDBC as it still is a very popular example in Java. 

> Note that iterating over large resultset with the bridge in that way 
> is very expensive in terms of performance. See the [considerations](https://github.com/belgattitude/soluble-japha#considerations)
> and eventually refer to the [JDBCPerformanceTest.php](https://github.com/belgattitude/soluble-japha/blob/master/test/src/SolubleTest/Japha/Db/JDBCPerformanceTest.php) test script for
> alternatives. 


Ensure your servlet installation can [locate the JDBC driver](https://github.com/belgattitude/php-java-bridge/tree/master/init-scripts) and try :

```php
<?php

use Soluble\Japha\Bridge\Exception;

// $ba = new BridgeAdapter(...); 

$driverClass = 'com.mysql.jdbc.Driver';
$dsn = "jdbc:mysql://localhost/my_database?user=login&password=pwd";

try {

    $driverManager = $ba->javaClass('java.sql.DriverManager');

    $class = $ba->javaClass('java.lang.Class');
    $class->forName($driverClass);
    
    $conn = $driverManager->getConnection($dsn);

} catch (Exception\ClassNotFoundException $e) {
    // Probably the jdbc driver is not registered
    // on the JVM side. Check that the mysql-connector.jar
    // is installed
    echo $e->getMessage();
    echo $e->getStackTrace();
} catch (Exception\JavaException $e) {
    echo $e->getMessage();
    echo $e->getStackTrace();
}
try {
    $stmt = $conn->createStatement();
    $rs = $stmt->executeQuery('select * from product');
    while ($rs->next()) {
        $title = $rs->getString("title");
        echo $title;            
    }        
    if (!$ba->isNull($rs)) {
        $rs->close();
    }
    if (!$ba->isNull($stmt)) {
        $stmt->close();
    }
    $conn->close();
} catch (Exception\JavaException $e) {
    echo $e->getMessage();
    // Because it's a JavaException
    // you can use the java stack trace
    echo $e->getStackTrace();
} catch (\Exception $e) {
    echo $e->getMessage();
}

```

### SSL client socket, readers and writers

Demonstrate some possible uses of streams *(code is irrelevant from a PHP point of view)*.

```php
<?php

// $ba = new BridgeAdapter(...); 

$serverPort = 443;
$host = 'www.google.com';

$socketFactory = $ba->javaClass('javax.net.ssl.SSLSocketFactory')->getDefault();
$socket = $socketFactory->createSocket($host, $serverPort);

$socket->startHandshake();
$bufferedWriter = $ba->java('java.io.BufferedWriter',
            $ba->java('java.io.OutputStreamWriter',
                    $socket->getOutputStream()
            )
        );

$bufferedReader = $ba->java('java.io.BufferedReader',
            $ba->java('java.io.InputStreamReader',
                $socket->getInputStream()
            )
        );

$bufferedWriter->write("GET / HTTP/1.0");
$bufferedWriter->newLine();
$bufferedWriter->newLine(); // end of HTTP request
$bufferedWriter->flush();

$lines = [];
do {
    $line = $bufferedReader->readLine();
    $lines[] = (string) $line;
} while(!$ba->isNull($line));

$content = implode("\n", $lines);
echo $content;

$bufferedWriter->close();
$bufferedReader->close();
$socket->close();

```
