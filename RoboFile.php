<?php

use Robo\Contract\ConfigAwareInterface;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Triple store setup commands.
 */
class RoboFile extends \Robo\Tasks implements ConfigAwareInterface {

  use \Robo\Common\ConfigAwareTrait;

  /**
   * Set default command options.
   *
   * @hook option *
   */
  public function setDefaultOptions(Command $command, AnnotationData $annotationData) {
    // Get default values from env variables, if any.
    $import_dir = getenv('IMPORT_DIR') ?: './import';
    $host = getenv('DBA_HOST') ?: 'http://localhost:8890';
    $username = getenv('DBA_USERNAME') ?: 'dba';
    $password = getenv('DBA_PASSWORD') ?: 'dba';

    // Set default command options.
    $command->addOption('import-dir', '', InputOption::VALUE_OPTIONAL, 'Data import directory.', $import_dir);
    $command->addOption('host', '', InputOption::VALUE_OPTIONAL, 'Virtuoso backend host.', $host);
    $command->addOption('username', '', InputOption::VALUE_OPTIONAL, 'Virtuoso backend username.', $username);
    $command->addOption('password', '', InputOption::VALUE_OPTIONAL, 'Virtuoso backend password.', $password);
  }

  /**
   * Fetch data.
   *
   * @command fetch
   */
  public function fetch() {
    $tasks = [];
    $tasks[] = $this->taskFilesystemStack()->mkdir($this->input->getOption('import-dir'));
    foreach ($this->config->get('data') as $datum) {
      // Fetch raw RDF file source.
      $tasks[] = $this->taskExec('wget')->option('-O', $this->getFilePath($datum))->arg($datum['url']);
      // Create graph IRI file for import.
      $tasks[] = $this->taskWriteToFile($this->getFilePath($datum, 'rdf.graph'))->text($datum['graph']);

      // If a ZIP archive extract it and move content to its final destination.
      if ($datum['format'] === 'zip') {
        $tasks[] = $this->taskExtract($this->getFilePath($datum))->to($datum['name']);
        $tasks[] = $this->taskFilesystemStack()->copy($datum['name'].'/'.$datum['file'], $this->getFilePath($datum, 'rdf'));
        $tasks[] = $this->taskFilesystemStack()->remove($datum['name']);
      }
    }

    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Import data.
   *
   * @command import
   */
  public function import() {
    $directory = $this->input->getOption('import-dir');
    return $this->taskRunQueries([
      "ld_dir('{$directory}', '*.rdf', NULL);",
      "rdf_loader_run();",
      "exec('checkpoint');",
      "WAIT_FOR_CHILDREN;",
    ]);
  }

  /**
   * Purge data.
   *
   * @command purge
   */
  public function purge() {
    return $this->taskRunQueries([
      'DELETE FROM DB.DBA.load_list;',
      'DELETE FROM DB.DBA.RDF_QUAD;',
    ]);
  }

  /**
   * Run list of queries via isql-v.
   *
   * @param array $queries
   *    Queries to be executed.
   *
   * @return \Robo\Collection\CollectionBuilder
   *    Task collection.
   */
  private function taskRunQueries(array $queries) {
    $host = $this->input->getOption('host');
    $username = $this->input->getOption('username');
    $password = $this->input->getOption('password');

    $tasks = [];
    $tasks[] = $this->taskWriteToFile('query.sql')->append(TRUE)->lines($queries);
    $tasks[] = $this->taskExec("cat query.sql");
    $tasks[] = $this->taskExec("isql-v -H {$host} -U {$username} -P {$password} < query.sql");
    $tasks[] = $this->taskFilesystemStack()->remove('query.sql');

    return $this->collectionBuilder()->addTaskList($tasks);
  }

  /**
   * Get full file path.
   *
   * @param array $datum
   *    File properties as in robo.yml.
   * @param string $format
   *    File format extension, i.e. "rdf", "zip", etc.
   *
   * @return string
   *    Full file path.
   */
  private function getFilePath(array $datum, $format = '') {
    $format = empty($format) ? $datum['format'] : $format;
    return $this->input->getOption('import-dir')."/{$datum['name']}.{$format}";
  }

}
