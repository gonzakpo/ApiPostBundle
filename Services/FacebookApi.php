<?php

namespace Tecspro\Bundle\ApiPostBundle\Services;


class FacebookApi {

    private $container;
    private $em;
    private $context;
    private $config;
    private $session;
    private $fb;

    public function __construct($container, $em, $context) {
        $this->container = $container;
        $this->em = $em;
        $this->context = $context;
    }

    /**
     * Configure facebook
     *
     * page appId
     * page secret
     *
     * @return void;
     */
    public function configure($config) {

        $this->config = array(
            'appId' => $config->getConfigFace()->getAppId(),
            'secret' => $config->getConfigFace()->getSecret(),
            'token' => $config->getConfigFace()->getSecret(),
            'client_id'
        );
        $this->fb = new \Facebook\Facebook([
            'app_id' => $this->config['id'],
            'app_secret' => $this->config['secret'],
            'default_graph_version' => 'v2.2',
                //'default_access_token' => '{access-token}', // optional
        ]);
        try {
            $helper = $this->fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
            $this->config['token'] = $accessToken;
            // Get the Facebook\GraphNodes\GraphUser object for the current user.
            // If you provided a 'default_access_token', the '{access-token}' is optional.
            // $helper = $fb->getRedirectLoginHelper();
            $permissions = ['email', 'user_likes']; // optional
            $loginUrl = $helper->getLoginUrl('https://www.facebook.com/Prueba-rodrigo-1682072515338195//login-callback.php', $permissions);

            echo '<a href="' . $loginUrl . '">Log in with Facebook!</a>';
            //  $response = $fb->get('/me', 'CAAXaXgDOAagBAPbmZBhr3wi0oGVBRbF6JpGZAXmkvEB7REFAhIkKuIQYEliSLcv4QlSxPuloZApjJF1pM4Pfxn2rqtXPFbECqAOPy8ZCUNUPztQzD4xBCvmd2QenLgNydJJ0BZB6L0HyVP2ZABypzFgm2D2qZA6dghP8yLn4jXgMqEjoGdDxsOrPqJJCPlrsmvhHibCPp8drgZDZD');
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $me = $response->getGraphUser();
        echo 'Logged in as ' . $me->getName();
    }

    /**
     * Post facebook
     *
     * @param array $post
     *
     * @return void;
     */
    public function postInPage($post) {
        $linkData = [
            'link' => 'http://www.example.com',
            'message' => 'User provided message',
        ];

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $this->fb->post('/me/feed', $linkData, $this->config['token']);
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $graphNode = $response->getGraphNode();

        echo 'Posted with id: ' . $graphNode['id'];
    }

    private function getUser() {
        return $this->context->getToken()->getUser();
    }

}
