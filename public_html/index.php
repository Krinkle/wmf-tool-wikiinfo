<?php
/**
 * Main index
 *
 * @author Timo Tijhof, 2011-2014
 * @license http://krinkle.mit-license.org/
 * @package wmf-tool-wikinfo
 */

/**
 * Configuration
 * -------------------------------------------------
 */

// BaseTool & Localization
require_once __DIR__ . '/../lib/base/InitTool.php';
require_once KR_TSINT_START_INC;

// Class for this tool
require_once __DIR__ . '/../class.php';
$tool = new WikiInfoTool();

$I18N = new Intuition( 'getwikiapi' );

$kgBase = BaseTool::newFromArray( array(
	'displayTitle' => $I18N->msg( 'title' ),
	'remoteBasePath' => dirname( $kgConf->getRemoteBase() ). '/',
	'revisionId' => '0.4.0',
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
