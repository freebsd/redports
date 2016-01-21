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

$app = new \Slim\App();

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
         'admin:repo_hook'
      )
   );

   $queryParams = $request->getQueryParams();

   if(isset($queryParams['code'])) {
      try {
         $token = $gitHub->requestAccessToken($queryParams['code']);

         $result = json_decode($gitHub->request('user'), true);

         /* TODO: register new user at master if it does not exist yet */

         $session->login($result['login']);
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
   if(!$session->getUsername())
      return $response->withStatus(403)->write('Not authenticated');

   $content = '';
   $client = new \Github\Client();
   $client->authenticate($_SESSION['token'], null, \Github\Client::AUTH_HTTP_TOKEN);

   foreach($client->api('user')->repositories($session->getUsername()) as $repository){
      $content .= sprintf('<b>%s</b> <a href="/repositories/%s/setup">setup</a><br>%s<br><br>',
         $repository['full_name'], $repository['name'], $repository['description']);
   }

   return $response->withHeader('Content-Type', 'text/html')->write($content);
});

/* GitHub repository setup */
$app->get('/repositories/{repository}/setup', function($request, $response, $args) use ($session) {
   if(!$session->getUsername())
      return $response->withStatus(403)->write('Not authenticated');

   $client = new \Github\Client();
   $client->authenticate($_SESSION['token'], null, \Github\Client::AUTH_HTTP_TOKEN);

   foreach($client->api('repo')->hooks()->all($session->getUsername(), $args['repository']) as $hook){
      if($hook['name'] == 'web' && strpos($hook['config']['url'], 'redports.org') !== false){
         $client->api('repo')->hooks()->remove($session->getUsername(), $args['repository'], $hook['id']);
      }
   }

   $webhook = $client->api('repo')->hooks()->create($session->getUsername(), $args['repository'],
      array(
         'name' => 'web',
         'active' => true,
         'events' => array(
            'push'
         ),
         'config' => array(
            'url' => 'https://api.redports.org/github/',
            'content_type' => 'json'
         )
      )
   );

   return $response->withRedirect('/repositories');
});

$app->run();

