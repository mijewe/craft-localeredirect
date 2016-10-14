<?php
namespace Craft;

class LocaleRedirectPlugin extends BasePlugin {

    function getName()
    {
         return Craft::t('Locale Redirect');
    }

    function getVersion()
    {
        return '1.0';
    }

    function getDeveloper()
    {
        return 'Michael Westwood';
    }

    function getDeveloperUrl()
    {
        return 'https://twitter.com/mijewe';
    }
}
