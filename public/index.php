<?php

/**
 * @link https://github.com/ptrofimov/beanstalk_console
 * @link http://kr.github.com/beanstalkd/
 * @author Petr Trofimov, Sergey Lysenko
 */
error_reporting(E_ALL);
ini_set('display_errors', true);

require_once '../lib/include.php';
$console = new Console;
$errors = $console->getErrors();
$fields = $console->getTubeStatFields();
$groups = $console->getTubeStatGroups();
$visible = $console->getTubeStatVisible();
$tplVars = $console->getTplVars();
extract($tplVars);
?>

<?php require_once "../lib/tpl/{$_tplMain}.php" ?>
