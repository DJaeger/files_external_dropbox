<?php
/**
 * @author Hemant Mann <hemant.mann121@gmail.com>
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

namespace OCA\Files_external_dropbox\Storage;

use Kunnu\Dropbox\Models\FolderMetadata;
use HemantMann\Flysystem\Dropbox\Adapter as DropboxAdapter;

class Adapter extends DropboxAdapter {
	protected function normalizeResponse($obj) {
		$result = parent::normalizeResponse($obj);

		if ($result['type'] === 'dir') {
			$result['timestamp'] = 0;
		}
		return $result;
	}

	public function getModifiedFolders($items) {
		$result = [];
		foreach ($items as $item) {
			if ($item instanceof FolderMetadata) {
				$dirname = $item->getPathDisplay();
			} else {
				$dirname = dirname($item->getPathDisplay());
			}
			$result[] = $dirname;
		}
		return array_unique($result);
	}
}
