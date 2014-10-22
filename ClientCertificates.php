<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ClientCertificates;

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
            'Live.getAllVisitorDetails' => 'getAllVisitorDetails'
        );
    }

    // Populates visitor card with additional data
    public function getAllVisitorDetails(&$visitor, $visitorDetails) {
        $visitor['additionalData'] = array();

        $visitor['additionalData']['Name'] = $visitorDetails['first_name']. ' ' .$visitorDetails['last_name'];
        $visitor['additionalData']['Email'] = $visitorDetails['email'];
        $visitor['additionalData']['Agency'] = $visitorDetails['agency'];
    }

    // Puts new visitor data from govport into database 
    public function newVisitorInformation(&$visitorInfo, $request) {
    	$dn = $this->getUserDN();

    	$result = $this->queryGovport($dn);

	 	$username = $result->{'uid'};
	    $fullname = $result->{'fullName'};
	    $email = $result->{'email'}; 
	    $firstname = $result->{'firstName'};
	    $lastname = $result->{'lastName'};
	    $agency = $result->{'grantBy'}[0];

	    if($agency == null)
	    {
	    	$agency = $result->{'organizations'}[0];
	    	if($agency == null) {
	    		$agency = 'N/A';
	    	}
	    }

        $visitorInfo['user_dn'] = $dn;
        $visitorInfo['user_id'] = $username;
        $visitorInfo['uid'] = $username;
        $visitorInfo['first_name'] = ucwords(strtolower($firstname));
        $visitorInfo['last_name'] = ucwords(strtolower($lastname));
        $visitorInfo['email'] = $email;
        $visitorInfo['agency'] = $agency;
    }

    // Sets visitor user_id to be hash of user DN in the database
    public function getVisitorId(&$idVisitor) {
        $dn = $this->getUserDN();

        $idVisitor = hex2bin(substr( sha1( $dn ), 0, 16));
    }

    // Ensures uniqueness of user is determined only by visitor id and not system configuration
    public function getShouldMatchOneFieldOnly(&$shouldMatchOneFieldOnly) {
    	$shouldMatchOneFieldOnly = true;
    }

    private function getUserDN() {
    	return getallheaders()['SSL_CLIENT_S_DN'];
    }

    private function queryGovport($dn) {
        $settings = new Settings();
        $govportUrl = $settings->govportServer->getValue();

		if(preg_match("/^\/c=/i",$dn)){
			$dn = implode(",",array_reverse(explode("/",ltrim($dn,"/"))));
		}
	    $url = "$govportUrl/$dn";
		error_log($url);
		$url =  str_replace (" " , "%20", $url);
		$curlSession = curl_init();
		curl_setopt($curlSession, CURLOPT_URL, $url);
		curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_VERBOSE, true);
		curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curlSession, CURLOPT_SSLVERSION, 3);
		curl_setopt($curlSession, CURLOPT_CAINFO, '/etc/ssl/adv/rootCA.pem');
		curl_setopt($curlSession, CURLOPT_SSLCERT, '/etc/ssl/adv/localhost.pem');
		curl_setopt($curlSession, CURLOPT_SSLKEY, '/etc/ssl/adv/localhost.np.key');
		
		$jsonData = json_decode(curl_exec($curlSession));
		curl_close($curlSession);

		return $jsonData;
    }
}
