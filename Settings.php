<?php

namespace Piwik\Plugins\ClientCertificates;

class Settings extends \Piwik\Plugin\Settings
{
    protected function init()
    {
        $this->govportServer = $this->createGovportServerSetting();
        
        $this->addSetting($this->govportServer);
    }

    private function createGovportServerSetting() {
	    $setting = new \Piwik\Settings\SystemSetting('govportServer', 'Govport Server');
	    $setting->type = self::TYPE_STRING;
	    $setting->description   = 'Enter the Govport server endpoint to use to authorize users';
	    $setting->defaultValue = 'http://localhost:3000/dn';
	    return $setting;
	}
}