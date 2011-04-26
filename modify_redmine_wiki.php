#!/usr/bin/php
<?php
include_once("./formation.php");

$db_host = "127.0.0.1";
$db_name = "redmine";
$db_user = "redmine_user";
$db_pass = "redmine_pass";

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name";
    $pdo = new PDO($dsn, $db_user, $db_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $e) {
    echo "Failed to get DB handle: " . $e->getMessage() . "\n";
    exit;
}

// $pdo->query('SET NAMES utf8'); // This would also resolve charset issues, however, I prefer to resolve it in object creation. 
$query = $pdo->prepare("
        SELECT t1.title, t2.page_id, t2.text FROM wiki_pages AS t1, wiki_contents AS t2
        WHERE t1.id=t2.page_id
        ");
$query->execute();

$query_update = $pdo->prepare("
        UPDATE wiki_contents SET
        text = :text
        WHERE page_id = :page_id
        ");

for($i=0; $row = $query->fetch(PDO::FETCH_ASSOC); $i++) {
    $text = modify_content($row['text'], $row['title']);
    echo "Page Title: " . $row['title'] . "\n";
    $query_update->execute(array(
                ':text' => $text,
                ':page_id' => $row['page_id']
                ));
}

unset($query);
unset($pdo);

?>
