<?php
/**
 * @author Hemant Mann <hemant.mann121@gmail.com>
 * @author Daniel Jäger <daniel-jaeger@online.de>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH.
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

namespace OCA\Files_external_dropbox\BackgroundJob;


use OC\Files\Utils\Scanner;
use OCA\Files_External\Lib\StorageConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IUser;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;
use function OCP\Log\logger;

class MetaData extends TimedJob {
	const APP_NAME = 'files_external_dropbox';

	/**
	 * This is used by LoggerInterface for app context
	 * @var string
	 */
	protected $appId = self::APP_NAME;

	/** @var IConfig */
	private $config;
	/** @var IUserManager */
	private $userManager;
	/** @var IDBConnection */
	private $dbConnection;
	/** @var LoggerInterface */
	private $logger;
	/** Amount of users that should get scanned per execution */
	const USERS_PER_SESSION = 500;

	/**
	 * @param ITimeFactory|null $time
	 * @param IConfig|null $config
	 * @param IUserManager|null $userManager
	 * @param IDBConnection|null $dbConnection
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(ITimeFactory $time,
								IConfig $config = null,
								IUserManager $userManager = null,
								IDBConnection $dbConnection = null,
								LoggerInterface $logger = null) {
		// Run once per 10 minutes
		$this->setInterval(1);
		if (is_null($userManager) || is_null($config)) {
			$this->fixDIForJobs();
		} else {
			$this->config = $config;
			$this->userManager = $userManager;
			$this->logger = $logger;
		}
	}

	protected function fixDIForJobs() {
		$this->config = \OC::$server->getConfig();
		$this->userManager = \OC::$server->getUserManager();
		$this->logger = logger(self::APP_NAME);
	}

	/**
	 * Get the storage configuration and sync the storage only if configured = true
	 * Instantiate new Dropbox Storage class, Check for the last stored cursor
	 * if found then check if the storage is updated and scan the modified folders
	 * else scan the whole storage
	 *
	 * @param  StorageConfig $storageConfig
	 * @return boolean      True on success, False on failure
	 */
	protected function syncStorage(StorageConfig $storageConfig) {
		$opts = $storageConfig->getBackendOptions();
		if ($opts['configured'] === 'false') {
			return false;
		}
		try {
			$storage = new \OCA\Files_external_dropbox\Storage\Dropbox($opts);
			$key = 'dropbox_cursor_storage_' . $storageConfig->getId();

			$cursor = $this->config->getAppValue($this->appId, $key, null);
			if ($cursor && $isUpdated = $storage->isStorageUpdated($cursor)) {
				$directories = $storage->getModifiedPaths($cursor);
				foreach ($directories as $directory) {
					$result = $storage->getScanner()->scan($directory, true);
				}
				$cursor = $storage->getLatestCursor();
			} else {
				$cursor = $storage->getLatestCursor();
				$storage->getScanner()->scan('/', true);
			}
			$this->config->setAppValue($this->appId, $key, $cursor);
		} catch (\Exception $e) {
			$this->logger->error('Storage Syncing failed for ' . $storageConfig->getId(), ['exception' => $e]);
			return false;
		}
		return true;
	}

	public function run($argument) {
		$service = \OC::$server->getGlobalStoragesService();
		$resp = $service->getAllStorages();
		$result = [];
		foreach ($resp as $r) {
			$data = $r->getBackend()->jsonSerialize();
			if ($data['identifier'] === $this->appId) {
				$result[] = $r;
			}
		}
		foreach ($result as $r) {
			$this->syncStorage($r);
		}
		return true;
	}
}
