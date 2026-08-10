<?php

namespace Drupal\digitalia_muni_json_dump\Plugin\Action;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides JSON dump for media.
 *
 * @Action(
 *	 id = "digitalia_muni_json_dump_media",
 *	 label = @Translation("Create JSON dump (media)"),
 *	 type = "media",
 *	 category = @Translation("Digitalia")
 * )
 */
class JsonDumpMediaAction extends JsonDumpActionBase implements ContainerFactoryPluginInterface {
	/**
	 * {@inheritdoc}
	 */
	public function access($media, AccountInterface $account = NULL, $return_as_object = FALSE) {
		$access = $media->access('update', $account, TRUE)
			->andIf($media->name->access('edit', $account, TRUE));
		return $return_as_object ? $access : $access->isAllowed();
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute($media = NULL) {
    if (!$media) {
      return;
    }

    if ($media->bundle() == "json_dump") {
      $this->logger->warning("Skipping media {$media->id()} to prevent dumps of dumps.");
      return;
    }

    $this->executeGeneric($media);
  }
}
