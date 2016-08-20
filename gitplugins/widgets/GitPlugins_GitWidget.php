<?php
namespace Craft;

class GitPlugins_GitWidget extends BaseWidget
{
	public function getName()
	{
		return Craft::t('Download plugins from Github');
	}
	public function getBodyHtml()
	{
		return craft()->templates->render('gitplugins/GitPluginsWidget');
	}
	public function init()
	{
		// includeHeadHtml()
	}
}
