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

namespace OCA\Files_external_dropbox\Controller;


use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\IL10N;
use OCP\IRequest;

/**
 * Oauth controller for Dropbox
 */
class OauthController extends Controller {
	/**
	 * L10N service
	 *
	 * @var IL10N
	 */
	protected $l10n;

	/**
	 * Creates a new storages controller.
	 *
	 * @param string $AppName application name
	 * @param IRequest $request request
	 * @param IL10N $l10n l10n service
	 */
	public function __construct(
		$AppName,
		IRequest $request,
		IL10N $l10n
	) {
		parent::__construct($AppName, $request);
		$this->l10n = $l10n;
	}

	/**
	 * Create a storage from its parameters
	 *
	 * @param string $client_id
	 * @param string $client_secret
	 * @param string $redirect
	 * @param int $step
	 * @param string $code
	 * @return DataResponse
	 * @NoAdminRequired
	 */
	public function receiveToken(
		$client_id,
		$client_secret,
		$redirect,
		$step,
		$code
	) {
		$clientId = $client_id;
		$clientSecret = $client_secret;
		if ($clientId !== null && $clientSecret !== null && $redirect !== null) {
			$app = new \Kunnu\Dropbox\DropboxApp($clientId, $clientSecret);
			$dropbox = new \Kunnu\Dropbox\Dropbox($app);
			$authHelper = $dropbox->getAuthHelper();

			if ($step == 1) {
				$authUrl = $authHelper->getAuthUrl($redirect);
				return new DataResponse([
					'status' => 'success',
					'data' => ['url' => $authUrl]
				]);
			} else if ($step == 2 && isset($code)) {
				try {
					$accessToken = $authHelper->getAccessToken($code, null, $redirect);
					return new DataResponse([
						'status' => 'success',
						'data' => ['token' => $accessToken->getToken()]
					]);
				} catch (\Exception $ex) {
					return new DataResponse([
						'data' => [
							'message' => $this->l10n->t('Step 2 failed. Exception: %s', [$ex->getMessage()])
						]
					], Http::STATUS_UNPROCESSABLE_ENTITY);
				}
			}
		}
		return new DataResponse(
			['data' => ['message' => $this->l10n->t('Invalid Request Params!!')]],
			Http::STATUS_BAD_REQUEST
		);
	}
}
