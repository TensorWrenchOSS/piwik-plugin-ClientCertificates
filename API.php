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
use Piwik\DataTable\Row;

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

        $dataTable->queueFilter( function(DataTable $table) {
            // Set new summed unique users column into daily unique users column so they can be displayed in the same column
            // when viewing a date range and not just a single day.
            foreach ($table->getRows() as $visitRow) {
                if($visitRow->hasColumn(\Piwik\Metrics::INDEX_SUM_DAILY_NB_USERS)) {
                    $visitRow->setColumn(\Piwik\Metrics::INDEX_NB_USERS, $visitRow->getColumn(\Piwik\Metrics::INDEX_SUM_DAILY_NB_USERS));
                }
            }
        });
        
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');

        return $dataTable;

        // throw new \Exception();
        // $data = \Piwik\Plugins\Live\API::getInstance()->getLastVisitsDetails(
        //     $idSite,
        //     $period,
        //     $date,
        //     $segment,
        //     $numLastVisitorsToFetch = 100,
        //     $minTimestamp = false,
        //     $flat = false,
        //     $doNotFetchActions = true
        // );
        // $data->applyQueuedFilters();

        // $result = $data->getEmptyClone($keepFilters = false); // we could create a new instance by using new DataTable(),
        //                                                       // but that wouldn't copy DataTable metadata, which can be
        //                                                       // useful.

        // $users = array();
        // foreach ($data->getRows() as $visitRow) {
        //     $agency = $visitRow->getColumn('agency');
        //     $userid = $visitRow->getColumn('userId');

        //     // try and get the row in the result DataTable for the browser used in this visit
        //     $resultRowForAgency = $result->getRowFromLabel($agency);

        //     // if there is no row for this browser, create it
        //     if ($resultRowForAgency === false) {
        //         $result->addRowFromSimpleArray(array(
        //             'label' => $agency,
        //             'nb_visits' => 1,
        //             'ClientCertificates_uniqueVisitors' => 1
        //         ));
        //         array_push($users, $userid);
        //     } else { // if there is a row, increment the visit count
        //         if(!in_array($userid, $users)) {
        //             array_push($users, $userid);
        //             $resultRowForAgency->setColumn('ClientCertificates_uniqueVisitors', $resultRowForAgency->getColumn('ClientCertificates_uniqueVisitors') + 1);
        //         } 
        //         $resultRowForAgency->setColumn('nb_visits', $resultRowForAgency->getColumn('nb_visits') + 1);
        //     }
        // }
       
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

        // $data = \Piwik\Plugins\Live\API::getInstance()->getLastVisitsDetails(
        //     $idSite,
        //     $period,
        //     $date,
        //     $segment,
        //     $numLastVisitorsToFetch = 100,
        //     $minTimestamp = false,
        //     $flat = false,
        //     $doNotFetchActions = true
        // );
        // $data->applyQueuedFilters();

        // $result = $data->getEmptyClone($keepFilters = false); // we could create a new instance by using new DataTable(),
        //                                                       // but that wouldn't copy DataTable metadata, which can be
        //                                                       // useful.

        // foreach ($data->getRows() as $visitRow) {
        //     $userid = $visitRow->getColumn('userId');
        //     $agency = $visitRow->getColumn('agency');
        //     $firstname = $visitRow->getColumn('first_name');
        //     $lastname = $visitRow->getColumn('last_name');

        //     // try and get the row in the result DataTable for the browser used in this visit
        //     $resultRowForUser = $result->getRowFromLabel($userid);

        //     // if there is no row for this browser, create it
        //     if ($resultRowForUser === false) {
        //         $result->addRowFromSimpleArray(array(
        //             'label' => $userid,
        //             'ClientCertificates_name' => "$firstname $lastname",
        //             'ClientCertificates_agency' => $agency,
        //             'nb_visits' => 1
        //         ));
        //     } else { // if there is a row, increment the visit count
        //         $resultRowForUser->setColumn('nb_visits', $resultRowForUser->getColumn('nb_visits') + 1);
        //     }
        // }

        // return $result;
    }

}
