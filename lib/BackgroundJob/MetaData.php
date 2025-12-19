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
use Psr\Log\LoggerInterface;
use function OCP\Log\logger;

class MetaData extends TimedJob {
	const APP_NAME = 'files_external_dropbox';

	/**
	 * This is used by LoggerInterface for app context
	 * @var string
	 */
	protected $appId = self::APP_NAME;

	/**
	 * @var IConfig
	 */
	private $config;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @param ITimeFactory|null $time
	 * @param IConfig|null $config
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(ITimeFactory $time,
								IConfig $config = null,
								LoggerInterface $logger = null) {

		parent::__construct($time);

		// Run once per 10 minutes
		$this->setInterval(60 * 10);

		// Get static logger and config if they are not in params
		if (is_null($logger) || is_null($config)) {
			$this->fixDIForJobs();
		} else {
			$this->config = $config;
			$this->logger = $logger;
		}
	}

	protected function fixDIForJobs() {
		$this->config = \OC::$server->getConfig();
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

	// The function, that is "run" by the background job handler
	protected function run($arguments) {
		$service = \OC::$server->getGlobalStoragesService();
		$Storages = $service->getAllStorages();
		foreach ($Storages as $storageConfig) {
			$data = $storageConfig->getBackend()->jsonSerialize();
			if ($data['identifier'] === self::APP_NAME) {
				$this->syncStorage($storageConfig);
			}
		}
		return true;
	}

}
