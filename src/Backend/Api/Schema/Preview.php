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

namespace Fusio\Impl\Backend\Api\Schema;

use Fusio\Impl\Authorization\ProtectionTrait;
use PSX\Controller\ApiAbstract;
use PSX\Data\Schema\Generator;
use PSX\Data\SchemaInterface;
use PSX\Http\Exception as StatusCode;
use RuntimeException;

/**
 * Preview
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Preview extends ApiAbstract
{
    use ProtectionTrait;

    public function onGet()
    {
        $sql = 'SELECT schema.cache
				  FROM fusio_schema `schema`
				 WHERE schema.id = :id';

        $row = $this->connection->fetchAssoc($sql, array('id' => $this->getUriFragment('schema_id')));

        if (!empty($row)) {
            $generator = new Generator\Html();
            $schema    = unserialize($row['cache']);

            if ($schema instanceof SchemaInterface) {
                $this->setHeader('Content-Type', 'text/html');
                $this->setBody($generator->generate($schema));
            } else {
                throw new RuntimeException('Invalid schema');
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid schema id');
        }
    }
}
