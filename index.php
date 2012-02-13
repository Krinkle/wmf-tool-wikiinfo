<?php
/**
 * GetWikiAPI
 * Created on March 23, 2011
 *
 * @author Krinkle <krinklemail@gmail.com>, 2011 - 2012
 * @license Public domain, WTFPL
 */

/**
 * Configuration
 * -------------------------------------------------
 */
// BaseTools
require_once( '/home/krinkle/common/InitTool.php' );
// Localization
require_once( KR_TSINT_START_INC );

$I18N = new TsIntuition( 'Getwikiapi' /* textdomain */ );

$toolConfig = array(
	'displayTitle'	=> _( 'title' ),
	'simplePath'	=> '/getWikiAPI.php',
	'revisionId'	=> '0.2.0',
	'revisionDate'	=> $I18N->dateFormatted( '2012-02-13' ) ,
	'I18N'			=> $I18N,
);

$Tool = BaseTool::newFromArray( $toolConfig );

$Tool->doHtmlHead();
$Tool->doStartBodyWrapper();


/**
 * Database connection
 * -------------------------------------------------
 */
kfConnectToolserverDB();


/**
 * Settings
 * -------------------------------------------------
 */
$toolSettings = array(
	'wikiids' => getParamVar( 'wikiids', '', $_REQUEST ),
	'format' => getParamVar( 'format', 'xhtml', $_REQUEST ),
	'callback' => getParamVar( 'callback', '', $_REQUEST ),
);
$params = array();
$params['wikiids'] = $toolSettings['wikiids'];
$params['callback'] = $toolSettings['callback'];

/**
 * Local functions for getWikiAPI
 * -------------------------------------------------
 */
// Sanatize and fix trailing slashes or (partial) urls
function cleanWikiID( $wikiid ) {
	$wikiid = strtolower( trim( $wikiid ) );

	if ( substr( $wikiid, 0, 4 ) == 'http' && strpos( $wikiid, '://' ) ) {
		$parsed = parse_url( $wikiid . '/', PHP_URL_HOST );

	} elseif ( substr( $wikiid, -1 ) == '/' ) {
		$parsed = parse_url( 'http://' . $wikiid, PHP_URL_HOST );
	}

	if ( !empty( $parsed ) ) {
		return $parsed;
	}
	return $wikiid;

}
// Selects for query
function getDBSelectArray( $wikiidEscaped ) {
	return array(
		/* autocomplete nice-ness */

		/* media -> mediawikiwiki_p, nl -> nlwiki_p */
		"dbname LIKE '" . $wikiidEscaped . "wiki%'",

		/* commonswiki -> commonswiki_p, nostal -> nostalgiawiki_p */
		"dbname LIKE '" . $wikiidEscaped . "%'",

		/* en.wiki		=> en.wikipedia.org */
		"domain LIKE '" . $wikiidEscaped . "%'",
	);
}
// Do the query
function doQueryForWikiData( $wikiid ) {
	global $Tool, $kgConf;

	$wikiidClean = cleanWikiID( $wikiid );

	$wikiidEscaped = mysql_clean( $wikiidClean );

	$selectArray = getDBSelectArray( $wikiidEscaped );

	$dbQuery = " /* LIMIT:15 */
	SELECT *
	FROM wiki
	WHERE " . join( " OR ", $selectArray ) . "
	ORDER BY size DESC
	LIMIT 1
	";
	kfLog( 'dbQuery: ' . $dbQuery, __FUNCTION__ );

	$dbReturn = kfDoSelectQueryRaw( $dbQuery );
	if ( !is_array( $dbReturn ) || !isset( $dbReturn[0] ) ) {
		$dbResults = false;;
	} else {
		$dbResults = $dbReturn[0];
	}

	$wikiData = wikiDataFromRow( $dbResults );
	return array(
		'input' => $wikiid,
		'search' => $wikiidClean,
		'match' => !!$dbResults,
		'data' => !!$dbResults ? $wikiData : array(),
	);
}

/**
 * Input form
 * -------------------------------------------------
 */
$Tool->addOut( _( 'input' ), 'h3', array( 'id' => 'input' ) );

$form = '<form class="colly ns" action="' . $Tool->remoteBasePath . '" method="get">
			<fieldset>
				<legend>' . _( 'form-legend-settings', 'krinkle' ) . '</legend>

				<label for="wikiids">' . _( 'label-wikiids' ) . _g( 'colon-separator' ) . '</label>
				<input type="text" id="wikiids" name="wikiids" placeholder="wiki" value="' . kfEscapeHTML( $toolSettings['wikiids'] ) . '" />
				<span>commonswiki, nl, enwiki_p, de.wikipedia, http://meta.wikimedia.org, http://wikisource.org/?diff=3 ' . _g( 'etc' ) . '</span>
				<br />

				<label></label>
				<input type="submit" nof value="' . kfEscapeHTML( _g('form-submit') ) . '" />
			</fieldset>
		</form>';
$Tool->addOut( $form );


/**
 * Output (if submitted)
 * -------------------------------------------------
 */
if ( $toolSettings['wikiids'] ) :

	$wikiids = explode( '|', $toolSettings['wikiids'] );
	$results = array();
	foreach ( $wikiids as $wikiid ) {
		$results[$wikiid] = doQueryForWikiData( $wikiid );
	}

	kfCloseAllConnections();
	$stillAlive = kfApiExport( $results, $toolSettings['format'], $toolSettings['callback'], 'xhtml' );

	if ( $stillAlive ) {
		// The API module exporter did not kill the page yet which means 'xhtml' was the format,
		// which we handle ourselfs here in the GUI
		foreach ( $results as $wikiid => $result ) {

			$Tool->addOut( _( 'output', array( 'variables' => array( $wikiid ) ) ), 'h3', array( 'id' => 'output' ) );

			if ( !$result['match'] ) {

				$opts = array(
					'variables' => array( $wikiid ),
					'escape' => 'html',
				);
				$Tool->addOut( '<p><em>' . _( 'no-matches', $opts ) . '</em></p>' );

			} else {
				$table = '<table class="wikitable">';
				foreach ( $result['data'] as $type => $value ) {
					$table .= '<tr><th>' . $type . '</th><td>' . $value . '</td></tr>';

				}
				$table .= '</table>';

				$Tool->addOut( $table );
			}

		}

	}

	// List of permalinks for export formats
	$permalinks = '<strong>' . _( 'formats-heading', array( 'escape' => 'html' ) ) . '</strong>';
	$permalinks . '<ul>';
	foreach ( kfApiFormats() as $format ) {
		$permalinks .= '<li><a href="' . htmlspecialchars( $Tool->generatePermalink( array_merge( $params, array( 'format' => $format ) ) ) ) . '">' . "$format</a></li>\n";
	}
	$permalinks . '</ul>';
	$Tool->addOut( $permalinks );

endif;

$Tool->addOut( $I18N->getPromoBox() );


/**
 * Close up
 * -------------------------------------------------
 */
$Tool->flushMainOutput();
