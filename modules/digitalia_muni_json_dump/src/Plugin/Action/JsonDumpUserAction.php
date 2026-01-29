<?php

namespace Drupal\digitalia_muni_general_includes\Plugin\Action;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides JSON dump for media.
 * TODO: create json_dump media as a module install configuration
 * TODO: create view as a module install configuration
 *
 * @Action(
 *	 id = "digitalia_muni_json_dump_user",
 *	 label = @Translation("Create JSON dump (user)"),
 *	 type = "user",
 *	 category = @Translation("Digitalia")
 * )
 */
class JsonDumpUserAction extends JsonDumpActionBase implements ContainerFactoryPluginInterface {
	/**
	 * {@inheritdoc}
	 */
	public function access($user, AccountInterface $account = NULL, $return_as_object = FALSE) {
		$access = $user->access('update', $account, TRUE)
			->andIf($user->name->access('edit', $account, TRUE));
		return $return_as_object ? $access : $access->isAllowed();
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute($user = NULL) {
    if (!$user) {
      return;
    }

    $this->executeGeneric($user);
  }
}
