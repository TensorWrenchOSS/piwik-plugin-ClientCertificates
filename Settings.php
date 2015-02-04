<?php

namespace Piwik\Plugins\ClientCertificates;

use Piwik\Settings\SystemSetting;

class Settings extends \Piwik\Plugin\Settings
{

    protected function init()
    {
        $this->govportServer = $this->createTextSetting('govportServer','Govport Server', 'Enter the Govport server URL with host and port', 'http://localhost:3000');
        $this->govportUserPath = $this->createTextSetting('govportUserPath', 'Govport User Path','Enter the root path to use for Govport user calls','/dn');
        $this->govportGroupPath = $this->createTextSetting('govportGroupPath', 'Govport Group Path','Enter the root path to use for Govport group calls','/groups');
        $this->serverCert = $this->createTextSetting('serverCert', 'Server Certificate','Enter path to server certificate','/etc/ssl/adv/localhost.pem');
        $this->serverKey = $this->createTextSetting('serverKey','Server Key','Enter path to server key file','/etc/ssl/adv/localhost.np.key');
        $this->serverCA = $this->createTextSetting('serverCA', 'Server Certificate Authority','Enter path to server certificate authority file','/etc/ssl/adv/rootCA.pem');
        
        $this->addSetting($this->govportServer);
        $this->addSetting($this->govportUserPath);
        $this->addSetting($this->govportGroupPath);
        $this->addSetting($this->serverCert);
        $this->addSetting($this->serverKey);
        $this->addSetting($this->serverCA);
    }

    private function createTextSetting($id, $name, $description, $default) {
    	$setting = new SystemSetting($id, $name);
	    $setting->type = self::TYPE_STRING;
	    $setting->description   = $description;
	    $setting->defaultValue = $default;
        $setting->readableByCurrentUser = true;

	    return $setting;
    }
}