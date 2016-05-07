<?php
namespace Craft;

class GitPlugins_GitWidget extends BaseWidget
{
    public function getName()
    {
        return Craft::t('Download Github Plugins');
    }

    public function getBodyHtml()
    {
        return craft()->templates->render('gitPlugins/gitPluginsWidget');
    }
}
