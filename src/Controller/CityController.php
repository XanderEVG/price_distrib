<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Entity\City;
use App\Repository\CityRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CityController extends DefaultController
{
    /**
     * @var CityRepository
     */
    private CityRepository $cityRepository;

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
     * @param CityRepository $cityRepository
     * @param Security $security
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CityRepository $cityRepository,
        Security $security
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->cityRepository = $cityRepository;
        $this->security = $security;
    }

    /**
     * @Route("/api/city/get_list", name="city_get_list")
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function list(Request $request): JsonResponse
    {
        $limit = intval(filter_var($request->get('limit', 10), FILTER_SANITIZE_NUMBER_INT));
        $offset = intval(filter_var($request->get('offset', 0), FILTER_SANITIZE_NUMBER_INT));
        $orderBy = $request->get('orderBy') ?? [];
        $filterBy = $request->get('filterBy') ?? [];

        $cities = $this->cityRepository->findWithSortAndFilters($filterBy, $orderBy, $limit, $offset);
        $total = $this->cityRepository->countWithFilters($filterBy);
        $responseData = [];
        foreach ($cities as $city) {
            $responseData[] = $this->serializer->normalize(
                $city,
                false,
                [
                    'attributes' => [
                        'id',
                        'name'
                    ]
                ]
            );
        }
        return $this->json(array('success' => true, 'cities' => $responseData, 'total' => $total));
    }

    /**
     * @Route("/api/city/save", name="create_or_edit_city", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function edit(Request $request): JsonResponse
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
                'msg' => "Невозможно создать город, такое наименование уже занято"
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
    public function delete(Request $request): JsonResponse
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

        try {
            $this->cityRepository->deleteById($ids);
        } catch (ForeignKeyConstraintViolationException $e) {
            return $this->json(array('success' => false, 'msg' => "Нельзя удалять города с привязанными к нему товарами"));
        }
        return $this->json(array('success' => true));

    }
}
