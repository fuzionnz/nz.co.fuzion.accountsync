<?php

/**
 * Collection of upgrade steps
 */
class CRM_Accountsync_Upgrader extends CRM_Accountsync_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1000() {
    $this->ctx->log->info('Applying update 1000');
    CRM_Core_DAO::executeQuery("
ALTER TABLE `civicrm_account_contact`
  ADD COLUMN `connector_id` INT NULL COMMENT 'ID of connector. Relevant to connect to more than one account of the same type' AFTER `accounts_needs_update`,
  DROP INDEX `account_system_id`,
  ADD UNIQUE INDEX `account_system_id` (`accounts_contact_id`, `connector_id`, `plugin`),
  DROP INDEX `contact_id_plugin`,
  ADD UNIQUE INDEX `contact_id_plugin` (`contact_id`, `connector_id`, `plugin`);
");

    CRM_Core_DAO::executeQuery("
  ALTER TABLE `civicrm_account_invoice`
  ADD COLUMN `connector_id` INT NULL COMMENT 'ID of connector. Relevant to connect to more than one account of the same type' AFTER `accounts_needs_update`,
  DROP INDEX `account_system_id`,
  ADD UNIQUE INDEX `account_system_id` (`accounts_invoice_id`, `connector_id`, `plugin`),
  DROP INDEX `invoice_id_plugin`,
  ADD UNIQUE INDEX `invoice_id_plugin` (`contribution_id`, `connector_id`, `plugin`)
    ");
    return TRUE;
  }

  /**
   * Example: Run a couple simple queries
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1100() {
    $this->ctx->log->info('Applying update 1100');
    CRM_Core_DAO::executeQuery("
ALTER TABLE `civicrm_account_contact`
  ADD COLUMN `do_not_sync` TINYINT(4) DEFAULT 0 COMMENT 'Do not sync this contact' AFTER `accounts_needs_update`
");
    return TRUE;
  }

  /**
   * Change accounts_status_id to have a default of 0.
   *
   * It was previously NULL but then a query like accounts_status_id IN ()
   *  would not get unset rows.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1200() {
    $this->ctx->log->info('Applying update 1200');
    CRM_Core_DAO::executeQuery("
ALTER TABLE `civicrm_account_invoice`
  ALTER COLUMN `accounts_status_id` SET DEFAULT 0
");
    return TRUE;
  }
  
  /**
   * Change existing accounts_status_id to 0 for NULL values.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_1300() {
    $this->ctx->log->info('Applying update 1300');
    CRM_Core_DAO::executeQuery("UPDATE `civicrm_account_invoice` SET `accounts_status_id` = 0 WHERE `accounts_status_id` IS NULL");
    return TRUE;
  }

  /**
   * Example: Run an external SQL script
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
