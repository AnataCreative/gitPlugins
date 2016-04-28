<?php
namespace Craft;

class GitPluginsPlugin extends BasePlugin
{
	function getName()
	{
		 return Craft::t('gitPlugins');
	}

	function getVersion()
	{
		return '1.0';
	}

	function getDeveloper()
	{
		return 'Anata Creative';
	}

	function getDeveloperUrl()
	{
		return 'https://anatacreative.com/';
	}

	public function onBeforeInstall()
	{
		if (!file_exists('../craft/storage/uploads/gitPlugins')) {
			mkdir('../craft/storage/uploads/gitPlugins', 0777, true);
		}
	}
}
