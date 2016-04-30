<?php
namespace Craft;

const CRAFT_FOLDER = __DIR__.'/../../..';
const CRAFT_PLUGIN_FOLDER = CRAFT_FOLDER.'/plugins';
const UPLOAD_FOLDER = CRAFT_FOLDER."/storage/uploads/pluginuploader";
const CRAFT_CONFIG_GENERAL = CRAFT_FOLDER.'/config/general.php';
const CRAFT_PUBLIC_INDEX = __DIR__.'/../../../../public/index.php';

class GitPlugins_GetService extends BaseApplicationComponent
{

	public function getPlugin($url)
	{
		// Initial state
		$error = false;

		// Check url
		$error = $this->checkUrl($url);

		// Return
		return $error;
	}



	/**
	* @param string $url
	*/
	public function checkUrl($url)
	{
		/* Check url
			- has .zip at the end? -> OK,
				else ->
				- Remove trailing / if there is one
				- Add /archive/master.zip
			- Determin $zipTarget
			- Determin $folderTarget
		*/

		// Is Github url?
		if (strpos($url, 'github') === false) {
			$error = 'Please enter a Github URL';
			return $error;
		}

		// Determin Download Url
		if (strpos($url, '.zip')) {
			// Set
			$downloadUrl = $url;
		} else {
			// Set
			$downloadUrl = $url;
			if (substr($downloadUrl, -1) !== '/') {
				$downloadUrl = $downloadUrl + '/';
			}
			$downloadUrl = $downloadUrl + 'archive/master.zip';
		}

		// Get filename
		$content = get_headers($url, 1);
		$content = array_change_key_case($content, CASE_LOWER);

		if ($content['content-disposition']) {
			$tmp_name = explode('=', $content['content-disposition']);

			if ($tmp_name[1]) {
				$realfilename = trim($tmp_name[1],'";\'');
			}
		} else  {
			$stripped_url = preg_replace('/\\?.*/', '', $url);
			$realfilename = basename($stripped_url);
		}

		// Determin Ziptarget
		$ziptarget = UPLOAD_FOLDER.'/'.$realfilename;

		// Determin Foltertarget
		$folderTarget = UPLOAD_FOLDER.'/'.str_replace('.zip', '', $realfilename);

		// Download
		$error = $this->download($zipTarget, $folderTarget, $downloadUrl);
		// $error = $this->download(UPLOAD_FOLDER.'/ImageResizer-master.zip', UPLOAD_FOLDER.'/ImageResizer-master/', 'https://github.com/engram-design/ImageResizer/archive/master.zip');

		// return
		return $error;
	}



	/**
	* @param string $zipTarget
	* @param string $folderTarget
	* @param string $downloadUrl
	*/
	public function download($zipTarget, $folderTarget, $downloadUrl)
	{
		file_put_contents($zipTarget,
			file_get_contents($downloadUrl)
		);

		$error = $this->extract($zipTarget, $folderTarget);

		return $error;
	}


	/**
	* @param string $zipTarget
	* @param string $folderTarget
	*/
	public function extract($zipTarget, $folderTarget)
	{
		$date = new DateTime();
		$now = $date->getTimestamp();
		$zip = new \ZipArchive();
		$res = $zip->open($zipTarget);

		if ($res === TRUE) {
			$zip->extractTo(UPLOAD_FOLDER);
			$zip->close();

			$error = $this->move($folderTarget, $zipTarget);
		} else {
			$error = "Can't extract zip";
		}

		return $error;
	}


	/**
	* @param string $uploadFolder
	* @param string $uploadZipFile
	*/
	public function move($uploadFolder, $uploadZipFile)
	{
		$pluginExtractFile = '';
		$pluginExtractFolder = '';

		// Find the folder that the Plugin.php file is in. That is the root of the plugin.
		foreach (glob($uploadFolder."/*Plugin.php") as $filename) {
			$pluginExtractFile = $filename;
			$pluginExtractFolder = dirname($filename);
		}

		if ($pluginExtractFile === '') {
			foreach (glob($uploadFolder."/**/*Plugin.php") as $filename) {
				$pluginExtractFile = $filename;
				$pluginExtractFolder = dirname($filename);
			}
		}

		// Open the file
		$fp = @fopen($pluginExtractFile, 'r');
		if ($fp) {
			$array = explode("\n", fread($fp, filesize($pluginExtractFile)));

			$pluginName = '';

			// Get name of plugin
			foreach ($array as $line) {
				if (strpos($line, 'class') !== false && strpos($line, 'extends') !== false) {
					$split = explode(" ", $line);
					$pluginName = substr($split[1], 0, -6);
					break;
				}
			}

			// Copy to craft/plugins folder.
			$pluginInstallFolder = CRAFT_PLUGIN_FOLDER.'/'.strtolower($pluginName);

			if (!file_exists($pluginInstallFolder)) {
				// Copy folder to craft/plugins
				$this->recurse_copy($pluginExtractFolder, $pluginInstallFolder);

				// Remove zipfile and folder from uploads
				$this->rrmdir($uploadFolder);
				unlink($uploadZipFile);
			}
			else {
				craft()->userSession->setNotice(Craft::t('A plugin with the same name (".$pluginName.") is already uploaded.'));
			}

			return false;
		} else {
			craft()->userSession->setNotice(Craft::t('The uploaded file is not a valid plugin.'));
		}
	}


	/**
	* @param string $dir
	*/
	private function rrmdir($dir)
	{
		foreach (glob($dir.'/{,.}[!.,!..]*', GLOB_MARK|GLOB_BRACE) as $file) {
			if (is_dir($file)) {
				$this->rrmdir($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dir);
	}


	/**
	* @param string $src
	* @param string $dst
	*/
	private function recurse_copy($src, $dst)
	{
		$dir = opendir($src);
		@mkdir($dst);

		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if ( is_dir($src . '/' . $file) ) {
					$this->recurse_copy($src . '/' . $file,$dst . '/' . $file);
				} else {
					copy($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}

		closedir($dir);
	}


	/**
	* @param string $to
	*/
	protected function move_uploaded_file($from, $to)
	{
		return move_uploaded_file($from, $to);
	}
}
