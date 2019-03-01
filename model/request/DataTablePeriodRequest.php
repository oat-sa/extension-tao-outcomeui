<?php

namespace oat\taoOutcomeUi\model\request;
use oat\tao\model\datatable\implementation\DatatableRequest;

class DataTablePeriodRequest extends DatatableRequest
{
    const PERIOD_FILTER =['startfrom', 'startto', 'endfrom', 'endto'];

    private $requestParams;

    /**
     * Get periodFilter
     * @return array
     */
    public function periodFilter()
    {
        $period = [];
        foreach (self::PERIOD_FILTER as $value){
            if(isset($this->requestParams[$value])){
                $period[$value] = strtotime($this->requestParams[$value]);
            }
        }
        return $period;
    }
}