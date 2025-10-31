<?php

namespace Drupal\sync_roles_civi_bridge\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Sync Roles Bridge.
 */
class SyncRolesBridgeSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sync_roles_bridge_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sync_roles_civi_bridge.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('sync_roles_civi_bridge.settings');

    $form['custom_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CiviCRM Custom Field'),
      '#default_value' => $config->get('custom_field') ?: 'custom_64',
      '#description' => $this->t('Provide the CiviCRM custom field column name (e.g. custom_64) or numeric field ID where roles should be stored.'),
    ];

    $form['sync_all'] = [
      '#type' => 'submit',
      '#value' => $this->t('Sync All Users'),
      '#submit' => ['::syncAllUsersSubmit'],
      '#limit_validation_errors' => [],
      '#weight' => 100,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Custom submit handler for the "Sync All Users" button.
   */
  public function syncAllUsersSubmit(array &$form, FormStateInterface $form_state) {
    // Build a list of all user IDs (disable access checking).
    $query = \Drupal::entityQuery('user');
    $query->accessCheck(FALSE);
    $query->condition('uid', 0, '>'); // Exclude UID 0
    $uids = $query->execute();

    $operations = [];
    foreach ($uids as $uid) {
      $operations[] = ['sync_roles_bridge_batch_process', [$uid]];
    }

    // Use the module handler service to get the module path.
    $module_path = \Drupal::service('module_handler')->getModule('sync_roles_civi_bridge')->getPath();

    $batch = [
      'title' => $this->t('Syncing all user roles to CiviCRM...'),
      'operations' => $operations,
      'finished' => 'sync_roles_bridge_batch_finished',
      'file' => $module_path . '/sync_roles_civi_bridge.module',
    ];
    batch_set($batch);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('sync_roles_civi_bridge.settings')
      ->set('custom_field', $form_state->getValue('custom_field'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
