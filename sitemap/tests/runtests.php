#!/usr/bin/env php
<?php
require_once(dirname(__FILE__) . '/lib/tests_runner/common.inc.php');
require_once(dirname(__FILE__) . '/lib/tests_runner/src/lmbTestRunner.class.php');
require_once(dirname(__FILE__) . '/lib/tests_runner/src/lmbTestTreeFilePathNode.class.php');

$runner = new lmbTestRunner();
$res = $runner->run(new lmbTestTreeFilePathNode(dirname(__FILE__) . '/cases/'));
exit($res ? 0 : 1);
