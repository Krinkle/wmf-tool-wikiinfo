<?php
/**
 * Main index
 *
 * @author Timo Tijhof, 2011-2015
 * @license http://krinkle.mit-license.org/
 * @package wmf-tool-wikiinfo
 */

/**
 * Configuration
 * -------------------------------------------------
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../class.php';

$tool = new WikiInfoTool();

$I18N = new Intuition( 'getwikiapi' );

$kgBase = BaseTool::newFromArray( array(
	'displayTitle' => $I18N->msg( 'title' ),
	'remoteBasePath' => dirname( $kgConf->getRemoteBase() ). '/',
	'revisionId' => '1.5.0',
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
