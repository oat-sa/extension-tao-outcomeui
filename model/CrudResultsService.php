<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA
 * 
 */

namespace oat\taoOutcomeUi\model;

use \common_exception_NoImplementation;
use \core_kernel_classes_Resource;
use \tao_models_classes_CrudService;

/**
 * Crud services implements basic CRUD services, orginally intended for 
 * REST controllers/ HTTP exception handlers.
 * Consequently the signatures and behaviors is closer to REST and throwing HTTP like exceptions
 * 
 * @author Patrick Plichart, patrick@taotesting.com
 */
class CrudResultsService extends tao_models_classes_CrudService
{

    /**
     * (non-PHPdoc)
     * 
     * @see tao_models_classes_CrudService::getClassService()
     */
    protected function getClassService()
    {
        return ResultsService::singleton();
    }

    /**
     * (non-PHPdoc)
     * 
     * @see tao_models_classes_CrudService::get()
     */
    public function get($uri)
    {
        $returnData = array();
        foreach ($this->getClassService()->getVariables(new core_kernel_classes_Resource($uri), null, false) as $itemVariable => $variables) {
            
            foreach ($variables as $key => $variable) {
                $returnData[$itemVariable][] = $this->getClassService()->getVariableData($variable);
            }
        }
        
        return $returnData;
    }

    /**
     * (non-PHPdoc)
     * 
     * @see tao_models_classes_CrudService::delete()
     */
    public function delete($resource)
    {
        throw new common_exception_NoImplementation();
    }

    /**
     * (non-PHPdoc)
     * 
     * @see tao_models_classes_CrudService::deleteAll()
     */
    public function deleteAll()
    {
        throw new common_exception_NoImplementation();
    }

    /**
     * (non-PHPdoc)
     * 
     * @see tao_models_classes_CrudService::update()
     */
    public function update($uri = null, $propertiesValues = array())
    {
        throw new common_exception_NoImplementation();
    }
}

?>
