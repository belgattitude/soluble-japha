# Recipes

!!! warning
    For learning purposes, the bridge optimizations techniques might not have 
    been applied to the recipe examples. That way, most examples will better correspond 
    to their equivalent Java syntax, they should be optimized for
    best performance. Learn more in the [language optimization](./language_optimizations.md) section. 

## Third party

The following examples are based on third-party Java libraries. 

!!! tip
    When using third party libraries, always check their license.
    Their installation can be done easily added when 
    [building](./install_server.md) your own Javabridge server. 
    *(pre-made init-scripts are available [here](https://github.com/belgattitude/php-java-bridge/blob/master/init-scripts/README.md)).*
    

### Json 

Json serialization can be particularly whenever you want to retrieve
a java object representation. 

    
#### GSON  
  
Gson is a fast and simple json serializer from google. 
    
    
```php
<?php
//...

$gson = $ba->java('com.google.gson.Gson');

$simpleDateFormat = $ba->java('java.text.SimpleDateFormat', 'yyyy-MM-dd');

$hashMap = $ba->java('java.util.HashMap', [
    'integer' => 1,
    'phpstring' => 'PHP Héllo',
    'javastring' => $ba->java('java.lang.String', 'Java Héllo'),
    'javadate' => $simpleDateFormat->parse('2017-05-20')
]);

$jsonString = (string) $gson->toJson($hashMap);
// Will produce:
//   {
//     "javastring":"Java Héllo",
//     "javadate":"May 20, 2017 12:00:00 AM",
//     "phpstring":"PHP Héllo",
//     "integer":1
//   }

$decoded = json_decode($jsonString);
// assertEquals('Java Héllo', $decoded->javastring);
    
```    

#### Json-io  
  
[Json-io](https://github.com/jdereg/json-io) is another serializer. 

```php
<?php
//...

$jsonWriter = $ba->javaClass('com.cedarsoftware.util.io.JsonWriter');

$simpleDateFormat = $ba->java('java.text.SimpleDateFormat', 'yyyy-MM-dd');

$hashMap = $ba->java('java.util.HashMap', [
    'integer' => 1,
    'phpstring' => 'PHP Héllo',
    'javastring' => $ba->java('java.lang.String', 'Java Héllo'),
    'javadate' => $simpleDateFormat->parse('2017-05-20')
]);

$jsonString = (string) $jsonWriter->objectToJson($hashMap);
// Will produce
// {
//   "@type":"java.util.HashMap",
//   "javastring":"Java Héllo",
//   "javadate": {
//         "@type":"date",
//         "value":1495231200000
//   },
//   "phpstring":"PHP Héllo",
//   "integer": {
//          "@type":"int",
//          "value":1
//   }
// }

$decoded = json_decode($jsonString);
// assertEquals('date', $decoded->javadate->{'@type'});
// assertEquals('Java Héllo', $decoded->javastring);

```    
    
### CoreNLP

Example based on the [http://stanfordnlp.github.io/CoreNLP/simple.html](http://stanfordnlp.github.io/CoreNLP/simple.html).

```php
<?php declare(strict_types=1);

function getSentences(Adapter $ba, string $text): array
{
    $doc = $ba->java('edu.stanford.nlp.simple.Document', $text);
    $sentences = $doc->sentences();
    
    $d = $ba->getDriver();
    $results = [];

    foreach($sentences as $idx => $sentence) {
        $results[$idx] = [
            'sentence' => (string) $sentence,
            'words'    => $d->values($sentence->words()),
            
            // If you have a model installed you can
            // use lemmas(), posTags(), parse()... methods
            'lemmas'  => $d->values($sentence->lemmas()),
            'posTags' => $d->values($sentence->posTags()), 
            'parse'   => $d->values($sentence->parse())
        ];
    }
    
    return $results;
}

$text = "add your text here! It can contain multiple sentences. Hello world.";
$results = getSentences($ba, $text);
assertEquals('add your text here!', $results[0]['sentence']);
assertEquals('Hello world.', $results[2]['sentence']);
assertEquals('Hello', $results[2]['words'][0]);
```

!!! tip
    While the bridge might offer more flexibility, CoreNLP provides a [rest server](http://stanfordnlp.github.io/CoreNLP/corenlp-server.html)
    that should be considered first for integration with PHP.         


### JDBC example

Demonstrate the usage of JDBC as it still is a very popular example in Java. 

!!! warning
    Iterating over probable large resultsets with the bridge 
    as illustrated on the JDBC example is very expensive in terms of performance. 
    This code should not be used with the bridge unless no other option exists.
    See the performance and best practices to learn why.
    
    
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

!!! tip
    You can easily add MySQL connector to your bridge server, pre-made
    build scripts are available [here](https://github.com/belgattitude/php-java-bridge/blob/master/init-scripts/README.md). 


## Standard runtime

Those examples can be tested on a standard JVM install (no third party requirements).

### SSL sockets

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
