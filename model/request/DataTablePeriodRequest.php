<?php

namespace oat\taoOutcomeUi\model\request;
use oat\tao\model\datatable\implementation\DatatableRequest;
use oat\taoOutcomeUi\model\ResultsService;
use Psr\Http\Message\ServerRequestInterface;

class DataTablePeriodRequest extends DatatableRequest
{
    const PERIOD_FILTER =[ResultsService::PARAM_START_FROM, ResultsService::PARAM_START_TO, ResultsService::PARAM_END_FROM,  ResultsService::PARAM_END_TO];

    private $requestParams;

    /**
     * DatatableRequest constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $bodyParams = $request->getParsedBody();
        if ($bodyParams === null) {
            $bodyParams = [];
        }
        $queryParams = $request->getQueryParams();
        $this->requestParams = array_merge($bodyParams, $queryParams);
    }
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