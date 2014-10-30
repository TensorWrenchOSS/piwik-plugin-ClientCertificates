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

}
