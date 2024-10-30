<?php

require_once (WP_ANDROID_CLIENT_PLUGIN_DIR . "/classes/class.wpcontract.php");

class ClassWPUserCapabilities {
	
	public function hasCapability($sCapability, $iUserId=0){
		$bHasCapability = false;
		global $current_user;
		
		if($iUserId == 0){
			$iUserId = $current_user->ID;
		}
		
		if(user_can($iUserId, $sCapability)){
			$bHasCapability = true;
		}
		
		return $bHasCapability;
	}
	
	public function hasPostUpdateCapability($postId, $postState){
		$bHasCapability = false;
		global $current_user;
		
		if($postState == WPContract::$POST_STATE_PUBLISH
			&& !$this->hasCapability(WPContract::$CAPABILITY_EDIT_PUBLISHED_POSTS)){
			
			return $bHasCapability;
		}
		
		$arPost = get_post($postId, ARRAY_A);
		$idPostAuthor = $arPost["post_author"];
		$idCurrentUser = $current_user->ID;
		
		if($idCurrentUser == $idPostAuthor){
			$bHasCapability = true;
		}else{
			if($this->hasCapability(WPContract::$CAPABILITY_EDIT_OTHER_POSTS)){
				$bHasCapability = true;
			}
		}
		
		
		return $bHasCapability;
	}
	
	public function hasUpdateCapability($sPostState){
		$bHasCapability = false;
		
		if($sPostState == WPContract::$POST_STATE_DRAFT){
			if($this->hasCapability(WPContract::$CAPABILITY_EDIT_POSTS)){
				$bHasCapability = true;
			}
		}
		
		if($sPostState == WPContract::$POST_STATE_PENDING){
			if($this->hasCapability(WPContract::$CAPABILITY_EDIT_POSTS)){
				$bHasCapability = true;
			}
		}
		
		if($sPostState == WPContract::$POST_STATE_PUBLISH){
			if($this->hasCapability(WPContract::$CAPABILITY_EDIT_PUBLISHED_POSTS)){
				$bHasCapability = true;
			}
		}
		return  $bHasCapability;
	}
	
	public function getAllowedPostTypes(){
		$sAllowedPostTypes = "";
		
		foreach(WPContract::$arPostTypes as $sPostType){
			if(strlen($sAllowedPostTypes)){
				$sAllowedPostTypes .= ",";
			}
			
			if($this->isPostTypeAllowed($sPostType)){
				$sAllowedPostTypes .= $sPostType;
			}
			
		}
		return $sAllowedPostTypes;
	}
	
	public function getAllowedPostStates(){
		
		$arAllowedPostStates = array();
		$sAllowedPostStates = "";
		
		if($this->isPostStateAllowed(WPContract::$POST_STATE_DRAFT)){
			$arAllowedPostStates[] = WPContract::$POST_STATE_DRAFT;
		}

		if($this->isPostStateAllowed(WPContract::$POST_STATE_PENDING)){
			$arAllowedPostStates[] = WPContract::$POST_STATE_PENDING;
		}
		
		if($this->isPostStateAllowed(WPContract::$POST_STATE_PUBLISH)){
			$arAllowedPostStates[] = WPContract::$POST_STATE_PUBLISH;
		}
		
		$sAllowedPostStates = implode(",", $arAllowedPostStates);
		
		return $sAllowedPostStates;
	}
	
	public function isPostStateAllowed($sPostState){
		$bIsAllowed = false;
		
		if(current_user_can(WPContract::$CAPABILITY_DELETE_POSTS) // Contributor
				&& current_user_can(WPContract::$CAPABILITY_EDIT_POSTS)
				&& $sPostState == "pending"){
			$bIsAllowed = true;
		}
		elseif(current_user_can(WPContract::$CAPABILITY_DELETE_POSTS) // Contributor
				&& current_user_can(WPContract::$CAPABILITY_EDIT_POSTS)
				&& $sPostState == "draft"){
			$bIsAllowed = true;
		}
		elseif(current_user_can(WPContract::$CAPABILITY_DELETE_POSTS) // Author
				&& current_user_can(WPContract::$CAPABILITY_EDIT_POSTS)
				&& current_user_can(WPContract::$CAPABILITY_EDIT_PUBLISHED_POSTS)
				&& $sPostState == "publish"){
			$bIsAllowed = true;
		}
		return $bIsAllowed;
	}
	
	public function isPostTypeAllowed($sPostType){
		
		$bIsAllowed = false;
		
		if(current_user_can(WPContract::$CAPABILITY_DELETE_POSTS)
				&& current_user_can(WPContract::$CAPABILITY_EDIT_POSTS)
				&& $sPostType == "post"){
			
			$bIsAllowed = true;
		}
		
		return $bIsAllowed;
		
	}
	
	public function getCapabilityInfo(){
		
		$WPContract = new WPContract();
		
		$sResult = <<< HTML
			<table class="wp-list-table widefat">
			<thead>
				<tr>
					<th class="manage-column column-name column-primary">Capability</th>
					<th>Description</th>
					<th>has normally(eventually manipulated by other extensions)</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>{$WPContract::$CAPABILITY_EDIT_POSTS}</td>
					<td>Edit own posts</td>
					<td>Contributor, Author, Editor, Administrator</td>
				</tr>
				<tr>
					<td>{$WPContract::$CAPABILITY_EDIT_PUBLISHED_POSTS}</td>
					<td>Edit posts with state "publish"</td>
					<td>Author, Editor, Administrator</td>
				</tr>
				<tr>
					<td>{$WPContract::$CAPABILITY_PUBLISH_POSTS}</td>
					<td>Save post with state "publish"</td>
					<td>Author, Editor, Administrator</td>
				</tr>
				<tr>
					<td>{$WPContract::$CAPABILITY_UPLOAD_FILES}</td>
					<td>Upload files(actually only featired image)</td>
					<td>Author, Editor, Administrator</td>
				</tr>
				<tr>
					<td>{$WPContract::$CAPABILITY_EDIT_OTHER_POSTS}</td>
					<td>Edit posts other users</td>
					<td>Editor, Administrator</td>
				</tr>
			</tbody>
			</table>
HTML;
		
		return $sResult;
	}
	
	public function getAllRoles(){
		global $wp_roles;
		
		$arRoles = $wp_roles->get_names();
		
		return $arRoles;
	}
	
	public function isEditingByRoleAllowed(){
		global $current_user;
		
		$bIsAllowed = false;
		
		$arChecked= get_option("wpc_editing_available_by_role");
		$arUserRoles = $current_user->roles;
		
		foreach($arUserRoles as $key => $keyUserRole){
			
			if(is_array($arChecked) && isset($arChecked[$keyUserRole]) && $arChecked[$keyUserRole]){
				$bIsAllowed = true;
			}
			
		}
		return $bIsAllowed;
	}
	
	
}
