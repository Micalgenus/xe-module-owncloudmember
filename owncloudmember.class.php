<?php
/**
 * @class	owncloudmember Module
 * @date	2016/10/19
 * @author	Micalgenus(micalgenus@gmail.com)
 * @package /modules/owncloudmember
 * @version 0.0.2
 * @brief	ownCloud 회원 동기화 모듈
 */
 class owncloudmember extends ModuleObject {

	/**
	 * @brief Init
	 */
	function owncloud()
    {
		if(!Context::isInstalled()) return;
	}

	/**
	 * @brief Update Check
	 */
	function checkUpdate() {
		if ($this->config == null) return true;
		return false;
	}

	/**
	 * @brief Update
	 */
	function moduleUpdate()
    {
		$oModuleModel = &getModel('module');
		$oModuleController = &getController('module');

		$config = $this->InitConfig();
		$oModuleController->insertModuleConfig('owncloudmember', $config);

		return new Object(0, 'success_updated');
	}

	/**
	 * @brief 캐시 파일 재생성
	 */
	function recompileCache()
    {
	}

	/**
	 * @brief Init Module Config
	 */
	function InitConfig()
    {
		$config = getModel('module')->getModuleConfig('owncloudmember');
		$config->admin_id = $config->admin_id ?: "";
		$config->admin_pw = $config->admin_pw ?: "";
		$config->site_url = $config->site_url ?: "";

		return $config;
	}
}
?>