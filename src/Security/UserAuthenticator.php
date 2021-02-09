<?php

namespace App\Security;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Code\Generator\DocBlock\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $this->getLoginUrl($request) === $request->getPathInfo();
    }

    public function authenticate(Request $request): PassportInterface
    {
        $Username = $request->request->get('username', '');

        $request->getSession()->set(Security::LAST_USERNAME, $Username);

        return new Passport(
            new UserBadge($Username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('main_token', $request->headers->get('X-CSRF-TOKEN')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        $current_user = $token->getUser();

        $cities = $current_user->getCities();
        $shops = $current_user->getShops();
        $rights = array(
            'cities' => $this->serializer->normalize(
                $cities,
                false,
                [
                    'attributes' => [
                        'id',
                        'name',
                    ]
                ]
            ),
            'shops' => $this->serializer->normalize(
                $shops,
                false,
                [
                    'attributes' => [
                        'id',
                        'name',
                        'city' => ['id', 'name']
                    ]
                ]
            )
        );
        return new JsonResponse([
            'success' => true,
            'targetPath' => $targetPath,
            'id' => $current_user->getId(),
            'username' => $current_user->getUsername(),
            'fio' => $current_user->getFio(),
            'roles' => $current_user->getRoles(),
            'rights' =>$rights

        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $error = $exception->getMessage();
        $error = $this->translator->trans($error);
        return new JsonResponse(['success' => false, 'error' => $error]);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
