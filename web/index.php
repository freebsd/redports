<?php

/**
 * redports is a continuous integration platform for FreeBSD ports.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
namespace Redports\Web;

require_once __DIR__.'/vendor/autoload.php';

$session = new Session();

$app = new \Slim\App(new \Slim\Container(Config::get('slimconfig')));

/* init php-view */
$container = $app->getContainer();
$container['view'] = function($container) {
   return new \Slim\Views\PhpRenderer(__DIR__.'/templates/');
};

/* landing page */
$app->get('/', function($request, $response, $args) use ($session) {
   return $this->view->render($response, 'index.html', $args);
});

/* GitHub OAuth login */
$app->get('/login', function($request, $response) use ($session) {
   $credentials = new \OAuth\Common\Consumer\Credentials(
      Config::get('github.oauth.key'),
      Config::get('github.oauth.secret'),
      Config::get('github.oauth.redirecturl')
   );

   $serviceFactory = new \OAuth\ServiceFactory();
   $gitHub = $serviceFactory->createService('GitHub', $credentials,
      new \OAuth\Common\Storage\Session(), array(
         'user:email',
         'public_repo',
         'repo:status',
         'admin:repo_hook',
      )
   );

   $queryParams = $request->getQueryParams();

   if (isset($queryParams['code'])) {
       try {
           $token = $gitHub->requestAccessToken($queryParams['code']);

           $result = json_decode($gitHub->request('user'), true);

         /* TODO: register new user at master if it does not exist yet */

         $session->login($result['login']);
           $_SESSION['name'] = $result['name'];
           $_SESSION['profile_url'] = $result['html_url'];
           $_SESSION['token'] = $token->getAccessToken();

           return $response->withRedirect('/repositories');
       } catch (\OAuth\Common\Http\Exception\TokenResponseException $e) {
           return $response->withStatus(500)
                         ->write($e->getMessage());
       }
   } else {
       return $response->withRedirect($gitHub->getAuthorizationUri());
   }
});

/* GitHub list repositories */
$app->get('/repositories', function($request, $response) use ($session) {
   if (!$session->getUsername()) {
       return $response->withStatus(403)->write('Not authenticated');
   }

   $client = new \Github\Client();
   $client->authenticate($_SESSION['token'], null, \Github\Client::AUTH_HTTP_TOKEN);

   try {
       $repos = $client->api('user')->repositories($session->getUsername());

       foreach ($repos as $key => $repository) {
           $repos[$key]['redports_enabled'] = false;

           foreach ($client->api('repo')->hooks()->all($session->getUsername(), $repository['name']) as $hook) {
               if ($hook['name'] == 'web' && strpos($hook['config']['url'], 'redports.org') !== false) {
                   $repos[$key]['redports_enabled'] = true;
               }
           }
       }
   } catch (\Github\Exception\RuntimeException $e) {
       return $response->withStatus(500)
                      ->write($e->getMessage());
   }

   return $this->view->render($response, 'repositories.html', array('repositories' => $repos));
});

/* GitHub repository setup */
$app->get('/repositories/{repository}/install', function($request, $response, $args) use ($session) {
   if (!$session->getUsername()) {
       return $response->withStatus(403)->write('Not authenticated');
   }

   $client = new \Github\Client();
   $client->authenticate($_SESSION['token'], null, \Github\Client::AUTH_HTTP_TOKEN);

   try {
       foreach ($client->api('repo')->hooks()->all($session->getUsername(), $args['repository']) as $hook) {
           if ($hook['name'] == 'web' && strpos($hook['config']['url'], 'redports.org') !== false) {
               $client->api('repo')->hooks()->remove($session->getUsername(), $args['repository'], $hook['id']);
           }
       }

       $webhook = $client->api('repo')->hooks()->create($session->getUsername(), $args['repository'],
         array(
            'name'   => 'web',
            'active' => true,
            'events' => array(
               'push',
            ),
            'config' => array(
               'url'          => 'https://api.redports.org/github/',
               'content_type' => 'json',
            ),
         )
      );
   } catch (\Github\Exception\RuntimeException $e) {
       return $response->withStatus(500)
                      ->write($e->getMessage());
   }

   /* TODO: register repository at master */

   return $response->withRedirect('/repositories');
});

/* GitHub repository uninstall */
$app->get('/repositories/{repository}/uninstall', function($request, $response, $args) use ($session) {
   if (!$session->getUsername()) {
       return $response->withStatus(403)->write('Not authenticated');
   }

   $client = new \Github\Client();
   $client->authenticate($_SESSION['token'], null, \Github\Client::AUTH_HTTP_TOKEN);

   try {
       foreach ($client->api('repo')->hooks()->all($session->getUsername(), $args['repository']) as $hook) {
           if ($hook['name'] == 'web' && strpos($hook['config']['url'], 'redports.org') !== false) {
               $client->api('repo')->hooks()->remove($session->getUsername(), $args['repository'], $hook['id']);
           }
       }
   } catch (\Github\Exception\RuntimeException $e) {
       return $response->withStatus(500)
                      ->write($e->getMessage());
   }

   return $response->withRedirect('/repositories');
});

$app->run();
