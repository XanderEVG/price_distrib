<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Entity\Device;
use App\Entity\Shop;
use App\Repository\CityRepository;
use App\Repository\DeviceRepository;
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

class DeviceController extends DefaultController
{

    /**
     * @var ShopRepository
     */
    private ShopRepository $shopRepository;

    /**
     * @var DeviceRepository
     */
    private DeviceRepository $deviceRepository;

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
     * @param DeviceRepository $deviceRepository
     * @param Security $security
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ShopRepository $shopRepository,
        DeviceRepository $deviceRepository,
        Security $security
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->shopRepository = $shopRepository;
        $this->deviceRepository = $deviceRepository;
        $this->security = $security;
    }

    /**
     * @Route("/api/device/get_list", name="device_get_list")
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

        $shop_id = $request->get('shop_id');
        if ($shop_id == null) {
            $filterBy[] = array(
                'column' => 'shop',
                'operator' => 'is',
                'value' => null,
            );
        } elseif ($shop_id !== 'all') {
            $filterBy[] = array(
                'column' => 'shop',
                'operator' => '=',
                'value' => $shop_id,
            );
        }

        $devices = $this->deviceRepository->findWithSortAndFilters($filterBy, $orderBy, $limit, $offset);
        $total = $this->deviceRepository->countWithFilters($filterBy);
        $responseData = [];
        foreach ($devices as $device) {
            $responseData[] = $this->serializer->normalize(
                $device,
                false,
                [
                    'attributes' => [
                        'id',
                        'shop' => ['id', 'name'],
                        'product' => ['id', 'name'],
                        'mac',
                    ]
                ]
            );
        }
        return $this->json(array('success' => true, 'devices' => $responseData, 'total' => $total));
    }

    /**
     * @Route("/api/device/save", name="create_or_edit_device", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function edit(Request $request): JsonResponse
    {
        $id = intval($request->get('id'));
        $mac = filter_var($request->get('mac'), FILTER_SANITIZE_STRING);
        $shop = filter_var_array($request->get('shop') ?? array(), FILTER_SANITIZE_STRING);

        $constraints_array = [
            'mac' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 255])
            ],
        ];
        $validation_fields = [
            'mac' => $mac,
        ];
        $constraints = new Assert\Collection($constraints_array);
        $violations = $this->validator->validate($validation_fields, $constraints);

        if (count($violations) !== 0) {
            return $this->json(['success' => false, 'msg' => $this->violationsToArray($violations)]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        if ($id > 0) {
            $device = $this->deviceRepository->find($id);
            if (!$device) {
                return $this->json(array(
                    'success' => false,
                    'msg' => "Не удалось обновить. Объект не существует."
                ));
            }
        } else {
            $device = new Device();
        }

        $device->setMac($mac);
        if ($shop) {
            $shop_object = $this->shopRepository->find($shop['id']);
            if ($shop_object) {
                $device->setShop($shop_object);
            }
        } else {
            $device->setShop(null);
        }


        try {
            $entityManager->persist($device);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(array(
                'success' => false,
                'msg' => "Невозможно создать устройство, такой мак-адресс уже занят"
            ));

        }

        $responseData = [
            'success' => true,
            'device' => $this->serializer->normalize(
                $device,
                false,
                [
                    'attributes' => [
                        'id',
                        'shop' => ['id', 'name'],
                        'product' => ['id', 'name'],
                        'mac',
                    ]
                ]
            )
        ];
        return $this->json($responseData);
    }

    /**
     * @Route("/api/device/delete", name="delete_device", methods={"DELETE"})
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

        $this->deviceRepository->deleteById($ids);
        return $this->json(array('success' => true));

    }
}
