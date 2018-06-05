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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA;
 */
namespace oat\taoOutcomeUi\model\search;

use oat\tao\helpers\UserHelper;
use oat\tao\model\search\index\IndexDocument;
use oat\tao\model\search\index\IndexService;
use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoResultServer\models\classes\ResultService;
use Slim\Exception\NotFoundException;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResultIndexIterator implements \Iterator
{
    use ServiceLocatorAwareTrait;

    const CACHE_SIZE = 100;

    private $resourceIterator;

    /** @var ResultServerService  */
    private $resultService;

    /**
     * Id of the current instance
     *
     * @var int
     */
    private $currentInstance = 0;

    /**
     * List of resource uris currently being iterated over
     *
     * @var array
     */
    private $instanceCache = null;

    /**
     * Indicater whenever the end of  the current cache is also the end of the current class
     *
     * @var boolean
     */
    private $endOfResource = false;

    /**
     * Whenever we already moved the pointer, used to prevent unnecessary rewinds
     *
     * @var boolean
     */
    private $unmoved = true;

    /**
     * Constructor of the iterator expecting a class or classes as argument
     *
     * @param mixed $classes array/instance of class(es) to iterate over
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function __construct($classes, ServiceLocatorInterface $serviceLocator) {
        $this->setServiceLocator($serviceLocator);
        $this->resourceIterator = new \core_kernel_classes_ResourceIterator($classes);
        /** @var ResultServerService $resultService */
        $this->resultService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);

        $this->ensureNotEmpty();
        $this->ensureValidResult();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    function rewind() {
        if (!$this->unmoved) {
            $this->resourceIterator->rewind();
            $this->ensureNotEmpty();
            $this->unmoved = true;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    function current() {
        $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($this->instanceCache[$this->currentInstance]);
        return $this->createDocument($deliveryExecution);
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    function key() {
        return $this->resourceIterator->key().'#'.$this->currentInstance;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    function next() {
        $this->unmoved = false;
        if ($this->valid()) {
            $this->currentInstance++;
            if (!isset($this->instanceCache[$this->currentInstance])) {
                // try to load next block (unless we know it's empty)
                $remainingInstances = !$this->endOfResource && $this->load($this->resourceIterator->current(), $this->currentInstance);

                // endOfClass or failed loading
                if (!$remainingInstances) {
                    $this->resourceIterator->next();
                    $this->ensureNotEmpty();
                }
            }
            $this->ensureValidResult();
        }
    }

    /**
     * While there are remaining classes there are instances to load
     *
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    function valid() {
        return $this->resourceIterator->valid();
    }

    // Helpers

    /**
     * Ensure the class iterator is pointin to a non empty class
     * Loads the first resource block to test this
     */
    protected function ensureNotEmpty() {
        $this->currentInstance = 0;
        while ($this->resourceIterator->valid() && !$this->load($this->resourceIterator->current(), 0)) {
            $this->resourceIterator->next();
        }
    }

    /**
     * Ensure the current item is valid result
     */
    protected function ensureValidResult() {
        $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($this->instanceCache[$this->currentInstance]);
       try {
           $deliveryExecution->getDelivery();
       } catch (\common_exception_NotFound $e) {
           $message = 'Skip result '. $deliveryExecution->getIdentifier(). ' with message '.$e->getMessage();
           \common_Logger::e($message);
           $this->next();
       }
    }

    /**
     * @param \core_kernel_classes_Resource $delivery
     * @param $offset
     * @return bool
     * @throws \common_exception_Error
     */
    protected function load(\core_kernel_classes_Resource $delivery, $offset) {

        $options = array(
            'recursive' => true,
            'offset' => $offset,
            'limit' => self::CACHE_SIZE
        );

        $resultsImplementation = $this->resultService->getResultStorage(null);

        $this->instanceCache = array();
        $results = $resultsImplementation->getResultByDelivery([$delivery->getUri()], $options);
        foreach($results as $result){
            $id = isset($result['deliveryResultIdentifier']) ? $result['deliveryResultIdentifier'] : null;
            if ($id) {
                $this->instanceCache[$offset] = $id;
                $offset++;
            }
        }

        $this->endOfResource = count($results) < self::CACHE_SIZE;

        return count($results) > 0;
    }

    /**
     * @param DeliveryExecution $execution
     * @return IndexDocument
     * @throws \common_Exception
     * @throws \common_exception_NotFound
     * @throws \oat\oatbox\extension\script\MissingOptionException
     */
    protected function createDocument(DeliveryExecution $execution)
    {
        /** @var ResultCustomFieldsService $customFieldService */
        $customFieldService = $this->getServiceLocator()->get(ResultCustomFieldsService::SERVICE_ID);
        $customBody = $customFieldService->getCustomFields($execution);

        $user = UserHelper::getUser($execution->getUserIdentifier());
        $userName = UserHelper::getUserName($user, true);

        $body = [
            'label' => $execution->getLabel(),
            ResultsWatcher::INDEX_DELIVERY => $execution->getDelivery()->getUri(),
            'type' => ResultService::DELIVERY_RESULT_CLASS_URI,
            ResultsWatcher::INDEX_TEST_TAKER => $user->getIdentifier(),
            ResultsWatcher::INDEX_TEST_TAKER_NAME => $userName,
            ResultsWatcher::INDEX_DELIVERY_EXECUTION => $execution->getIdentifier(),
        ];

        $body = array_merge($body, $customBody);
        $document = [
            'id' => $execution->getIdentifier(),
            'body' => $body
        ];
        /** @var IndexService $indexService */
        $indexService = $this->getServiceLocator()->get(IndexService::SERVICE_ID);
        return $indexService->createDocumentFromArray($document);
    }
}