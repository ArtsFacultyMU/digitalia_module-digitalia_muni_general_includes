<?php

namespace Drupal\digitalia_muni_json_dump\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class JsonDumpActionBase extends ActionBase {
	/**
	 * The Messenger service.
	 *
	 * @var \Drupal\Core\Messenger\MessengerInterface
	 */
	protected $messenger;

	/**
	 * Logger service.
	 *
	 * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
	 */
	protected $logger;

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		$instance = new static($configuration, $plugin_id, $plugin_definition);
		$instance->logger = $container->get('logger.factory')->get('digitalia_muni_general_includes');
		$instance->messenger = $container->get('messenger');
		return $instance;
	}

  /**
   * Expects existing entity
   */
  protected function executeGeneric($entity) {
    $id = $entity->id();
    $entity_type = $this->getPluginDefinition()["type"];
    $dest_dir_uri = "fedora://json-dump/{$entity_type}";
    $file_name = "{$entity_type}_{$id}.json";

    $json = $this->getRenderedViewMarkup("dev_json_dump_{$entity_type}", "data_export_1", $id);
    $json_file_id = $this->saveToFile($json, $dest_dir_uri, $file_name);

    if (!$json_file_id) {
      return FALSE;
    }

    $props = ["field_reference_{$entity_type}" => $id];
    $existing_dumps = \Drupal::entityTypeManager()->getStorage("media")->loadByProperties($props);

    if (empty($existing_dumps)) {
      return $this->createJsonDumpMedia($entity_type, $id, $file_name, $json_file_id);
    }

    return $this->rewriteExistingDumpMedia($existing_dumps, $id, $json_file_id, $file_name);
  }

  protected function saveToFile($json, $dest_dir_uri, $file_name) {
    $file_uri = "{$dest_dir_uri}/{$file_name}";

    if (!\Drupal::service("file_system")->prepareDirectory($dest_dir_uri, FileSystemInterface::CREATE_DIRECTORY)) {
		  $this->logger->error("Could not prepare directory at: {$dest_dir_uri}");
      return FALSE;
    }

    if (!\Drupal::service("file_system")->saveData($json, $file_uri, FileSystemInterface::EXISTS_REPLACE)) {
		  $this->logger->error("Could not create file at: {$file_uri}");
      return FALSE;
    }

    $json_file = File::create(["uri" => $file_uri]);
    $json_file->setOwnerId(1);
    $json_file->setPermanent();
    $json_file->save();
    
    return $json_file->id();
  }

  protected function getRenderedViewMarkup($view_machine_name, $display, $id) {
    $view = \Drupal\views\Views::getView($view_machine_name);
    $view->setDisplay($display);
    $view->setArguments(array($id));
    $view->execute();

    return $view->render()["#markup"];
  }

  protected function createJsonDumpMedia($type, $entity_id, $file_name, $file_id) {
    $json_dump_media = Media::create([
      "name" => "{$type}_{$entity_id}",
      "bundle" => "json_dump",
      "status" => 0,
      "field_media_file" => [
        "target_id" => $file_id,
        "title" => $file_name,
      ],
      "field_reference_{$type}" => $entity_id,
    ]);

    return $json_dump_media->save();
  }

  protected function rewriteExistingDumpMedia($existing_dumps, $entity_id, $json_file_id, $file_name) {
    if (count($existing_dumps) > 1) {
      $entity_type = $this->getPluginDefinition()["type"];
      $this->logger->warning("{$entity_type} {$entity_id} has more than one JSON dump media!");
    }
    // TODO: Do we want to rewrite existing media? If yes, maybe do some more sophisticated selection
    $existing_dumps[array_keys($existing_dumps)[0]]->set("field_media_file", ["target_id" => $json_file_id, "title" => $file_name]);
    return $existing_dumps[array_keys($existing_dumps)[0]]->save();
  }

}
