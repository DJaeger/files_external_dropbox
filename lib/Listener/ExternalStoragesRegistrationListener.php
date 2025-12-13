<?php

declare(strict_types=1);
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

namespace OCA\Files_external_dropbox\Listener;

use OCA\Files_External\Service\BackendService;
use OCA\Files_external_dropbox\Backend\Provider as BackendProvider;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

class ExternalStoragesRegistrationListener implements IEventListener {

	/** @var BackendService */
	private $backendService;
	/** @var BackendProvider */
	private $backendProvider;
	/** @var AuthMechanismProvider */
	private $authMechanismProvider;

	public function __construct(
		BackendService $backendService,
		BackendProvider $backendProvider,
	) {
		$this->backendService = $backendService;
		$this->backendProvider = $backendProvider;
	}

	public function handle(Event $event): void {
		$this->backendService->registerBackendProvider($this->backendProvider);
	}
}