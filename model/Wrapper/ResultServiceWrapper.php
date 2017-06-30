<?php
/**
 * Created by PhpStorm.
 * User: christophe
 * Date: 20/06/17
 * Time: 15:11
 */

namespace oat\taoOutcomeUi\model\Wrapper;


use oat\oatbox\service\ConfigurableService;
use oat\taoOutcomeUi\model\ResultsService;

class ResultServiceWrapper extends ConfigurableService
{

    const SERVICE_ID  = 'taoOutcomeUi/resultService';

    /**
     * @var ResultsService
     */
    protected $resultService;

    public function getService() {
        if(is_null($this->resultService)) {
            $serviceClass = $this->getOption('class');
            $this->resultService = $serviceClass::singleton();
        }
        return $this->resultService;
    }

}