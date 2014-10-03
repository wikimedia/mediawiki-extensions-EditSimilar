<?php
/**
 * Extension that suggests editing of similar articles upon saving an article
 *
 * @file
 * @ingroup Extensions
 * @author Bartek Łapiński <bartek@wikia-inc.com>
 * @author Łukasz Garczewski (TOR) <tor@wikia-inc.com>
 * @copyright Copyright © 2008, Wikia Inc.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link https://www.mediawiki.org/wiki/Extension:EditSimilar Documentation
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'EditSimilar',
	'version' => '1.24',
	'author' => array( 'Bartek Łapiński', 'Łukasz Garczewski' ),
	'url' => 'https://www.mediawiki.org/wiki/Extension:EditSimilar',
	'descriptionmsg' => 'editsimilar-desc',
);

// Internationalization file & the new class to be autoloaded
$wgMessagesDirs['EditSimilar'] = __DIR__ . '/i18n';
$wgAutoloadClasses['EditSimilar'] = __DIR__ . '/EditSimilar.class.php';
$wgAutoloadClasses['EditSimilarHooks'] = __DIR__ . '/EditSimilar.hooks.php';

// ResourceLoader support for MW 1.17+
$wgResourceModules['ext.editSimilar'] = array(
	'styles' => 'EditSimilar.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'EditSimilar',
	'position' => 'top'
);

# Configuration
// maximum number of results to choose from
$wgEditSimilarMaxResultsPool = 50;

// maximum number of results to display in text
$wgEditSimilarMaxResultsToDisplay = 3;

// show message per specified number of edits
$wgEditSimilarCounterValue = 1;
# End configuration

// Hooked functions
$wgHooks['PageContentSaveComplete'][] = 'EditSimilarHooks::onPageContentSaveComplete';
$wgHooks['OutputPageBeforeHTML'][] = 'EditSimilarHooks::onOutputPageBeforeHTML';
$wgHooks['GetPreferences'][] = 'EditSimilarHooks::onGetPreferences';
