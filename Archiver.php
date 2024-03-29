<?php

namespace Piwik\Plugins\ClientCertificates;

use Piwik\Metrics;
use Piwik\DataTable;
use Piwik\DataArray;

class Archiver extends \Piwik\Plugin\Archiver {
	public function aggregateDayReport() {
		$this->aggregateAgencyInformation();
		$this->aggregateUserInformation();
		$this->aggregateNewUserInformation();
    }

    private function aggregateAgencyInformation() {
		$logAggregator = $this->getLogAggregator();

		$dataTable = $logAggregator->getMetricsFromVisitByDimension('log_visit.agency')->asDataTable();

		$archiveProcessor = $this->getProcessor();
		$archiveProcessor->insertBlobRecord('ClientCertificates_GetAgencyInformation', $dataTable->getSerialized(500));
    }

    private function aggregateNewUserInformation() {
    	$logAggregator = $this->getLogAggregator();

    	$dataTable = $logAggregator->getMetricsFromVisitByDimension('log_visit.visitor_returning')->asDataTable();

    	$archiveProcessor = $this->getProcessor();
    	$archiveProcessor->insertBlobRecord('ClientCertificates_GetNewUsers', $dataTable->getSerialized(500));
    }

    private function aggregateUserInformation() {
    	$logAggregator = $this->getLogAggregator();

		$query = $logAggregator->queryVisitsByDimension(
			$dimensions = array('log_visit.user_id'),
			$where = '',
			$additionalSelects = array(
				'log_visit.first_name as "'.ClientCertificates::DATA_FIRST_NAME.'"',
				'log_visit.last_name as "'.ClientCertificates::DATA_LAST_NAME.'"',
				'log_visit.agency as "'.ClientCertificates::DATA_AGENCY.'"'
			)
		);

		$dataRows = array();
		while ($row = $query->fetch()) {
			array_push($dataRows, $row);
        }
		$dataArray = new DataArray($dataRows);
		$dataTable = $dataArray->asDataTable();

		foreach ($dataTable->getRows() as $row) {
			$label = $row->getColumn('log_visit.user_id');
			$row->setColumn('label',$label);
		}

		$archiveProcessor = $this->getProcessor();
		$archiveProcessor->insertBlobRecord('ClientCertificates_GetUserInformation', $dataTable->getSerialized(500));
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function aggregateMultipleReports() {
		$columnsAggregationOperation = array('log_visit.user_id' => 'max',ClientCertificates::DATA_FIRST_NAME => 'max', ClientCertificates::DATA_LAST_NAME => 'max', ClientCertificates::DATA_AGENCY => 'max');
    	
    	$archiveProcessor = $this->getProcessor();
    	$archiveProcessor->aggregateDataTableRecords('ClientCertificates_GetAgencyInformation', 500);
    	$archiveProcessor->aggregateDataTableRecords('ClientCertificates_GetNewUsers', 500);
    	$archiveProcessor->aggregateDataTableRecords('ClientCertificates_GetUserInformation', 500, null, null, $columnsAggregationOperation);
    }
}