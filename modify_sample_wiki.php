#!/usr/bin/php

<?php
include_once("./formation.php");

$wiki_page = "./sample.wiki";
$data = file_get_contents($wiki_page);
$data = modify_content($data, "Engineering");
print "$data\n";
?>
