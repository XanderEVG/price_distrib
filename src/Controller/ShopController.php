<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Entity\City;
use App\Repository\ShopRepository;
use App\Services\QuotesWiper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShopController extends DefaultController
{
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
     * VacationsController constructor.
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param ShopRepository $shopRepository
     * @param Security $security
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ShopRepository $shopRepository,
        Security $security
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->shopRepository = $shopRepository;
        $this->security = $security;
    }

    /**
     * @Route("/api/shop/get_list", name="shop_get_list")
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->get('filters') ?? array();
        $sort = $request->get('sort') ?? array();
        $limit = $request->get('limit');
        $start = intval($request->get('start'));
        $city_id = QuotesWiper::slashInteger($request->get('city_id'));

        $shops = $this->shopRepository->get($filters, $sort, $limit, $start, $city_id);
        $total = $this->shopRepository->getTotal($filters);
        $responseData = [];
        foreach ($shops as $shop) {
            $responseData[] = $this->serializer->normalize(
                $shop,
                false,
                [
                    'attributes' => [
                        'id',
                        'name',
                        'city' => ['id', 'name']
                    ]
                ]
            );
        }
        return $this->json(array('success' => true, 'shops' => $responseData, 'total' => $total));
    }

    /**
     * @Route("/api/shop/save", name="create_or_edit_shop", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function edit(Request $request)
    {
        $id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING);


        $constraints_array = [
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
        ];
        $validation_fields = [
            'name' => $name
        ];
        $constraints = new Assert\Collection($constraints_array);
        $violations = $this->validator->validate($validation_fields, $constraints);

        if (count($violations) !== 0) {
            return $this->json(['success' => false, 'msg' => $this->violationsToArray($violations)]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        if ($id > 0) {
            $city = $this->cityRepository->find($id);
            if (!$city) {
                return $this->json(array(
                    'success' => false,
                    'msg' => "Не удалось обновить. Объект не существует."
                ));
            }
        } else {
            $city = new City();
        }

        $city->setName($name);
        try {
            $entityManager->persist($city);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(array(
                'success' => false,
                'msg' => "Невозможно создать пользователя, такое имя уже занято"
            ));

        }

        $responseData = [
            'success' => true,
            'city' => $this->serializer->normalize(
                $city,
                false,
                [
                    'attributes' => [
                        'id',
                        'name',
                    ]
                ]
            )
        ];
        return $this->json($responseData);
    }

    /**
     * @Route("/api/city/delete", name="delete_city", methods={"DELETE"})
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

        $this->cityRepository->deleteById($ids);
        return $this->json(array('success' => true));

    }
}
