<?php
class OpenGraphTags {
	protected $tagArray;
	
	function __construct($tagArray = array())
	{
		$this->tagArray = $tagArray;
	}
	
	function render()
	{
		$tagHtml = '';
		foreach ($this->tagArray as $name => $value) {
			$namespace = 'og';
			if (in_array($name, array('admins', 'app_id'))) {
				$namespace = 'fb';
			}
			$tagHtml .= '<meta property="' . $namespace . ':' . $name . '" content="' . $value . '" />';
		}
		
		return $tagHtml;
	}
	
	function setType($type)
	{
		$this->tagArray['type'] = $type;
	}
	
	function setTitle($title)
	{
		$this->tagArray['title'] = $title;
	}
	
	function setImage($image)
	{
		$this->tagArray['image'] = $image;
	}
	
	function setUrl($url)
	{
		$this->tagArray['url'] = $url;
	}
	
	function setSetName($siteName)
	{
		$this->tagArray['site_name'] = $siteName;
	}
	
	function setAdmins($admins)
	{
		$this->tagArray['admins'] = $admins;
	}
	
	function setAppId($appId)
	{
		$this->tagArray['app_id'] = $appId;
	}
	
	function setDescription($description)
	{
		$this->tagArray['description'] = $description;
	}
}