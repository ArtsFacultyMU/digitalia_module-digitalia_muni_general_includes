<?php

namespace Drupal\digitalia_muni_json_dump\Plugin\Action;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides JSON dump for node.
 *
 * @Action(
 *	 id = "digitalia_muni_json_dump_node",
 *	 label = @Translation("Create JSON dump (node)"),
 *	 type = "node",
 *	 category = @Translation("Digitalia")
 * )
 */
class JsonDumpNodeAction extends JsonDumpActionBase implements ContainerFactoryPluginInterface {
	/**
	 * {@inheritdoc}
	 */
	public function access($node, AccountInterface $account = NULL, $return_as_object = FALSE) {
		$access = $node->access("update", $account, TRUE)
			->andIf($node->title->access("edit", $account, TRUE));
		return $return_as_object ? $access : $access->isAllowed();
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute($node = NULL) {
    if (!$node) {
      return;
    }

    $this->logger->notice("Pre parent execute");

    $this->executeGeneric($node);
  }
}
