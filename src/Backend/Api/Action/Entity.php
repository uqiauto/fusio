<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\Api\Action;

use Fusio\Impl\Authorization\ProtectionTrait;
use Fusio\Impl\Backend\Table\Action;
use PSX\Api\Documentation;
use PSX\Api\Resource;
use PSX\Api\Version;
use PSX\Controller\SchemaApiAbstract;
use PSX\Data\RecordInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Loader\Context;

/**
 * Entity
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Entity extends SchemaApiAbstract
{
    use ProtectionTrait;
    use ValidatorTrait;

    /**
     * @Inject
     * @var \PSX\Data\Schema\SchemaManagerInterface
     */
    protected $schemaManager;

    /**
     * @Inject
     * @var \PSX\Sql\TableManager
     */
    protected $tableManager;

    /**
     * @return \PSX\Api\DocumentationInterface
     */
    public function getDocumentation()
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Action'))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setRequest($this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Action\Update'))
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Message'))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Message'))
        );

        return new Documentation\Simple($resource);
    }

    /**
     * Returns the GET response
     *
     * @param \PSX\Api\Version $version
     * @return array|\PSX\Data\RecordInterface
     */
    protected function doGet(Version $version)
    {
        $actionId = (int) $this->getUriFragment('action_id');
        $action   = $this->tableManager->getTable('Fusio\Impl\Backend\Table\Action')->get($actionId);

        if (!empty($action)) {
            return $action;
        } else {
            throw new StatusCode\NotFoundException('Could not find action');
        }
    }

    /**
     * Returns the POST response
     *
     * @param \PSX\Data\RecordInterface $record
     * @param \PSX\Api\Version $version
     * @return array|\PSX\Data\RecordInterface
     */
    protected function doCreate(RecordInterface $record, Version $version)
    {
    }

    /**
     * Returns the PUT response
     *
     * @param \PSX\Data\RecordInterface $record
     * @param \PSX\Api\Version $version
     * @return array|\PSX\Data\RecordInterface
     */
    protected function doUpdate(RecordInterface $record, Version $version)
    {
        $actionId = (int) $this->getUriFragment('action_id');
        $action   = $this->tableManager->getTable('Fusio\Impl\Backend\Table\Action')->get($actionId);

        if (!empty($action)) {
            $this->checkLocked($action);

            $this->tableManager->getTable('Fusio\Impl\Backend\Table\Action')->update(array(
                'id'     => $action->getId(),
                'name'   => $record->getName(),
                'class'  => $record->getClass(),
                'config' => $record->getConfig()->getRecordInfo()->getData(),
                'date'   => new \DateTime(),
            ));

            return array(
                'success' => true,
                'message' => 'Action successful updated',
            );
        } else {
            throw new StatusCode\NotFoundException('Could not find action');
        }
    }

    /**
     * Returns the DELETE response
     *
     * @param \PSX\Data\RecordInterface $record
     * @param \PSX\Api\Version $version
     * @return array|\PSX\Data\RecordInterface
     */
    protected function doDelete(RecordInterface $record, Version $version)
    {
        $actionId = (int) $this->getUriFragment('action_id');
        $action   = $this->tableManager->getTable('Fusio\Impl\Backend\Table\Action')->get($actionId);

        if (!empty($action)) {
            $this->checkLocked($action);

            // delete route dependencies
            $this->tableManager->getTable('Fusio\Impl\Backend\Table\Routes\Action')->deleteByAction($action['id']);

            $this->tableManager->getTable('Fusio\Impl\Backend\Table\Action')->delete(array(
                'id' => $action['id']
            ));

            return array(
                'success' => true,
                'message' => 'Action successful deleted',
            );
        } else {
            throw new StatusCode\NotFoundException('Could not find action');
        }
    }

    protected function checkLocked($action)
    {
        if ($action['status'] == Action::STATUS_LOCKED) {
            $paths = $this->tableManager
                ->getTable('Fusio\Impl\Backend\Table\Routes\Action')
                ->getDependingRoutePaths($action['id']);

            $paths = implode(', ', $paths);

            throw new StatusCode\ConflictException('Action is locked because it is used by a route. Change the route status to "Development" or "Closed" to unlock the schema. The following routes reference this schema: ' . $paths);
        }
    }
}
