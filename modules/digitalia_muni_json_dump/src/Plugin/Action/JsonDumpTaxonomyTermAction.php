<?php

namespace Drupal\digitalia_muni_json_dump\Plugin\Action;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides JSON dump for media.
 *
 * @Action(
 *	 id = "digitalia_muni_json_dump_taxonomy_term",
 *	 label = @Translation("Create JSON dump (taxonomy_term)"),
 *	 type = "taxonomy_term",
 *	 category = @Translation("Digitalia")
 * )
 */
class JsonDumpTaxonomyTermAction extends JsonDumpActionBase implements ContainerFactoryPluginInterface {
	/**
	 * {@inheritdoc}
	 */
	public function access($taxonomy_term, AccountInterface $account = NULL, $return_as_object = FALSE) {
		$access = $taxonomy_term->access('update', $account, TRUE)
			->andIf($taxonomy_term->name->access('edit', $account, TRUE));
		return $return_as_object ? $access : $access->isAllowed();
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute($taxonomy_term = NULL) {
    if (!$taxonomy_term) {
      return;
    }

    $this->executeGeneric($taxonomy_term);
  }
}
