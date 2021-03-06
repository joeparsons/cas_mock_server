<?php

declare(strict_types = 1);

namespace Drupal\cas_mock_server;

/**
 * Contains helper methods related to CAS service tickets.
 */
class ServiceTicketHelper {

  /**
   * Returns a service ticket.
   *
   * @return string
   *   The service ticket.
   */
  public static function generateServiceTicket(): string {
    /** @var \Drupal\Component\Uuid\UuidInterface $uuid_service */
    $uuid_service = \Drupal::service('uuid');
    return 'ST-' . $uuid_service->generate();
  }

}
