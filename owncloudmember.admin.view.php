<?php
/**
 * @class	owncloudmember Admin View Module
 * @date	2016/10/12
 * @author	Micalgenus(micalgenus@gmail.com)
 * @package /modules/owncloudmember
 * @version 1.0
 * @brief	ownCloud 회원 동기화 관리자 View
 */
class owncloudmemberAdminView extends owncloudmember
{
	/**
	 * @brief	Initilization
	 */
	function init() {
		// Setting layout template path
		$template_path = sprintf("%stpl/",$this->module_path);
		$this->setTemplatePath($template_path);
	}

	/**
	 * @brief	ownCloud admin index page
	 */
	function dispOwncloudmemberAdminIndex() {
		// Get config
		$config = getModel('module')->getModuleConfig('owncloudmember');

        $oOwncloudmemberAdminModel = &getAdminModel('owncloudmember');
        //$oOwncloudmemberAdminModel->addGroup('hgs196', '홍보부');
        //echo $oOwncloudmemberAdminModel->getQuota();

		// Setting value
		Context::set('able_module', $config->able_module);
		Context::set('able_admin', $config->able_admin);
		Context::set('site_protocol', $config->site_protocol);
		Context::set('site_url', $config->site_url);
		Context::set('admin_id', $config->admin_id);
		Context::set('admin_pw', $config->admin_pw ? str_repeat('*', 8) : '');
		Context::set('quota_size', $config->quota_size);
		Context::set('quota_option', $config->quota_option);

		// Set layout template
		$this->setTemplateFile('index');
	}

	function dispOwncloudmemberAdminGroup() {
		// Set layout template
		$this->setTemplateFile('group');
	}
}
