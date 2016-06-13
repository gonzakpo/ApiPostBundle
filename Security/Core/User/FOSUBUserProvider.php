<?php

namespace Tecspro\Bundle\ApiPostBundle\Security\Core\User;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseFOSUBProvider;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * FOS User Provider
 */
class FOSUBUserProvider extends BaseFOSUBProvider
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();        

        $service      = $response->getResourceOwner()->getName();
        $setter       = 'set'.ucfirst($service);
        $setter_id    = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        $existingUser = $this->userManager->findUserBy(array($property => $username));
        if (null !== $existingUser) {
            $existingUser->$setter_id(null);
            $existingUser->$setter_token(null);
            $this->userManager->updateUser($existingUser);
        }        

        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());
 
        $this->userManager->updateUser($user);        
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $userEmail = $response->getEmail();
        $user      = $this->userManager->findUserByEmail($userEmail);

        $service      = $response->getResourceOwner()->getName();
        $setter       = 'set'.ucfirst($service);
        $setter_id    = $setter.'Id';
        $setter_token = $setter.'AccessToken';
        // if null just create new user and set it properties
        if (null === $user) {
            $username = $response->getRealName();
            $user = new User();
            $user->setUsername($username);

            // ... save user to database
            $this->userManager->updateUser($user);

            return $user;
        }
        // else update access token of existing user
        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';
        $user->$setter($response->getAccessToken());//update access token

        return $user;
    }
}