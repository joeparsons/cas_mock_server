<?php

declare(strict_types = 1);

namespace Drupal\cas_mock_server\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a custom menu link that leads to the CAS login form.
 */
class CasLoginMenuLink extends MenuLinkDefault {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Creates a new menu link instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $static_override
   *   The static override storage.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, StaticMenuLinkOverridesInterface $static_override, AccountInterface $current_user, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $static_override);

    $this->currentUser = $current_user;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_link.static.overrides'),
      $container->get('current_user'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isDeletable(): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(): bool {
    return $this->currentUser->isAnonymous();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return ['user.roles:authenticated'];
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'cas.login';
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    // Pass the current path (including query arguments) as the 'returnto' query
    // argument so that the CAS service can return the user to the current path
    // after authenticating.
    $request = $this->requestStack->getCurrentRequest();

    // If a 'destination' query argument exists, then this is what the user
    // should return to.
    if ($request->query->has('destination')) {
      $return_to = Url::fromUserInput($request->query->get('destination'))->setAbsolute()->toString();
    }
    else {
      $query_arguments = $request->query->all();
      $route_match = RouteMatch::createFromRequest($request);
      $route_name = $route_match->getRouteName();
      $route_parameters = $route_match->getRawParameters()->all();
      $route_options = ['query' => $query_arguments];
      $return_to = (new Url($route_name, $route_parameters, $route_options))->setAbsolute()->toString();
    }

    $options = parent::getOptions();
    $options['query']['returnto'] = $return_to;

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteLink() {
  }

}
