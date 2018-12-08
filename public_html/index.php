<?php
/**
 * Main index
 *
 * @copyright 2011-2018 Timo Tijhof
 * @package wmf-tool-wikiinfo
 */

/**
 * Configuration
 * -------------------------------------------------
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../class.php';

$tool = new WikiInfoTool();
require_once __DIR__ . '/../config.php';

$I18N = new Intuition( 'getwikiapi' );

$kgBase = BaseTool::newFromArray( array(
	'displayTitle' => $I18N->msg( 'title' ),
	'remoteBasePath' => dirname( $_SERVER['PHP_SELF'] ),
	'styles' => array(
		'main.css',
	),
	'scripts' => array(
		'main.js',
	),
	'I18N' => $I18N,
) );
$kgBase->setSourceInfoGithub( 'Krinkle', 'wmf-tool-wikiinfo', dirname( __DIR__ ) );

/**
 * Output
 * -------------------------------------------------
 */

$tool->run();
$kgBase->flushMainOutput();
