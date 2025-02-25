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

namespace Fusio\Impl\Backend\Api\Scope;

use Fusio\Impl\Authorization\ProtectionTrait;
use PSX\Api\Documentation;
use PSX\Api\Resource;
use PSX\Api\Version;
use PSX\Controller\SchemaApiAbstract;
use PSX\Data\RecordInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Loader\Context;
use PSX\Sql\Condition;

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
            ->addResponse(200, $this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Scope'))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setRequest($this->schemaManager->getSchema('Fusio\Impl\Backend\Schema\Scope\Update'))
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
        $scopeId = (int) $this->getUriFragment('scope_id');
        $scope   = $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope')->get($scopeId);

        if (!empty($scope)) {
            $scope['routes'] = $this->tableManager
                ->getTable('Fusio\Impl\Backend\Table\Scope\Route')
                ->getByScopeId($scope['id']);

            return $scope;
        } else {
            throw new StatusCode\NotFoundException('Could not find scope');
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
        $scopeId = (int) $this->getUriFragment('scope_id');
        $scope   = $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope')->get($scopeId);

        if (!empty($scope)) {
            $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope')->update(array(
                'id'   => $scope['id'],
                'name' => $record->getName(),
            ));

            $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope\Route')->deleteAllFromScope($record->getId());

            $this->insertRoutes($record->getId(), $record->getRoutes());

            return array(
                'success' => true,
                'message' => 'Scope successful updated',
            );
        } else {
            throw new StatusCode\NotFoundException('Could not find scope');
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
        $scopeId = (int) $this->getUriFragment('scope_id');
        $scope   = $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope')->get($scopeId);

        if (!empty($scope)) {
            // check whether the scope is used by an app or user
            $appScopes = $this->tableManager->getTable('Fusio\Impl\Backend\Table\App\Scope')->getCount(new Condition(['scopeId', '=', $scope['id']]));
            if ($appScopes > 0) {
                throw new StatusCode\InternalServerErrorException('Scope is assigned to an app. Remove the scope from the app in order to delete the scope');
            }

            $userScopes = $this->tableManager->getTable('Fusio\Impl\Backend\Table\User\Scope')->getCount(new Condition(['scopeId', '=', $scope['id']]));
            if ($userScopes > 0) {
                throw new StatusCode\InternalServerErrorException('Scope is assgined to an user. Remove the scope from the user in order to delete the scope');
            }

            // delete all routes assigned to the scope
            $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope\Route')->deleteAllFromScope($scope['id']);

            $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope')->delete(array(
                'id' => $scope['id']
            ));

            return array(
                'success' => true,
                'message' => 'Scope successful deleted',
            );
        } else {
            throw new StatusCode\NotFoundException('Could not find scope');
        }
    }

    protected function insertRoutes($scopeId, $routes)
    {
        if (!empty($routes) && is_array($routes)) {
            foreach ($routes as $route) {
                //$this->getFieldValidator()->validate($field);

                if ($route->getAllow()) {
                    $this->tableManager->getTable('Fusio\Impl\Backend\Table\Scope\Route')->create(array(
                        'scopeId' => $scopeId,
                        'routeId' => $route->getRouteId(),
                        'allow'   => $route->getAllow() ? 1 : 0,
                        'methods' => $route->getMethods(),
                    ));
                }
            }
        }
    }
}
