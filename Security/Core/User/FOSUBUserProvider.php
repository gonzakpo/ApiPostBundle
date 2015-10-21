<?php

namespace Tecspro\Bundle\ApiPostBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * FOS User Provider
 */
class FOSUBUserProvider extends BaseClass
{
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();        

        $service = $response->getResourceOwner()->getName();

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }        

        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
 
        $this->userManager->updateUser($user);        
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $username = $response->getUsername();
        $email = $response->getEmail();

        $service = $response->getResourceOwner()->getName();
        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        //busco usuario segun username id
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));

        if (null === $user) {
            //busco usuario segun email
            $user = $this->userManager->findUserBy(array('email' => $email));
            if (null === $user) {
                $user = $this->userManager->createUser();
                $user->setUsername($email);
                $user->setEmail($email);
                $user->setPassword($username);
            }
        }

        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
        $user->setEnabled(true);
        $this->userManager->updateUser($user);
        // $user = parent::loadUserByOAuthUserResponse($response);
        // $serviceName = $response->getResourceOwner()->getName();
        // $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        // //update access token
        // $user->$setter($response->getAccessToken());

        return $user;
    }
}