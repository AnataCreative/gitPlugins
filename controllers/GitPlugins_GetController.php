<?php
namespace Craft;

class GitPlugins_getController extends BaseController
{
	public function actionGetPlugin()
	{
		$error = craft()->getPlugins_get->getPlugin();

		if ($error) {
			craft()->userSession->setNotice(Craft::t($error));
		} else {
			craft()->userSession->setNotice(Craft::t('Plugin downloaded'));
		}
	}
}
