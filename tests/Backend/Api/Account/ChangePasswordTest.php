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

namespace Fusio\Impl\Backend\Api\Account;

use Fusio\Impl\Fixture;
use PSX\Test\ControllerDbTestCase;
use PSX\Test\Environment;

/**
 * ChangePasswordTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ChangePasswordTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/account/change_password', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'oldPassword'    => '0a29e5bcaa810de0ca0513d9d4ab62f1860f998a',
            'newPassword'    => '0a29e5bcaa810de0ca0513d9d4ab62f1860f998f',
            'verifyPassword' => '0a29e5bcaa810de0ca0513d9d4ab62f1860f998f',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "success": true,
    "message": "Password successful changed"
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
