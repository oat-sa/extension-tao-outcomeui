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
 * Copyright (c) 2017 Open Assessment Technologies S.A.
 *
 */
namespace oat\taoOutcomeUi\model\export;

use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\task\implementation\SyncQueue;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\taoOutcomeUi\scripts\task\ExportDeliveryResultsTask;
use \common_Exception;
use \core_kernel_classes_Resource;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class ResultExportService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const DELIVERY_EXPORT_QUEUE_CONTEXT = 'taoOutcomeUi/results-export-by-delivery';

    /**
     * Use the sync queue to create a task executed immediately
     * The created task contains a report with export file
     *
     * @param core_kernel_classes_Resource $delivery
     * @return \oat\oatbox\filesystem\File
     * @throws common_Exception
     */
    public function exportDeliveryResults(core_kernel_classes_Resource $delivery)
    {
        if (!$this->isSynchronousExport()) {
            throw new common_Exception('Unable to get an export file, taskqueue is not synchronous.');
        }

        if (!$delivery->exists()) {
            throw new common_Exception('The delivery to export does not exist.');
        }

        /** @var Task $task */
        $task = $this->createExportTask($delivery);
        $taskErrorReports = $task->getReport()->getErrors();
        if (!empty($taskErrorReports)) {
            throw new common_Exception('Task export has failed.');
        }

        $taskSuccessReports = $task->getReport()->getSuccesses();
        $taskReport = reset($taskSuccessReports);
        $taskFile = $this->getQueueStorage()->getFile($taskReport->getData());
        if ($taskFile->exists()) {
            return $taskFile;
        }

        throw new common_Exception('Export result task does not have an exported file');
    }

    /**
     * Create a task to export result by delivery
     *
     * @param core_kernel_classes_Resource $delivery
     * @return Task
     */
    public function createExportTask(core_kernel_classes_Resource $delivery)
    {
        return $this->getTaskQueue()->createTask(
            ExportDeliveryResultsTask::class,
            [$delivery->getUri()],
            false,
            __('CSV results export for delivery "%s"', $delivery->getLabel()),
            self::DELIVERY_EXPORT_QUEUE_CONTEXT
        );
    }

    /**
     * Check if taskqueue is the sync one
     *
     * @return bool
     */
    public function isSynchronousExport()
    {
        return $this->getTaskQueue() instanceof SyncQueue;
    }

    /**
     * Get the Queue service to manage tasks
     *
     * @return Queue
     */
    protected function getTaskQueue()
    {
        return $this->getServiceLocator()->get(Queue::SERVICE_ID);
    }

    /**
     * Get the directory of Queue storage
     *
     * @return Directory
     */
    public function getQueueStorage()
    {
        return $this->getServiceLocator()->get(FileSystemService::SERVICE_ID)->getDirectory(Queue::FILE_SYSTEM_ID);
    }
}