<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Entity\Device;
use App\Entity\Product;
use App\Entity\Shop;
use App\Repository\CityRepository;
use App\Repository\DeviceRepository;
use App\Repository\ProductRepository;
use App\Repository\ShopRepository;
use App\Services\QuotesWiper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductController extends DefaultController
{

    /**
     * @var ShopRepository
     */
    private ShopRepository $shopRepository;

    /**
     * @var CityRepository
     */
    private CityRepository $cityRepository;

    /**
     * @var DeviceRepository
     */
    private DeviceRepository $deviceRepository;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

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
     * @param ShopRepository $shopRepository
     * @param DeviceRepository $deviceRepository
     * @param ProductRepository $productRepository
     * @param Security $security
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        CityRepository $cityRepository,
        ShopRepository $shopRepository,
        DeviceRepository $deviceRepository,
        ProductRepository $productRepository,
        Security $security
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->cityRepository = $cityRepository;
        $this->shopRepository = $shopRepository;
        $this->deviceRepository = $deviceRepository;
        $this->productRepository = $productRepository;
        $this->security = $security;
    }

    /**
     * @Route("/api/product/get_list", name="product_get_list")
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function list(Request $request): JsonResponse
    {
        $filters = $request->get('filters') ?? array();
        $sort = $request->get('sort') ?? array();
        $limit = $request->get('limit');
        $start = intval($request->get('start'));
        $city_id = QuotesWiper::slashInteger($request->get('city_id'));
        $shop_id = QuotesWiper::slashInteger($request->get('shop_id'));

        if ($sort) {
            $sort = json_decode($sort, true);
        }

        $products = $this->productRepository->get($filters, $sort, $limit, $start, $city_id, $shop_id);
        $total = $this->productRepository->getTotal($filters, $city_id, $shop_id);
        $responseData = [];
        foreach ($products as $product) {
            $responseData[] = $this->serializer->normalize(
                $product,
                false,
                [
                    'attributes' => [
                        'id',
                        'name',
                        'mainUnit',
                        'mainPrice',
                        'secondUnit',
                        'secondPrice',
                        'city' => ['id', 'name'],
                        'shop' => ['id', 'name'],
                        'devices' => ['id', 'mac'],
                    ]
                ]
            );
        }
        return $this->json(array('success' => true, 'products' => $responseData, 'total' => $total));
    }

    /**
     * @Route("/api/product/save", name="create_or_edit_product", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function edit(Request $request): JsonResponse
    {
        $id = intval($request->get('id'));
        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING);
        $main_unit = filter_var($request->get('mainUnit'), FILTER_SANITIZE_STRING);
        $second_unit = filter_var($request->get('secondUnit'), FILTER_SANITIZE_STRING);
        $main_price = filter_var($request->get('mainPrice'), FILTER_SANITIZE_NUMBER_FLOAT);
        $second_price = filter_var($request->get('secondPrice'), FILTER_SANITIZE_NUMBER_FLOAT);
        $city = filter_var_array($request->get('city') ?? array(), FILTER_SANITIZE_STRING);
        $shop = filter_var_array($request->get('shop') ?? array(), FILTER_SANITIZE_STRING);
        $devices = filter_var_array($request->get('devices') ?? array(), FILTER_SANITIZE_STRING);

        $constraints_array = [
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
            'main_unit' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
            'main_price' => [
                new Assert\NotBlank(),
                new Assert\Positive()
            ],
            'city' => [
                new Assert\NotBlank(),
                new Assert\Count(['min' => 1])
            ],
            'shop' => [
                new Assert\NotBlank(),
                new Assert\Count(['min' => 1])
            ],


        ];
        $validation_fields = [
            'name' => $name,
            'main_unit' => $main_unit,
            'main_price' => $main_price,
            'city' => $city,
            'shop' => $shop,
        ];
        $constraints = new Assert\Collection($constraints_array);
        $violations = $this->validator->validate($validation_fields, $constraints);

        if (count($violations) !== 0) {
            return $this->json(['success' => false, 'msg' => $this->violationsToArray($violations)]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        if ($id > 0) {
            $product = $this->productRepository->find($id);
            if (!$product) {
                return $this->json(array(
                    'success' => false,
                    'msg' => "Не удалось обновить. Объект не существует."
                ));
            }
        } else {
            $product = new Product();
        }

        $product->setName($name);
        $product->setMainUnit($main_unit);
        $product->setMainPrice($main_price);
        $product->setSecondUnit($second_unit);
        $product->setSecondPrice($second_price);

        $city_object = $this->cityRepository->find($city['id']);
        if ($city_object) {
            $product->setCity($city_object);
        }

        $shop_object = $this->shopRepository->find($shop['id']);
        if ($shop_object) {
            $product->setShop($shop_object);
        }

        foreach ($devices as $device) {
            $device_object = $this->deviceRepository->find($device['id']);
            if ($device_object) {
                $product->addDevice($device_object);
            }
        }

        try {
            $entityManager->persist($product);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(array(
                'success' => false,
                'msg' => "Невозможно создать товар"
            ));
        }

        $responseData = [
            'success' => true,
            'product' => $this->serializer->normalize(
                $product,
                false,
                [
                    'attributes' => [
                        'id',
                        'name',
                        'mainUnit',
                        'mainPrice',
                        'secondUnit',
                        'secondPrice',
                        'city' => ['id', 'name'],
                        'shop' => ['id', 'name'],
                        'devices' => ['id', 'mac'],
                    ]
                ]
            )
        ];
        return $this->json($responseData);
    }

    /**
     * @Route("/api/product/delete", name="delete_product", methods={"DELETE"})
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

        $this->productRepository->deleteById($ids);
        return $this->json(array('success' => true));

    }
}
