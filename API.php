<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ClientCertificates;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Segment;
use Piwik\DataTable\Row;
use Piwik\Plugins\VisitsSummary\API as VisitsSummaryAPI;

/**
 * API for plugin ClientCertificates
 *
 * @method static \Piwik\Plugins\ClientCertificates\API getInstance()
 */
class API extends \Piwik\Plugin\API
{

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getAgencyInformation($idSite, $period, $date, $segment = false)
    {
        $dataTable = Archive::getDataTableFromArchive('ClientCertificates_GetAgencyInformation', $idSite, $period, $date, $segment, false);

        $dataTable->queueFilter( function(DataTable $table) use ($idSite, $period, $date, $segment) {
            // Set new summed unique users column into daily unique users column so they can be displayed in the same column
            // when viewing a date range and not just a single day.
            $visitsSummary = VisitsSummaryAPI::getInstance();

            foreach ($table->getRows() as $visitRow) {
                $agency = $visitRow->getColumn('label');
                $segmentString = 'agency=='.$agency;
                if($segment) {
                    $segmentString .= ';'.$segment;
                }
                $newSegment = new Segment($segmentString, $idSite);

                $data = $visitsSummary->getUniqueVisitors($idSite, $period, $date, $newSegment);
                $data->queueFilter( function(DataTable $uniqueTable) use ($visitRow, $agency) {
                    $uniqueVisitors = $uniqueTable->getRows()[0]->getColumn('nb_uniq_visitors');

                    $visitRow->setColumn(\Piwik\Metrics::INDEX_NB_USERS, $uniqueVisitors);
                });
                $data->applyQueuedFilters();
            }
        });
        
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');


        return $dataTable;
    }

    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getUserInformation($idSite, $period, $date, $segment = false)
    {
        $dataTable = Archive::getDataTableFromArchive('ClientCertificates_GetUserInformation', $idSite, $period, $date, $segment, false);

        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->queueFilter(function(DataTable $table) {
            foreach ($table->getRows() as $visitRow) {
                $firstname = $visitRow->getColumn(ClientCertificates::DATA_FIRST_NAME);
                $lastname = $visitRow->getColumn(ClientCertificates::DATA_LAST_NAME);
                $agency = $visitRow->getColumn(ClientCertificates::DATA_AGENCY);


                $visitRow->setColumn('label', "$firstname $lastname");
                $visitRow->setColumn('ClientCertificates_agency', $agency);
            }
        });

        return $dataTable;
    }


    /**
     * Another example method that returns a data table.
     * @param int    $idSite
     * @param string $period
     * @param string $date
     * @param bool|string $segment
     * @return DataTable
     */
    public function getNewUsers($idSite, $period, $date, $segment = false)
    {
        $dataTable = Archive::getDataTableFromArchive('ClientCertificates_GetNewUsers', $idSite, $period, $date, $segment, false);
        
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        $dataTable->queueFilter(function(DataTable $table) {
            foreach ($table->getRows() as $visitRow) {
                $visitor_returning = $visitRow->getColumn('label');
                if($visitor_returning) {
                    $visitRow->setColumn('label','Returning Users');
                } else {
                    $visitRow->setColumn('label','New Users');
                }
            }

            $rowId = $table->getRowIdFromLabel('Returning Users');
            if($rowId) {
                $table->deleteRow($rowId);
            }
        });

        return $dataTable;
    }


    public function getUserDN() {
        if(array_key_exists('SSL_CLIENT_S_DN', $_SERVER)) {
            return $_SERVER['SSL_CLIENT_S_DN'];
        } else if(array_key_exists('HTTP_SSL_CLIENT_S_DN', $_SERVER)) {
            return $_SERVER['HTTP_SSL_CLIENT_S_DN'];
        } else {
            return null;
        }
    }

    public function queryGovport($dn) {
        $settings = new \Piwik\Plugins\ClientCertificates\Settings();

        $govportUrl = $settings->govportServer->getValue();
        $govportUserPath = $settings->govportUserPath->getValue();

        $dn = $this->checkDnEncoding($dn);

        $url = "$govportUrl$govportUserPath/$dn";
        $url =  str_replace (" " , "%20", $url);

        return $this->getJSON($url);
    }

    public function queryGovportGroup($dn, $group, $project) {
        $settings = new Settings();

        $govportUrl = $settings->govportServer->getValue();
        $govportGroupPath = $settings->govportGroupPath->getValue();

        $dn = $this->checkDnEncoding($dn);

        $url = "$govportUrl$govportGroupPath/$project!$group/members/$dn";
        $url =  str_replace (" " , "%20", $url);

        return $this->getJSON($url);
    }

    private function getJSON($url) {
        \Piwik\Log::debug("Connecting to url [".$url."]");
        $settings = new Settings();
        $serverCert = $settings->serverCert->getValue();
        $serverKey = $settings->serverKey->getValue();
        $serverCA = $settings->serverCA->getValue();

        $curlSession = curl_init();
        curl_setopt($curlSession, CURLOPT_URL, $url);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_VERBOSE, true);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curlSession, CURLOPT_CAINFO, $serverCA);
        curl_setopt($curlSession, CURLOPT_SSLCERT, $serverCert);
        curl_setopt($curlSession, CURLOPT_SSLKEY, $serverKey);
        
        $data = curl_exec($curlSession);
        $jsonData = json_decode(trim($data,"/*"));
        curl_close($curlSession);

        return $jsonData;
    }

    private function checkDnEncoding($dn) {
        if(preg_match("/^\/c=/i",$dn)){
            return implode(",",array_reverse(explode("/",ltrim($dn,"/"))));
        } else {
            return $dn;
        }
    }

}
