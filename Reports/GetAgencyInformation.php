<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ClientCertificates\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\ClientCertificates\Columns\Agency;

use Piwik\View;

/**
 * This class defines a new report.
 *
 * See {@link http://developer.piwik.org/api-reference/Piwik/Plugin/Report} for more information.
 */
class GetAgencyInformation extends Base
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('ClientCertificates_GetAgencyInformation');
        $this->dimension     = new Agency();
        $this->documentation = Piwik::translate('ClientCertificatesDocumentation');

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 32;

        // By default standard metrics are defined but you can customize them by defining an array of metric names
        $this->metrics       = array('nb_visits','nb_users');

        // Uncomment the next line if your report does not contain any processed metrics, otherwise default
        // processed metrics will be assigned
        // $this->processedMetrics = array();

        // Uncomment the next line if your report defines goal metrics
        // $this->hasGoalMetrics = true;

        // Uncomment the next line if your report should be able to load subtables. You can define any action here
        // $this->actionToLoadSubTables = 'getUserInformation';

        // Uncomment the next line if your report always returns a constant count of rows, for instance always
        // 24 rows for 1-24hours
        // $this->constantRowsCount = true;

        // If a menu title is specified, the report will be displayed in the menu
        $this->menuTitle    = 'Agencies';

        // If a widget title is specified, the report will be displayed in the list of widgets and the report can be
        // exported as a widget
        $this->widgetTitle  = $this->menuTitle;
    }

    /**
     * Here you can configure how your report should be displayed. For instance whether your report supports a search
     * etc. You can also change the default request config. For instance change how many rows are displayed by default.
     *
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {

        $view->config->show_search = false;
        // $view->requestConfig->filter_sort_column = 'nb_visits';
        // $view->requestConfig->filter_limit = 10';
        $view->config->addTranslation('label','Agency');
        $view->config->addTranslation('nb_users','Unique Visitors');

        $view->config->columns_to_display = array_merge(array('label'), $this->metrics);

        // $view->config->subtable_controller_action = 'getUserInformation';
        // $view->config->pivot_by_dimension = (new \Piwik\Plugins\ClientCertificates\Columns\Agency())->getId();
        // $view->config->show_pivot_by_subtable = true;
    }

    /**
     * Here you can define related reports that will be shown below the reports. Just return an array of related
     * report instances if there are any.
     *
     * @return \Piwik\Plugin\Report[]
     */
    public function getRelatedReports()
    {
         return array(); // eg return array(new XyzReport());
    }

    /**
     * A report is usually completely automatically rendered for you but you can render the report completely
     * customized if you wish. Just overwrite the method and make sure to return a string containing the content of the
     * report. Don't forget to create the defined twig template within the templates folder of your plugin in order to
     * make it work. Usually you should NOT have to overwrite this render method.
     *
     * @return string
    public function render()
    {
        $view = new View('@ClientCertificates/getClientCertificates');
        $view->myData = array();

        return $view->render();
    }
    */

    /**
     * By default your report is available to all users having at least view access. If you do not want this, you can
     * limit the audience by overwriting this method.
     *
     * @return bool
    public function isEnabled()
    {
        return Piwik::hasUserSuperUserAccess()
    }
     */
}
