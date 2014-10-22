<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ClientCertificates\Reports;

use Piwik\Plugin\Report;
use Piwik\Plugins\ClientCertificates\API;

abstract class Base extends Report
{
    protected function init()
    {
        $this->category = 'User Information';
        $this->processedMetrics = false;
    }
}
