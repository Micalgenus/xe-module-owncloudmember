<?php
/**
 * @class	owncloudmember Admin Model Module
 * @date	2016/10/19
 * @author	Micalgenus(micalgenus@gmail.com)
 * @package /modules/owncloudmember
 * @version 0.0.2
 * @brief	ownCloud 회원 동기화 관리자 Model
 */
class owncloudmemberAdminModel extends owncloudmember
{
	/**
	 * @brief	Initilization
	 */
	function init() {
	}

	function getAdminPassword() {
		$config = getModel('module')->getModuleConfig('owncloudmember');
		return convert_uudecode($config->admin_pw);
	}

    function getQuota() {
		$config = getModel('module')->getModuleConfig('owncloudmember');

        if ($config->quota_option == 'none') return 'none';
		
        $size = $config->quota_size ?: '0';
        $size = $size < 0 ? '0' : $size;

        return $size . $config->quota_option;
    }

	function getGroupListById($user_id = NULL) {
		$oMemberModel = &getModel('member');
		$member = $oMemberModel->getMemberInfoByUserID($user_id);
		return $oMemberModel->getMemberGroups($member->member_srl);
	}

	/**
	 * @brief	Execute Query
	 */
	function sendQuery($path = NULL, $protocol = 'GET', $option = NULL) {
		// Parameter check
		if ($path == NULL) return NULL;

		// Model Init
		$config = getModel('module')->getModuleConfig('owncloudmember');

		// Value Init
		$url = $config->site_protocol . $config->admin_id . ":" . convert_uudecode($config->admin_pw) . "@" . $config->site_url . "/";
		$url = $url . $path . " ";
		$protocol = strtoupper($protocol);

		$cURL = curl_init ();
		curl_setopt($cURL, CURLOPT_URL, $url);

		switch ($protocol) {
			case "GET":
				break;
			case "PUT":
				curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($cURL, CURLOPT_POSTFIELDS, http_build_query($option));
				break;
			case "POST":
				curl_setopt($cURL, CURLOPT_POST, 1);
				curl_setopt($cURL, CURLOPT_POSTFIELDS, $option);
				curl_setopt($cURL, CURLOPT_POSTFIELDSIZE, 0);
				break;
			default:
				return false;
		}

		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, 1);

		$res = curl_exec($cURL);
		// return XML object
		return simplexml_load_string($res); 
	}

	

	/**
	 * @brief	Create ownCloud user from xe member information.
	 */
	function createUser($user_id = NULL, $password = NULL) {
		// Parameter check
		if ($user_id == NULL || $password == NULL) return NULL;

		// String encoding
		if (preg_match('!!u', $password)) $password = iconv("euckr", "utf8", $password);

		// Value init
		$url = "/ocs/v1.php/cloud/users";
		$post_data["userid"] = $user_id;
		$post_data["password"] = $password;

		// Execute query
		$data = $this->sendQuery($url, 'POST', $post_data);
		$code = $data->meta->statuscode;

		// Create success
		if ($code == 100) {
			$this->syncUserInfo($user_id);
			return true;
		}
		// Create fail
		else return false;

	}

	/**
	 * @brief	Exist check user from ownCloud.
	 */
	function userExistCheck($user_id = NULL) {
		// Parameter check
		if ($user_id == NULL) return NULL;
		// make url
		$url = "/ocs/v1.php/cloud/users/" . $user_id;
		$data = $this->sendQuery($url, 'GET');

		$code = $data->meta->statuscode;

		// exist user
		if ($code == 100) return true;
		// exception
		else return NULL;
	}

	/**
	 * @brief
	 */
	function grantCheck($user_id = NULL) {
		// Parameter check
		if ($user_id == NULL) return NULL;

		$group_list = $this->getGroupListById($user_id);

		if ($group_list[75307] || $group_list[3]) 
            return true;
		return false;
	}

	/**
	 * @brief
	 */
	function userSync($user_id = NULL, $password = NULL) {
		// Parameter check
		if ($user_id == NULL) return NULL;

		// Permission check
		if ($this->grantCheck($user_id) == false) return NULL;

		// Exist check
		if ($this->userExistCheck($user_id) == false)
			// Password Exist
			if ($password != NULL)
				$this->createUser($user_id, $password);

		$this->syncUserInfo($user_id);
	}

	/**
	 * @brief
	 */
	function allUserSync() {
		$oMemberAdminModel = &getAdminModel('member');
		$oMemberModel = &getModel('member');

		$args->list_count = 2100000000; // Limit
		$members = executeQuery('member.getMemberList', $args);
		$members = $members->data;

		foreach ($members as $member) {
            $this->userSync($member->user_id);
		}
	}

	function syncUserInfo($user_id = NULL) {
		// Parameter check
		if ($user_id == NULL) return NULL;

		$oMemberModel = &getModel('member');
		$member = $oMemberModel->getMemberInfoByUserID($user_id);

		$this->changeUserInfo($user_id, "display", $member->user_name);
		$this->changeUserInfo($user_id, "email", $member->email_address);
		//$this->changeUserInfo($user_id, "quota", $this->getQuota());
        $this->addGroup($user_id, "ISCERT");
	}

    function changeUserPassword($user_id = NULL, $password = NULL)
    {
		// Parameter check
        if ($user_id == NULL || $password == NULL) return NULL;

		// String encoding
		if (preg_match('!!u', $password)) $password = iconv("euckr", "utf8", $password);

		$result = $this->changeUserInfo($user_id, "password", $password);
        // success
        if ($result) return true;
        // fail
        else return false;
    }

	function changeUserInfo($user_id = NULL, $key = NULL, $value = NULL)
    {
		// Parameter check
		if ($user_id == NULL || $key == NULL || $value == NULL) return NULL;

		$url = "/ocs/v1.php/cloud/users/" . $user_id;

		$options["key"] = $key;
		$options["value"] = $value;

		// Execute query
		$data = $this->sendQuery($url, "PUT", $options);
		$code = $data->meta->statuscode;

		// Change success
		if ($code == 100) return true;
		// Change fail
		else return false;
	}

    function getOwncloudGroupList() {
        // Set URL
		$url = "/ocs/v1.php/cloud/groups";

		// Execute query
		$data = $this->sendQuery($url, "GET");
		$code = $data->meta->statuscode;

		// Change success
		if ($code == 100) return $data->data->groups->element;
		// Change fail
		else return NULL;
    }

	function addGroup($user_id = NULL, $group_id = NULL) {
		// Parameter check
		if ($user_id == NULL || $group_id == NULL) return NULL;

		$url = "/ocs/v1.php/cloud/users/" . $user_id . "/groups";

		$options["groupid"] = $group_id;

		// Execute query
		$data = $this->sendQuery($url, "POST", $options);
		$code = $data->meta->statuscode;

		// Add success
		if ($code == 100) return true;
		// Add fail
		else return false;
	}
}