<?php
/**
 * @author Daniel Jäger <daniel-jaeger@online.de>
 *
 * @copyright Copyright (c) 2025, Daniel Jäger <daniel-jaeger@online.de>.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Files_external_dropbox\Backend;

use OCA\Files_External\Lib\Auth\OAuth2\OAuth2;
use OCA\Files_External\Lib\Backend\Backend;
use OCA\Files_External\Lib\Config\IBackendProvider;
use OCP\L10N\IFactory;

class Provider implements IBackendProvider {

	/** @var IFactory */
	protected $lFactory;

	public function __construct(IFactory $lFactory) {
		$this->lFactory = $lFactory;
	}


	#[\Override]
	public function getBackends(): array {
		$backend = new \OCA\Files_external_dropbox\Backend\Dropbox(
			$this->lFactory->get('files_external_dropbox'),
			new OAuth2($this->lFactory->get('files_external'))
		);
		return [ $backend ];
	}
}