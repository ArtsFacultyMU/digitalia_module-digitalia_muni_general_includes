<?php

namespace Drupal\digitalia_muni_json_dump\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileExists;
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
    $module_handler = \Drupal::service('module_handler');
    $serializer = \Drupal::service("serializer");

    #$time_start = hrtime(true);
    #$json = $serializer->serialize($entity, "json");
    #$time_end = hrtime(true);
    #\Drupal::logger("TIMING_DEBUG_SERIALIZE")->debug(($time_end-$time_start)/1000000);
    #$time_start = hrtime(true);
    #$json = $serializer->serialize($entity, "json");
    #$time_end = hrtime(true);
    #\Drupal::logger("TIMING_DEBUG_SERIALIZE")->debug(($time_end-$time_start)/1000000);
    #$time_start = hrtime(true);
    #$json = $serializer->serialize($entity, "json");
    #$time_end = hrtime(true);
    #\Drupal::logger("TIMING_DEBUG_SERIALIZE")->debug(($time_end-$time_start)/1000000);
    #$time_start = hrtime(true);
    #$json = $serializer->serialize($entity, "json");
    #$time_end = hrtime(true);

    $time_start = hrtime(true);
    $json_array = $serializer->normalize($entity);

    #$time_start = hrtime(true);
    #$json = $this->getRenderedViewMarkup("dm_json_dump_{$entity_type}", "data_export_1", $id);

    #$time_start = hrtime(true);
    #$json = $this->getRenderedViewMarkup("dm_json_dump_{$entity_type}", "data_export_1", $id);
    #$time_end = hrtime(true);
    #\Drupal::logger("TIMING_DEBUG_VIEW")->debug(($time_end-$time_start)/1000000);
    #$time_start = hrtime(true);
    #$json = $this->getRenderedViewMarkup("dm_json_dump_{$entity_type}", "data_export_1", $id);
    #$time_end = hrtime(true);
    #\Drupal::logger("TIMING_DEBUG_VIEW")->debug(($time_end-$time_start)/1000000);
    #$time_start = hrtime(true);
    #$json = $this->getRenderedViewMarkup("dm_json_dump_{$entity_type}", "data_export_1", $id);
    #$time_end = hrtime(true);
    #\Drupal::logger("TIMING_DEBUG_VIEW")->debug(($time_end-$time_start)/1000000);
    #$time_start = hrtime(true);
    #$json = $this->getRenderedViewMarkup("dm_json_dump_{$entity_type}", "data_export_1", $id);
    #$time_end = hrtime(true);
    #\Drupal::logger("TIMING_DEBUG_VIEW")->debug(($time_end-$time_start)/1000000);


    // Add relationships to dump
    if ($module_handler->moduleExists("group")) {
      #$decoded = json_decode($json, TRUE);
      #$json_array = json_decode($json, TRUE);
      $relationships = \Drupal::entityTypeManager()->getStorage("group_relationship")->loadByProperties(["entity_id" => $id]);

      $relationships_serialized = array();

      foreach ($relationships as $relationship) {
        $relationship_bundle = array_pop(explode(":", $relationship->get("plugin_id")->getValue()[0]["value"]));

        // Other entities should be ok (nodes are for sure)
        if ($relationship_bundle == "group_membership") {
          $relationship_bundle = "user";
        }

        $tmp_array = json_decode($serializer->serialize($relationship, "json"), TRUE);

        // We want only relationships for the same entity type as input entity
        // For the rare case when for example a taxonomy term with same id as node is also a member of a group
        if ($entity->bundle() == $relationship_bundle) {
          array_push($relationships_serialized, json_decode($serializer->serialize($relationship, "json"), TRUE));
        }
      }

      $json_array = array_merge($json_array, $relationships_serialized);
      #$json = json_encode($json_array);
    }

    $json = json_encode($json_array);
    $time_end = hrtime(true);
    $time_elapsed = ($time_end-$time_start)/1000000;
    \Drupal::logger("TIMING_DEBUG_END_VIEW")->debug("{$entity->id()}: {$time_elapsed}");
    #\Drupal::logger("TIMING_DEBUG_END_VIEW")->debug(($time_end-$time_start)/1000000);

    $time_start = hrtime(true);
    $json_file_id = $this->saveToFile($json, $dest_dir_uri, $file_name);
    $time_end = hrtime(true);
    \Drupal::logger("TIMING_DEBUG_SAVE_TO_FILE_VIEW")->debug(($time_end-$time_start)/1000000);

    if (!$json_file_id) {
      return FALSE;
    }

    $props = ["field_reference_{$entity_type}" => $id];
    $existing_dumps = \Drupal::entityTypeManager()->getStorage("media")->loadByProperties($props);

    if (empty($existing_dumps)) {
      return $this->createJsonDumpMedia($entity_type, $id, $file_name, $json_file_id);
    }

    return $this->rewriteExistingDumpMedia($existing_dumps, $id, $file_name, $json_file_id);
  }

  protected function saveToFile($json, $dest_dir_uri, $file_name) {
    $file_uri = "{$dest_dir_uri}/{$file_name}";

    if (!\Drupal::service("file_system")->prepareDirectory($dest_dir_uri, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
		  $this->logger->error("Could not prepare directory at: {$dest_dir_uri}");
      return FALSE;
    }

    $file_uri = \Drupal::service("file_system")->saveData($json, $file_uri, FileExists::Replace);
    if (!$file_uri) {
		  $this->logger->error("Could not create file at: {$dest_dir_uri}/{$file_name}");
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

  /**
   * Rewriting causes the file to be visible to everyone? Yes, original file is no longer referenced
   * from any media, therefore it can't inherit permission checks.
   * Removing file entity from Drupal only works
   */
  protected function rewriteExistingDumpMedia($existing_dumps, $entity_id, $file_name, $json_file_id) {
    if (count($existing_dumps) > 1) {
      $entity_type = $this->getPluginDefinition()["type"];
      $this->logger->warning("{$entity_type} {$entity_id} has more than one JSON dump media!");
    }

    // Delete original file only from drupal (the uri stays the same, versioning is left up to fedora)
    $original_id = $existing_dumps[array_keys($existing_dumps)[0]]->get("field_media_file")->getValue()[0]["target_id"];
    $props = ["fid" => $original_id];
    $file_query= \Drupal::entityTypeManager()->getStorage("file")->loadByProperties($props);
    $original_file = $file_query[array_keys($file_query)[0]];
    $original_file->setFileUri("");
    $original_file->delete();

    $existing_dumps[array_keys($existing_dumps)[0]]->set("field_media_file", [
            "target_id" => $json_file_id,
            "title" => $file_name,
    ]);

    return $existing_dumps[array_keys($existing_dumps)[0]]->save();
  }

}
