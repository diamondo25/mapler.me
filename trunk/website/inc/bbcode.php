<?php

require_once __DIR__.'/bb/Parser.php';
$parser = new JBBCode\Parser();
$parser->loadDefaultCodes();

//Images
$parser->addBBCode("avatar", '<img src="//'.$domain.'/avatar/{param}"/>');
$parser->addBBCode("card", '<img src="//'.$domain.'/card/{param}"/>');
$parser->addBBCode("stats", '<img src="//'.$domain.'/infopic/{param}"/>');

//Links
$parser->addBBCode("player", '<a href="//'.$domain.'/player/{param}">{param}<sup>(character)</sup></a>');

//Various
$parser->addBBCode("br", '<br />');
?>