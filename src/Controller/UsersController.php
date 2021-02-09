<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsersController extends DefaultController
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var Security
     */
    protected Security $security;

    /**
     * VacationsController constructor.
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @param Security $security
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        Security $security
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->security = $security;
    }


    /**
     * @Route("/api/users/get_list", name="users_get_list")
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->get('filters') ?? array();
        $sort = $request->get('sort') ?? array();
        $limit = $request->get('limit');
        $start = intval($request->get('start'));

        $users = $this->userRepository->get($filters, $sort, $limit, $start);
        $total = $this->userRepository->getTotal($filters);
        $responseData = [];
        foreach ($users as $user) {
            $responseData[] = $this->serializer->normalize(
                $user,
                false,
                [
                    'attributes' => [
                        'id',
                        'username',
                        'fio',
                        'email',
                        'roles',
                        'cities' => ['id', 'name'],
                        'shops' => ['id', 'name', 'city' => ['id', 'name']],
                    ]
                ]
            );
        }
        return $this->json(array('success' => true, 'users' => $responseData, 'total' => $total));
    }
}
