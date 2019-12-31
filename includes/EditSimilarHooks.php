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

class EditSimilarHooks {

	/**
	 * Check if we had the extension enabled at all and if the current page is in a
	 * content namespace.
	 *
	 * @param WikiPage $wikiPage The page that was edited
	 * @param User $user The user who performed the edit
	 * @param Content $content [unused]
	 * @param string $summary Edit summary [unused]
	 * @param bool $isMinor Is the edit marked as a minor edit? [unused]
	 * @param bool $isWatch [unused]
	 * @param int $section [unused]
	 * @param $flags [unused]
	 * @param Revision $revision [unused]
	 * @param Status $status [unused]
	 * @param int|bool $baseRevId [unused]
	 */
	public static function onPageContentSaveComplete(
		WikiPage $wikiPage, $user, $content, $summary, $isMinor,
			$isWatch, $section, $flags, $revision, $status, $baseRevId
		) {
		global $wgContentNamespaces;

		$namespace = $wikiPage->getTitle()->getNamespace();
		if (
			( $user->getOption( 'edit-similar', 1 ) == 1 ) &&
			( in_array( $namespace, $wgContentNamespaces ) )
		) {
			$_SESSION['ES_saved'] = 'yes';
		}
	}

	/**
	 * Show a message, depending on settings and the relevancy of the results.
	 *
	 * @param OutputPage $out
	 * @param string $text [unused]
	 */
	public static function onOutputPageBeforeHTML( OutputPage &$out, &$text ) {
		global $wgEditSimilarAlwaysShowThanks;
		$user = $out->getUser();

		if (
			!empty( $_SESSION['ES_saved'] ) &&
			( $user->getOption( 'edit-similar', 1 ) == 1 ) &&
			$out->isArticle()
		) {
			if ( EditSimilar::checkCounter() ) {
				$message_text = '';
				$title = $out->getTitle();
				$articleTitle = $title->getText();
				// here we'll populate the similar articles and links
				$instance = new EditSimilar( $title->getArticleID(), 'category' );
				$similarities = $instance->getSimilarArticles();
				$messageText = '';

				if ( !empty( $similarities ) ) {
					global $wgLang;

					if ( $instance->mSimilarArticles ) {
						$messageText = wfMessage(
							'editsimilar-thanks',
							$wgLang->listToText( $similarities ),
							count( $similarities )
						)->parse();
					} else {
						// the articles we found were rather just articles needing attention
						$messageText = wfMessage(
							'editsimilar-thanks-notsimilar',
							$wgLang->listToText( $similarities ),
							count( $similarities )
						)->parse();
					}
				} else {
					if ( $user->isLoggedIn() && !empty( $wgEditSimilarAlwaysShowThanks ) ) {
						$messageText = wfMessage( 'editsimilar-thankyou', $user->getName() )->parse();
					}
				}

				if ( $messageText != '' ) {
					EditSimilar::showMessage( $messageText, $articleTitle );
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
