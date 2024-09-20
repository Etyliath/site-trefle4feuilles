<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * @see https://symfony.com/doc/current/security/custom_authenticator.html
 */
class AppAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, private UserRepository $userRepository)
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->isMethod('POST') && $request->getPathInfo() === '/login';
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->getPayload()->getString('_username');
        $password = $request->getPayload()->getString('_password');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username,
                fn(string $identifier) => $this->userRepository->findUserByEmailOrUsername($identifier)),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::AUTHENTICATION_ERROR, $exception);
        }

        $url = $this->urlGenerator->generate(self::LOGIN_ROUTE);

        return new RedirectResponse($url);
    }

    public function start(AuthenticationException $authException = null): Response
    {

        $url = $this->urlGenerator->generate(self::LOGIN_ROUTE);

        return new RedirectResponse($url);
    }
}
