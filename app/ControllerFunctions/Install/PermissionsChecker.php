<?php

namespace App\ControllerFunctions\Install;

class PermissionsChecker
{
	/**
	 * @var array
	 */
	protected $results = [];

	/**
	 * Set the result array permissions and errors.
	 *
	 * @return mixed
	 */
	public function __construct()
	{
		$this->results['permissions'] = [];
		$this->results['errors'] = null;
	}

	/**
	 * Check for the folders permissions.
	 *
	 * @param array $folders
	 *
	 * @return array
	 */
	public function check(array $folders)
	{
		foreach ($folders as $folder => $permission) {
			$this->addFile($folder, $permission, $this->getPermission($folder, $permission));
		}

		return $this->results;
	}

	/**
	 * Get a folder permission.
	 *
	 * @param string $folder
	 * @param string $permissions
	 *
	 * @return int the position of 1 determines the errors
	 */
	private function getPermission(string $folder, string $permissions)
	{
		$return = 0;
		foreach (explode('|', $permissions) as $permission) {
			preg_match('/(!*)(.*)/', $permission, $f);
			$return <<= 1;
			$return |= !($f[2](base_path($folder)) xor ($f[1] == '!'));
		}

		return $return;
	}

	/**
	 * Add the file to the list of results.
	 *
	 * @param $folder
	 * @param $permission
	 * @param $isSet
	 */
	private function addFile($folder, $permission, $isSet)
	{
		array_push($this->results['permissions'], [
			'folder' => $folder,
			'permission' => $this->map_perm_set($permission, $isSet),
			'isSet' => $isSet,
		]);

		// set error if $isSet is positive
		if ($isSet > 0) {
			// @codeCoverageIgnoreStart
			$this->results['errors'] = true;
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 *  map.
	 */
	private function map_perm_set($permissions, $areSet)
	{
		$array_permission = array_reverse(explode('|', $permissions));
		$ret = [];
		$i = 0;
		foreach ($array_permission as $perm) {
			$perm = str_replace('file_', '', $perm);
			$perm = str_replace('!', 'not', $perm);
			$perm = str_replace('is_', ' ', $perm);
			$ret[$i++] = [$perm, $areSet & 1];
			$areSet >>= 1;
		}

		return $ret;
	}
}