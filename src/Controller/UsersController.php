<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use http\Client\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsersController extends DefaultController
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var CityRepository
     */
    private CityRepository $cityRepository;

    /**
     * @var ShopRepository
     */
    private ShopRepository $shopRepository;

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
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * VacationsController constructor.
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserRepository $userRepository
     * @param CityRepository $cityRepository
     * @param ShopRepository $shopRepository
     * @param Security $security
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        CityRepository $cityRepository,
        ShopRepository $shopRepository,
        Security $security,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->cityRepository = $cityRepository;
        $this->shopRepository = $shopRepository;
        $this->security = $security;
        $this->passwordEncoder = $passwordEncoder;
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

        $available_roles = User::availsbleRoles();
        foreach ($responseData as $index => $user) {
            $formatted_roles = array();
            foreach ($user['roles'] as $role) {
                $formatted_roles[] = array('id' => $role, 'name' => $available_roles[$role] ?? $role);
            }
            $responseData[$index]['roles'] = $formatted_roles;
        }


        return $this->json(array('success' => true, 'users' => $responseData, 'total' => $total));
    }


    /**
     * @Route("/api/users/save", name="create_or_edit_users", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function edit(Request $request)
    {
        $id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        $username = filter_var($request->get('username'), FILTER_SANITIZE_STRING);
        $fio = filter_var($request->get('fio'), FILTER_SANITIZE_STRING);
        $email = filter_var($request->get('email'), FILTER_SANITIZE_STRING);
        $password = filter_var($request->get('password'), FILTER_SANITIZE_STRING);

        $roles = filter_var_array($request->get('roles') ?? array(), FILTER_SANITIZE_STRING);
        $cities = filter_var_array($request->get('cities') ?? array(), FILTER_SANITIZE_STRING);
        $shops = filter_var_array($request->get('shops') ?? array(), FILTER_SANITIZE_STRING);

        $constraints_array = [
            'username' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
            'fio' => [
                new Assert\Length(['max' => 255])
            ],
            'email' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
            'roles' => [
                new Assert\NotBlank(),
            ],
        ];
        $validation_fields = [
            'username' => $username,
            'fio' => $fio,
            'email' => $email,
            'roles' => $roles,
        ];
        if ($password) {
            $constraints_array['password'] = [
                new Assert\Length(['max' => 255]),
                new Assert\Length(['min' => 4])
            ];
            $validation_fields['password'] = $password;
        }
        $constraints = new Assert\Collection($constraints_array);
        $violations = $this->validator->validate($validation_fields, $constraints);

        if (count($violations) !== 0) {
            return $this->json(['success' => false, 'msg' => $this->violationsToArray($violations)]);
        }

        $roles = array_map(fn($role) => $role['id'], $roles);


        $entityManager = $this->getDoctrine()->getManager();
        if ($id > 0) {
            $user = $this->userRepository->find($id);
            if (!$user) {
                return $this->json(array(
                    'success' => false,
                    'msg' => "Не удалось обновить. Объект не существует."
                ));
            }
        } else {
            $user = new User();
        }

        $user->setUsername($username);
        $user->setFio($fio);
        $user->setEmail($email);
        $user->setRoles($roles);
        if ($password) {
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $password
                )
            );
        }

        $user->setCities(null);
        foreach ($cities as $city_info) {
            $city = $this->cityRepository->find($city_info['id']);
            if ($city) {
                $user->addCity($city);
            }
        }

        $user->setShops(null);
        foreach ($shops as $shop_info) {
            $shop = $this->shopRepository->find($shop_info['id']);
            if ($shop) {
                $user->addShop($shop);
            }
        }

        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(array(
                'success' => false,
                'msg' => strpos($e->getMessage(), 'Key (username)') > 0 ?
                    "Невозможно создать пользователя, такое имя уже занято" :
                    "Невозможно создать пользователя, такой email уже занят"
            ));

        }

        $responseData = [
            'success' => true,
            'user' => $this->serializer->normalize(
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
            )
        ];

        $available_roles = User::availsbleRoles();

        $formatted_roles = array();
        foreach ($responseData['user']['roles'] as $role) {
            $formatted_roles[] = array('id' => $role, 'name' => $available_roles[$role] ?? $role);
        }
        $responseData['user']['roles'] = $formatted_roles;


        return $this->json($responseData);
    }

    /**
     * @Route("/api/users/delete", name="delete_users", methods={"DELETE"})
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $ids_json = $request->getContent();
        if ($ids_json) {
            $ids = json_decode($ids_json, true);
        } else {
            return $this->json(
                ['success' => false, 'errors' => 'Не указаны id для удаления']
            );
        }

        $ids = filter_var_array($ids, FILTER_SANITIZE_NUMBER_INT);
        $constraints = new Assert\All([
            new Assert\NotBlank(),
            new Assert\Positive()
        ]);
        $violations = $this->validator->validate($ids, $constraints);
        if (count($violations) !== 0) {
            return $this->json(
                ['success' => false, 'errors' => $this->violationsToArray($violations)]
            );
        }

        $this->userRepository->deleteById($ids);
        return $this->json(array('success' => true));

    }
}
