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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\model\table;

use oat\tao\model\datatable\DatatablePayload as DataTablePayloadInterface;
use oat\tao\model\datatable\implementation\DatatableRequest;
use oat\taoOutcomeUi\model\export\ResultsExporterInterface;
use oat\taoOutcomeUi\model\ResultsService;

/**
 * ResultsPayload
 *
 * @author Gyula Szucs <gyula@taotesting.com>
 */
class ResultsPayload implements DataTablePayloadInterface
{
    private $request;

    /**
     * @var ResultsExporterInterface
     */
    private $exporter;

    public function __construct(ResultsExporterInterface $exporter)
    {
        $this->request = DatatableRequest::fromGlobals();
        $this->exporter = $exporter;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        $page = $this->request->getPage();
        $limit = $this->request->getRows();

        // offset and limit be default for getResultsByDelivery()

        $this->exporter->setStorageOptions([
            'offset' => $limit * ($page - 1),
            'limit' => $limit
        ]);

        $data = $this->exporter->getData();

        $countTotal = ResultsService::singleton()->countResultByDelivery([$this->exporter->getResourceToExport()->getUri()]);

        $payload = [
            'data' => $data,
            'page' => $page,
            'amount'  => count($data),
            'total' => ceil($countTotal / $limit)
        ];

        return $payload;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getPayload();
    }
}