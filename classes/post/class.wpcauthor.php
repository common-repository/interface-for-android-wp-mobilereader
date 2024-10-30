<?php

class ClassWPCAuthor {
	
	public function getAuthorData($arSinglePost){
		
		$objUserdata = get_userdata($arSinglePost["post_author"]);
		
		$arAuthor = array();
		
		$arAuthor["post_author_user_login"] = $objUserdata->data->user_login;
		$arAuthor["post_author_user_nicename"] = $objUserdata->data->user_nicename;
		$arAuthor["post_author_display_name"] = $objUserdata->data->display_name;
		
		return $arAuthor;
		
	}
	
}
