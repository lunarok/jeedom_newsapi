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
	public static $_widgetPossibility = array('custom' => true);

	public static function cron30() {
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
		$this->loadCmdFromConf('newsapi');
		$this->refresh();
	}

	public function preUpdate() {
		if ($this->getConfiguration('api') == '') {
			throw new Exception(__('Vous devez remplir le champ API',__FILE__));
			return;
		}
		if ($this->getConfiguration('number') == '') {
			throw new Exception(__('Vous devez remplir le numéro de la news',__FILE__));
			return;
		}
		if ($this->getConfiguration('type') == 'top-headlines') {
			if ($this->getConfiguration('country') == '') {
				throw new Exception(__('Vous devez remplir le champ pays',__FILE__));
				return;
			}
		} else {
			if ($this->getConfiguration('languages') == '') {
				throw new Exception(__('Vous devez remplir le champ langue',__FILE__));
				return;
			}
		}
	}

	public function getOptions() {
		$options['q'] = $this->getConfiguration('q');
		if ($this->getConfiguration('type') == 'top-headlines') {
			$options['country'] = $this->getConfiguration('country');
			if ($this->getConfiguration('category') != "none") {
				$options['category'] = $this->getConfiguration('category');
			} else {
				$options['sources'] = $this->getConfiguration('sources');
			}
		} else {
			$options['languages'] = $this->getConfiguration('languages');
			$options['sources'] = $this->getConfiguration('sources');
			$options['domains'] = $this->getConfiguration('domains');
			$options['excludeDomains'] = $this->getConfiguration('excludeDomains');
			$options['sortBy'] = $this->getConfiguration('sortBy');
		}
		return $options;
	}

	public function refresh() {
		if ($this->getConfiguration('api') == '' || $this->getConfiguration('number') == '') {
			return;
		}
		$this->getInfos($this->getOptions());
		$this->refreshWidget();
	}

	public function getInfos($_options) {
		$url = 'https://newsapi.org/v2/' . $this->getConfiguration('type') . '?apiKey=' . $this->getConfiguration('api');
		foreach ($_options as $key => $value) {
			$url .= '&' . $key . '=' . urlencode($value);
		}
		$number = intval($this->getConfiguration('number')) - 1;
		log::add('newsapi', 'debug', 'Ask ' . $url . ' for article ' . $number);
		$request_http = new com_http($url);
		$data = $request_http->exec(30);
		$data = json_decode($data,true);
		if ($data["status"] != 'ok') {
			return;
		}
		$this->checkAndUpdateCmd('source', $data["articles"][$number]["source"]["name"]);
		$this->checkAndUpdateCmd('title', $data["articles"][$number]["title"]);
		$this->checkAndUpdateCmd('description', $data["articles"][$number]["description"]);
		$this->checkAndUpdateCmd('url', $data["articles"][$number]["url"]);
		$this->checkAndUpdateCmd('urlToImage', $data["articles"][$number]["urlToImage"]);
		$this->checkAndUpdateCmd('content', $data["articles"][$number]["content"]);
	}

	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		foreach ($this->getCmd('info') as $cmd) {
      $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
      $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
      $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
      $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
      if ($cmd->getIsHistorized() == 1) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
      }
    }
		$templatename = 'newsapi';
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $templatename, 'newsapi')));
	}

}

class newsapiCmd extends cmd {
	public function execute($_options = null) {
		$eqLogic = $this->getEqLogic();
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
