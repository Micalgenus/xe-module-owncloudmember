<?php
/**
 * @class	owncloudmember Controller Module
 * @date	2016/10/13
 * @author	Micalgenus(micalgenus@gmail.com)
 * @package /modules/owncloudmember
 * @version 1.0
 * @brief	ownCloud 회원 동기화 관리자 controller
 */
class owncloudmemberController extends owncloudmember
{
	/**
	 * @brief	Initilization
	 */
	function init() {
	}

	/**
	 * @brief 로그인 실패 시 로그인 기록 남김
	 */
	function triggerBeforeLogin(&$member_info) {


		$user_id = $member_info->user_id;
		$password = $member_info->password;
		if(!$user_id || !$password) {
			return NULL;
		}

		$oMemberModel = &getModel('member');
		$member = $oMemberModel->getMemberInfoByUserID($user_id);

		if(!$member) {
			return NULL;
		}

		$password_org = $member->password;

		if($oMemberModel->isValidPassword($password_org, $password)) {
			$oOwncloudememberAdminModel = &getAdminModel('owncloudmember');

			$oOwncloudememberAdminModel->userSync($user_id, $password);
		}
	}
}
