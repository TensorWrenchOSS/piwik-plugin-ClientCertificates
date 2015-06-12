<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ClientCertificates;

use Piwik\Plugins\ClientCertificates\API;
use Piwik\Container\StaticContainer;

/**
 */
class ClientCertificates extends \Piwik\Plugin {

    const DATA_FIRST_NAME = 50;
    const DATA_LAST_NAME = 51;
    const DATA_AGENCY = 52;

	public function getListHooksRegistered() {
        return array(
            'Tracker.newVisitorInformation' => 'newVisitorInformation',
            'Tracker.getVisitorId' => 'getVisitorId',
            'Tracker.getShouldMatchOneFieldOnly' => 'getShouldMatchOneFieldOnly',
            'Live.getAllVisitorDetails' => 'getAllVisitorDetails',
            'Dashboard.changeDefaultDashboardLayout' => 'changeDefaultDashboardLayout'
        );
    }

    public function changeDefaultDashboardLayout(&$defaultLayout) {
        $defaultLayout = '[
            [
                {"uniqueId":"widgetVisitsSummarygetEvolutionGraphcolumnsArray","parameters":{"module":"VisitsSummary","action":"getEvolutionGraph","columns":"nb_visits","widget":1},"isHidden":false},
                {"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget","widget":1},"isHidden":false},
                {"uniqueId":"widgetVisitorInterestgetNumberOfVisitsPerVisitDuration","parameters":{"module":"VisitorInterest","action":"getNumberOfVisitsPerVisitDuration","widget":1},"isHidden":false}
            ],[
                {"uniqueId":"widgetEventsgetCategorysecondaryDimensioneventAction","parameters":{"module":"Events","action":"getCategory","secondaryDimension":"eventAction","widget":1},"isHidden":false},
                {"uniqueId":"widgetClientCertificatesgetAgencyInformation","parameters":{"module":"ClientCertificates","action":"getAgencyInformation","widget":1},"isHidden":false},
                {"uniqueId":"widgetClientCertificatesgetUserInformation","parameters":{"module":"ClientCertificates","action":"getUserInformation","widget":1},"isHidden":false},
                {"uniqueId":"widgetClientCertificatesgetNewUsers","parameters":{"module":"ClientCertificates","action":"getNewUsers","widget":1},"isHidden":false}
            ],[
                {"uniqueId":"widgetActionsgetSiteSearchKeywords","parameters":{"module":"Actions","action":"getSiteSearchKeywords","widget":1},"isHidden":false},
                {"uniqueId":"widgetActionsgetSiteSearchNoResultKeywords","parameters":{"module":"Actions","action":"getSiteSearchNoResultKeywords","widget":1},"isHidden":false},
                {"uniqueId":"widgetUserSettingsgetBrowser","parameters":{"module":"UserSettings","action":"getBrowser","widget":1},"isHidden":false},
                {"uniqueId":"widgetVisitTimegetVisitInformationPerServerTime","parameters":{"module":"VisitTime","action":"getVisitInformationPerServerTime","widget":1},"isHidden":false}
            ]
        ]';
    }

    // Populates visitor card with additional data
    public function getAllVisitorDetails(&$visitor, $visitorDetails) {
        $visitor['additionalData'] = array();

        $visitor['additionalData']['Name'] = $visitorDetails['first_name']. ' ' .$visitorDetails['last_name'];
        $visitor['additionalData']['Email'] = $visitorDetails['email'];
        $visitor['additionalData']['Agency'] = $visitorDetails['agency'];

        // Populates auto complete for columns when selecting segments
        $visitor['agency'] = $visitorDetails['agency'];
        $visitor['email'] = $visitorDetails['email'];
        $visitor['first_name'] = $visitorDetails['first_name'];
        $visitor['last_name'] = $visitorDetails['last_name'];
        $visitor['uid'] = $visitorDetails['uid'];
    }

    // Puts new visitor data from govport into database 
    public function newVisitorInformation(&$visitorInfo, $request) {
        $logger = StaticContainer::get('Psr\Log\LoggerInterface');

        $clientCertificateAPI = API::getInstance();
    	$dn = $clientCertificateAPI->getUserDN();

    	$result = $clientCertificateAPI->queryGovport($dn);

	 	$username = $this->getProperty($result, 'uid');
	    $fullname = $this->getProperty($result, 'fullName');
	    $email = $this->getProperty($result,'email'); 
	    $firstname = $this->getProperty($result,'firstName');
	    $lastname = $this->getProperty($result,'lastName');
	   
        $agency = null;
        if(property_exists($result, 'grantBy')) {
            $agency = $result->{'grantBy'}[0];
        }

	    if($agency == null)
	    {
	    	$agency = $result->{'organizations'}[0];
	    	if($agency == null) {
	    		$agency = 'N/A';
	    	}
	    }

        $logger->info("ClientCert Tracker: $username - $fullname - $email - $firstname - $lastname - $agency");

        $visitorInfo['user_dn'] = $dn;
        $visitorInfo['user_id'] = $username;
        $visitorInfo['uid'] = $username;
        $visitorInfo['first_name'] = ucwords(strtolower($firstname));
        $visitorInfo['last_name'] = ucwords(strtolower($lastname));
        $visitorInfo['email'] = $email;
        $visitorInfo['agency'] = strtoupper($agency);
    }

    // Sets visitor user_id to be hash of user DN in the database
    public function getVisitorId(&$idVisitor) {
        $dn = API::getInstance()->getUserDN();

        $idVisitor = $this->hextobin(substr( sha1( $dn ), 0, 16));
    }

    // Ensures uniqueness of user is determined only by visitor id and not system configuration
    public function getShouldMatchOneFieldOnly(&$shouldMatchOneFieldOnly) {
    	$shouldMatchOneFieldOnly = true;
    }

    private function getProperty($data, $property) {
        if(property_exists($data, $property)) {
            return $data->{$property};
        } else {
            return null;
        }
    }

    private function hextobin($hexstr) { 
        $n = strlen($hexstr); 
        $sbin="";   
        $i=0; 
        while($i<$n) 
        {       
            $a =substr($hexstr,$i,2);           
            $c = pack("H*",$a); 
            if ($i==0){$sbin=$c;} 
            else {$sbin.=$c;} 
            $i+=2; 
        } 
        return $sbin; 
    }  
}
