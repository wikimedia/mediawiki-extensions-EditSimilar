<?php
/**
 * Extension that suggests editing of similar articles upon saving an article
 *
 * @file
 * @ingroup Extensions
 * @author Bartek Łapiński <bartek@wikia-inc.com>
 * @author Łukasz Garczewski (TOR) <tor@wikia-inc.com>
 * @copyright Copyright © 2008, Wikia Inc.
 * @license GPL-2.0-or-later
 * @link https://www.mediawiki.org/wiki/Extension:EditSimilar Documentation
 */

use MediaWiki\MediaWikiServices;

class EditSimilarHooks {

	/**
	 * Check if we had the extension enabled at all and if the current page is in a
	 * content namespace.
	 *
	 * @param WikiPage $wikiPage The page that was edited
	 * @param MediaWiki\User\UserIdentity $user The user who performed the edit
	 */
	public static function onPageSaveComplete( WikiPage $wikiPage, $user ) {
		global $wgContentNamespaces;

		$namespace = $wikiPage->getTitle()->getNamespace();
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();
		if (
			( $userOptionsLookup->getOption( $user, 'edit-similar', 1 ) == 1 ) &&
			( in_array( $namespace, $wgContentNamespaces ) )
		) {
			$_SESSION['ES_saved'] = 'yes';
		}
	}

	/**
	 * Show a message, depending on settings and the relevancy of the results.
	 *
	 * @param OutputPage &$out
	 * @param string &$text [unused]
	 */
	public static function onOutputPageBeforeHTML( OutputPage &$out, &$text ) {
		global $wgEditSimilarAlwaysShowThanks;

		$user = $out->getUser();
		$userOptionsLookup = MediaWikiServices::getInstance()->getUserOptionsLookup();

		if (
			!empty( $_SESSION['ES_saved'] ) &&
			( $userOptionsLookup->getOption( $user, 'edit-similar', 1 ) == 1 ) &&
			$out->isArticle()
		) {
			if ( EditSimilar::checkCounter() ) {
				$title = $out->getTitle();
				// here we'll populate the similar articles and links
				$instance = new EditSimilar( $title->getArticleID() );
				$similarities = $instance->getSimilarArticles();
				$messageText = '';

				if ( !empty( $similarities ) ) {
					if ( $instance->mSimilarArticles ) {
						$messageText = $out->msg(
							'editsimilar-thanks',
							$out->getLanguage()->listToText( $similarities ),
							count( $similarities )
						)->parse();
					} else {
						// the articles we found were rather just articles needing attention
						$messageText = $out->msg(
							'editsimilar-thanks-notsimilar',
							$out->getLanguage()->listToText( $similarities ),
							count( $similarities )
						)->parse();
					}
				} else {
					if ( $user->isRegistered() && !empty( $wgEditSimilarAlwaysShowThanks ) ) {
						$messageText = $out->msg(
							'editsimilar-thankyou',
							$user->getName()
						)->parse();
					}
				}

				if ( $messageText != '' ) {
					EditSimilar::showMessage( $out, $messageText );
				}
			}

			// display that only once
			$_SESSION['ES_saved'] = '';
		}
	}

	/**
	 * Adds the new toggle to Special:Preferences for enabling EditSimilar
	 * extension on a per-user basis.
	 *
	 * @param User $user
	 * @param mixed[] &$preferences
	 */
	public static function onGetPreferences( $user, array &$preferences ) {
		$preferences['edit-similar'] = [
			'type' => 'toggle',
			'section' => 'editing',
			'label-message' => 'tog-edit-similar',
		];
	}

}
