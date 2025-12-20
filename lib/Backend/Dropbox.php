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

namespace OCA\Files_external_dropbox\Backend;

use OCA\Files_External\Lib\Auth\AuthMechanism;
use OCA\Files_External\Lib\DefinitionParameter;
use OCP\IL10N;

class Dropbox extends \OCA\Files_External\Lib\Backend\Backend {

	/**
	 * Backend constructor.
	 *
	 * @param IL10N $l
	 */
    public function __construct(IL10N $l) {
        $appWebPath = \OC_App::getAppWebPath('files_external_dropbox');

        $refreshTokenParameter = new DefinitionParameter('refresh_token', $l->t('Refresh Token'));
        if (defined('DefinitionParameter::FLAG_HIDDEN')) {
            # Nextcloud >= 30.0.11
            $refreshTokenParameter
                ->setType(DefinitionParameter::VALUE_PASSWORD)
                ->setFlags(DefinitionParameter::FLAG_HIDDEN);
        } else {
            # Nextcloud <= 30.0.10
            $refreshTokenParameter
                ->setType(DefinitionParameter::VALUE_HIDDEN);
        }


        $expiryTimeParameter = new DefinitionParameter('expiry_time', $l->t('Expiry Time'));
        if (defined('DefinitionParameter::FLAG_HIDDEN')) {
            # Nextcloud >= 30.0.11
            $expiryTimeParameter
                ->setType(DefinitionParameter::VALUE_PASSWORD)
                ->setFlags(DefinitionParameter::FLAG_HIDDEN);
        } else {
            # Nextcloud <= 30.0.10
            $expiryTimeParameter
                ->setType(DefinitionParameter::VALUE_HIDDEN);
        }

        $this
			->setIdentifier('files_external_dropbox')
			->addIdentifierAlias('\OC\Files\External_Storage\Dropbox')// legacy compat
			->setStorageClass('\OCA\Files_external_dropbox\Storage\Dropbox')
			->setText($l->t('Dropbox V2'))
			->addParameters([
                $refreshTokenParameter,
                $expiryTimeParameter
            ])
			->addAuthScheme(AuthMechanism::SCHEME_OAUTH2)
			->addCustomJs("../../../$appWebPath/js/dropbox");
	}

}
