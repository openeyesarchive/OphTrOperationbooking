<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace OEModule\OphTrOperationbooking\services;

class OphTrOperationbooking_Operation_WardService extends \services\DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'OphTrOperationbooking_Operation_Ward';

	static protected $model_map = array(
		'OphTrOperationbooking_Operation_Ward' => array(
			'fields' => array(
				'site_ref' => array(self::TYPE_REF, 'site_id', 'Site'),
				'name' => 'name',
				'long_name' => 'long_name',
				'directions' => 'directions',
				'restriction' => 'restriction',
				'code' => 'code',
				'display_order' => 'display_order',
				'active' => 'active',
			),
		),
	);

	public function search(array $params)
	{
	}
}
