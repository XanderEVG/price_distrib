<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Repository\CityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
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
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->get('filters') ?? array();
        $sort = $request->get('sort') ?? array();
        $limit = $request->get('limit');
        $start = intval($request->get('start'));

        $cities = $this->cityRepository->get($filters, $sort, $limit, $start);
        $total = $this->cityRepository->getTotal($filters);
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
}
