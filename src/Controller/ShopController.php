<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Repository\ShopRepository;
use App\Services\QuotesWiper;
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
}
