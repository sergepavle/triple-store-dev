<?php

use Robo\Contract\ConfigAwareInterface;

/**
 * Triple store setup commands.
 */
class RoboFile extends \Robo\Tasks implements ConfigAwareInterface {

  use \Robo\Common\ConfigAwareTrait;

  /**
   * Fetch data.
   *
   * @command fetch
   */
  public function fetch() {
    $collection = $this->collectionBuilder();
    foreach ($this->getConfig()->get('data') as $datum) {
      $task = $this->taskExec('wget')
        ->option('-O', "/tmp/{$datum['name']}.rdf")
        ->arg($datum['url']);
      $collection->addTask($task);
    }
    return $collection->run();
  }

  /**
   * Import data.
   *
   * @command import
   */
  public function import() {
    $baseUrl = $this->getConfig()->get('backend.base_url');
    $collection = $this->collectionBuilder();
    foreach ($this->getConfig()->get('data') as $datum) {
      $task = $this->taskExec('curl')
        ->option('digest')
        ->option('verbose')
        ->option('user', 'dba:dba')
        ->option('url', "{$baseUrl}/sparql-graph-crud-auth?graph-uri={$datum['graph']}")
        ->option('-T', "/tmp/{$datum['name']}.rdf");
      $collection->addTask($task);
    }
    return $collection->run();
  }

  /**
   * Purge data.
   *
   * @command purge
   */
  public function purge() {
    $this->_exec("echo 'DELETE FROM DB.DBA.RDF_QUAD;' | isql-v -U dba -P dba >/dev/null");
  }

}
