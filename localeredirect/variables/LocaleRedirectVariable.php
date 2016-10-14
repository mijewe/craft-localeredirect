<?php
namespace Craft;

class LocaleRedirectVariable {

	public function maybeSwitchLocale($currentLocale, $currentEntryId) {
		return craft()->localeRedirect_go->go($currentLocale, $currentEntryId);
	}

}
