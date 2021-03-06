<?php

namespace Craft;

/**
 * Dump Controller
 */
class DumpController extends BaseController
{
    protected $allowAnonymous = array('actionBackup');

    /**
     * Backup
     */
    public function actionBackup()
    {
        // check if plugin is installed
        if (!$plugin = craft()->plugins->getPlugin('dump'))
        {
            die('Could not find the plugin');
        }

        // get settings
        $settings = $plugin->getSettings();

        // get key
        $key = craft()->request->getParam('key');

        // verify key
        if (!$settings->key OR $key != $settings->key)
        {
            die('Unauthorised key');
        }

	    // Delete old backups if required
	    $filesDeleted = $this->_deleteOldBackups($settings->revisions);

        // run backup
        craft()->db->backup();

        // check if a redirect was posted
        if (craft()->request->getPost('redirect'))
        {
            $this->redirectToPostedUrl();
        }

        die('Success. Removed ' . $filesDeleted . ' old backups');
    }

	/**
	 * Delete old backups
     *
	 * @param int $revisions
	 * @return int
	 */
	private function _deleteOldBackups($revisions = null)
	{
		// If a number is not passed return
		if (!is_numeric($revisions))
        {
			return 0;
		}

    	$backupPath = craft()->path->getDbBackupPath();

		// Get a list of files in the backup directory and sort by descending order
		if ($files = scandir($backupPath, SCANDIR_SORT_DESCENDING))
        {
			// Remove 'x' from the beginning of the array
			$files = array_slice($files, ($revisions - 1));
			$i = 0;

			// Loop through any remaining files and delete them
			foreach($files as $file)
            {
				$filePath = $backupPath . $file;

				if (is_file($filePath))
                {
					unlink($filePath);
					$i++;
				}
			}

			return $i;
		}
	}
}
