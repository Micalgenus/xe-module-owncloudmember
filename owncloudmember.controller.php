<?php
/**
 * @class	owncloudmember Controller Module
 * @date	2016/10/19
 * @author	Micalgenus(micalgenus@gmail.com)
 * @package /modules/owncloudmember
 * @version 0.0.2
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
	function triggerBeforeLogin(&$member_info)
    {
		$user_id = $member_info->user_id;
		$password = trim($member_info->password);

		if(!$user_id || !$password) return NULL;

		$oMemberModel = &getModel('member');
		$member = $oMemberModel->getMemberInfoByUserID($user_id);

		if(!$member) return NULL;

		$password_org = $member->password;

		if($oMemberModel->isValidPassword($password_org, $password))
        {
			$oOwncloudememberAdminModel = &getAdminModel('owncloudmember');
			$oOwncloudememberAdminModel->userSync($user_id, $password);
		}
	}

    function triggerBeforeChangePassword()
    {
        if (Context::get('act') == 'procMemberModifyPassword')
        {
		    if(Context::get('is_logged'))
            {
                $user_id = Context::get('logged_info')->user_id;
                $current_password = trim(Context::get('current_password'));
                $password = trim(Context::get('password1'));
                // Get information of logged-in user
                $logged_info = Context::get('logged_info');
                $member_srl = $logged_info->member_srl;
                // Create a member model object
                $oMemberModel = getModel('member');
                // Get information of member_srl
                $columnList = array('member_srl', 'password');

                $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl, 0, $columnList);
                // Verify the cuttent password
                if(!$oMemberModel->isValidPassword($member_info->password, $current_password, $member_srl)) return new Object(-1, 'invalid_password');

                // Check if a new password is as same as the previous password
                if($current_password == $password) return new Object(-1, 'invalid_new_password');

                // ownCloud 비밀번호 변경
			    $oOwncloudememberAdminModel = &getAdminModel('owncloudmember');
                $oOwncloudememberAdminModel->changeUserPassword($user_id, $password);
            }
        }
    }
}
