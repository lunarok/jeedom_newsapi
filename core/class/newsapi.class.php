<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class newsapi extends eqLogic {

	public static function cron5() {
		$eqLogics = eqLogic::byType('newsapi', true);
		foreach ($eqLogics as $eqLogic) {
			$eqLogic->refresh();
		}
	}

	public function loadCmdFromConf($type) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $type . '.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $type . '.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		foreach ($device['commands'] as $command) {
			$cmd = null;
			foreach ($this->getCmd() as $liste_cmd) {
				if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
				|| (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
					$cmd = $liste_cmd;
					break;
				}
			}
			if ($cmd == null || !is_object($cmd)) {
				$cmd = new newsapiCmd();
				$cmd->setEqLogic_id($this->getId());
				utils::a2o($cmd, $command);
				$cmd->save();
			}
		}
	}

	public function postAjax() {
		$this->loadCmdFromConf($this->getConfiguration('type'));
	}

	public function getOptions() {
		$options['country'] = $this->getConfiguration('country');
		$options['category'] = $this->getConfiguration('category');
		$options['sources'] = $this->getConfiguration('sources');
		$options['q'] = $this->getConfiguration('q');
		return $options;
	}

	public function refresh() {
		$this->getInfos($this->getOptions());
	}

	public function getInfos($_options) {
		$url = 'https://newsapi.org/v2/' . $this->getConfiguration('type') . '?apiKey=' . $this->getConfiguration('api');
		foreach ($_options as $key => $value) {
			$url .= '&' . $key . '=' . $value;
		}
    $request_http = new com_http($url);
		$data = $request_http->exec(30);
    $data = json_decode($data,true);
	}

}

class newsapiCmd extends cmd {
	public function execute($_options = null) {
		if ($this->getLogicalId() == 'refresh') {
			$eqLogic->refresh();
		}
		if ($this->getLogicalId() == 'options') {
			if (isset($_options['title'])) {
				$options1 = $eqLogic->getOptions();
				$options2 = arg2array($_options['title']);
				$options = array_merge($options1, $options2);
			} else {
				return false;
			}
			$eqLogic->getInfos($options);
		}
	}
}
?>