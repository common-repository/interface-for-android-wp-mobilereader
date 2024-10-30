<?php

class ClassWPCPost{

	public function removeFeaturedImage($iPostId){
		return delete_post_thumbnail($iPostId);
	}
	
	public function getPostsDataFields($arSinglePost)
	{
		$arResult = array();
		$arResult["ID"] = $arSinglePost["ID"];
		$arResult["post_title"] = $arSinglePost["post_title"];
		$arResult["post_content"] = $arSinglePost["post_content"];
		$arResult["post_date"] = $arSinglePost["post_date"];
		$arResult["post_modified"] = $arSinglePost["post_modified"];
		$arResult["post_author"] = $arSinglePost["post_author"];
		$arResult["post_status"] = $arSinglePost["post_status"];
		
		return $arResult;
	}
	
	public function getImageData($arSinglePost){
		
		$arImages = $this->getImages($arSinglePost["ID"]);
		$arSinglePost = array_merge($arSinglePost, $arImages);
		
		return $arSinglePost;
	}
	
	public function getImages($iPostId){
	
		$arResult = array();
		
		if($iPostId || ((int)$iPostId) > 0)
		{
			$featured_image = wp_get_attachment_url(get_post_thumbnail_id($iPostId));
			$arResult["featured_image"] = $featured_image ? $featured_image : "";

			$featured_image_thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($iPostId),"thumbnail",false);
			$arResult["featured_image_thumbnail"] = $featured_image_thumbnail[0] ? $featured_image_thumbnail[0] : "";

			$featured_image_medium = wp_get_attachment_image_src(get_post_thumbnail_id($iPostId),"medium",false);
			$arResult["featured_image_medium"] = $featured_image_medium[0] ? $featured_image_medium[0] : "";
		
			$featured_image_large = wp_get_attachment_image_src(get_post_thumbnail_id($iPostId),"large",false);
			$arResult["featured_image_large"] = $featured_image_large[0] ? $featured_image_large[0] : "";
		
			$featured_image_full = wp_get_attachment_image_src(get_post_thumbnail_id($iPostId),"full",false);
			$arResult["featured_image_full"] = $featured_image_full[0] ? $featured_image_full[0] : "";
		}
		
		return $arResult;
	}
	
}
