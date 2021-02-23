<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AfterLoginRedirection implements AuthenticationSuccessHandlerInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @return Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $roles = $token->getRoleNames();
        $rolesTab = array_map(function ($role){return $role;}, $roles);
        if (in_array('ROLE_ADMIN', $rolesTab, true))
        {
            $redirection =  new RedirectResponse($this->router->generate('admin_list'));
        }
        elseif (in_array('ROLE_FORMATEUR', $rolesTab, true))
        {
            $redirection = new RedirectResponse($this->router->generate("formateur"));
        }
        elseif (in_array('ROLE_COMPTA', $rolesTab, true))
        {
            $redirection = new RedirectResponse($this->router->generate('compta_toutes_notes'));
        }
        return $redirection;
    }
}