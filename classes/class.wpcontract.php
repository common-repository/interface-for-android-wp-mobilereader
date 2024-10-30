<?php


class WPContract {

	static $CAPABILITY_DELETE_POSTS = "delete_posts"; // Contributor
	static $CAPABILITY_EDIT_POSTS = "edit_posts"; // Contributor
	static $CAPABILITY_PUBLISH_POSTS = "publish_posts"; // Author
	static $CAPABILITY_DELETE_PUBLISHED_POSTS = "delete_published_posts"; // Author
	static $CAPABILITY_EDIT_PUBLISHED_POSTS = "edit_published_posts"; // Author
	static $CAPABILITY_UPLOAD_FILES = "upload_files"; // Author
	static $CAPABILITY_EDIT_OTHER_POSTS = "edit_others_posts"; // Editor
	static $CAPABILITY_DELETE_OTHER_POSTS = "delete_others_posts"; // Editor
	
	static $arPostTypes = array("post");
	
	static $POST_STATE_DRAFT = "draft";
	static $POST_STATE_PENDING = "pending";
	static $POST_STATE_PUBLISH = "publish";
	
	static $FEATURED_IMAGE_STATE_DELETED = "deleted";
	
	static $TABLE_PREVENT_BRUTEFORCE = "wpc_prevent_bruteforce";
	static $COUNT_PER_LOGIN_PREVENT_BRUTEFORCE = 5;
	static $COUNT_PER_IP_PREVENT_BRUTEFORCE = 200;
	static $OBSERVATION_TIME_PREVENT_BRUTEFORCE = 5; // 5 Minuten 

	// ajax filtert Feld "ID" aus, deswegen maskieren
	static $COMMUNICATION_FIELD_MASK_POST_FIELD_NAME = "MASKED_FIELD_NAME_";
	
	static $USER_AUTH_KEY = "USER_AUTH";

	static $COMMUNICATION_FIELD_POST_ONLINE_ID = "ID";
	static $COMMUNICATION_FIELD_POST_TITLE = "post_title";
	static $COMMUNICATION_FIELD_POST_CONTENT = "post_content";
	static $COMMUNICATION_FIELD_POST_STATUS = "post_status";
	static $COMMUNICATION_FIELD_POST_CATEGORY = "post_category";
	static $COMMUNICATION_FIELD_POST_STATE_IMAGE_FEATURED = "state_image_featured";

}
