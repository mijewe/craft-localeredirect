<?php
namespace Craft;

global $currentLocale;
global $browserLocale;
class LocaleRedirect_GoService extends BaseApplicationComponent {


	/*
	If there's a cookie set already,
		do nothing. We've already chosen a locale.
	If not,
		If the current locale is different from the default locale,
			do nothing. We've specifically chosen to go to /es so don't redirect me.
		If not,
			redirect to the browser locale if different from the current locale.
			set a cookie.
	*/

	public function go($cl, $currentEntryId) {
		global $currentLocale;
		global $browserLocale;

		$currentLocale = $cl;
		$browserLocale = $this->getBrowserLocale(true);

		// If the user has used the language switcher, we don't want to force a
		// redirect or anything, so just set a cookie for their chosen locale
		// and do nothing.
		$switchLocale = $this->getQueryParameter('switchlocale');
		if ($switchLocale) {
			$this->setLocaleCookie($currentLocale);
			$this->doNothing('User used the locale switcher, so they probably know what they want. Do nothing.');
			return;
		}

		// If a locale cookie has already been set, then the user has already
		// chosen the langaugethey want. So do nothing.
		$localeCookie = $this->getLocaleCookie();
		if (!is_null($localeCookie)) {
			$this->doNothing('A language cookie has already been set, let\'s leave it.');
			return;
		} else {

			// If the user is not on the the default locale, then we can assume
			// they've deliberately chosen a locale, so set a cookie then do
			// nothing.
			$defaultLocale = $this->getDefaultLocale();
			if ($currentLocale != $defaultLocale) {
				$this->setLocaleCookie($currentLocale);
				$this->doNothing('The user has specifically chosen a locale via the URL, so that\'s probably the language they were after anyway. Do nothing.');
				return;
			} else {

				// Since there's no cookie set, and no chosen language already,
				// let's redirect the user to the locale of their browser.
				if ($browserLocale != $currentLocale) {
					$this->setLocaleCookie($browserLocale);
					$this->redirectToLocalEntry($currentEntryId, $browserLocale);
				} else {
					$this->setLocaleCookie($currentLocale);
					$this->doNothing('We interpretted the most appropriate language based on the browser settings, and that\'s the locale we\'re currently on, so do nothing.');
					return;
				}

			}

		}

	}

	/*
	Grabs the value of a query string parameter based on the $key, or false.
	*/
	private function getQueryParameter($key) {
		return isset($_GET[$key]) ? $_GET[$key] : false;
	}

	/*
	Returns the value of the cookie 'locale', or null.
	*/
	private function getLocaleCookie() {
		return isset($_COOKIE['locale']) ? $_COOKIE['locale'] : null;
	}

	/*
	Sets the locale cookie with the value $locale.
	*/
	private function setLocaleCookie($locale) {
		$this->setCookie('locale', $locale, time() + (60 * 60 * 24 * 365));
	}

	/*
	Does nothing. Used for debugging.
	*/
	private function doNothing($str) {
		// echo $str;
		return;
	}

	/*
	Returns the default locale of the Craft install.
	*/
	private function getDefaultLocale() {
		return craft()->i18n->getPrimarySiteLocale();
	}

	/*
	Returns the language of the browser, based on the http_accept_language header.
	*/
	private function getBrowserLocale($full = false) {
 		$langs = craft()->request->getBrowserLanguages();
		return ($full ? $langs[0] : substr($langs[0], 0, 2));
	}

	/*
	Returns the URL of the entry with id $id and locale $locale.
	*/
	private function getEntryUrlForLocale($locale, $id) {
		$criteria = craft()->elements->getCriteria(ElementType::Entry);
		$criteria->id = $id;
		$criteria->locale = $locale;
		$entry = $criteria->first();

		if ($entry) {
			return $entry->getUrl();
		} else {
			return null;
		}
	}

	/*
	Redirects to the entry with id $id and locale $desiredLocale. If there's no
	locale that matches $desiredLocale, it will use the first one with the same
	language code (eg 'es' will redirect to 'es_cl' if there is no 'es' locale)
	*/
	private function redirectToLocalEntry($id, $desiredLocale) {
		global $currentLocale;

		$locales = craft()->i18n->siteLocaleIds;

		$destinationLocale = null;
		foreach ($locales as $locale) {
			if (substr($locale, 0, 2) == substr($desiredLocale, 0, 2)) {
				$destinationLocale = $locale;
			}
		}

		if (!is_null($destinationLocale)) {

			if ($destinationLocale != $currentLocale) {
				$url = $this->getEntryUrlForLocale($destinationLocale, $id);

				if ($url) {
					craft()->request->redirect($url, true, 302);
				}
			} else {
				$this->doNothing('we were just about to redirect, but we\'re already on this page');
				return;
			}

		} else {
			$this->doNothing('attempted to redirect, but couldn\'t find an appropriate destination locale.');
			return;
		}

	}

	/*
	Sets a cookie.
	https://github.com/vigetlabs/craft-localeredirector
	*/
	private function setCookie($name = "", $value = "", $expire = 0, $path = "/", $domain = "", $secure = false, $httponly = false) {
		setcookie($name, $value, (int) $expire, $path, $domain, $secure, $httponly);
		$_COOKIE[$name] = $value;
	}

}
