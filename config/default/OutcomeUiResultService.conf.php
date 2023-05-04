<?php

/**
 * Default config header created during install
 */

use oat\taoOutcomeUi\model\ResultsService;

return new oat\taoOutcomeUi\model\ResultsService(array(
    ResultsService::OPTION_ALLOW_SQL_EXPORT => false,
    ResultsService::OPTION_ALLOW_TRACE_VARIABLES_EXPORT => false,
));
