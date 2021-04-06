<?php

namespace App\Controller;

use App\Common\Controller\DefaultController;
use App\Entity\City;
use App\Entity\Device;
use App\Entity\Product;
use App\Entity\Shop;
use App\Repository\CityRepository;
use App\Repository\DeviceRepository;
use App\Repository\ProductRepository;
use App\Repository\ShopRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Validator\Constraints as MyAssert;

class DevicesApiController extends DefaultController
{
    /**
     * @var CityRepository
     */
    private CityRepository $cityRepository;

    /**
     * @var ShopRepository
     */
    private ShopRepository $shopRepository;

    /**
     * @var DeviceRepository
     */
    private DeviceRepository $deviceRepository;

    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * VacationsController constructor.
     * @param SerializerInterface $serializer
     * @param CityRepository $cityRepository
     * @param ShopRepository $shopRepository
     * @param DeviceRepository $deviceRepository
     * @param ProductRepository $productRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(
        SerializerInterface $serializer,
        CityRepository $cityRepository,
        ShopRepository $shopRepository,
        DeviceRepository $deviceRepository,
        ProductRepository $productRepository,
        ValidatorInterface $validator
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->cityRepository = $cityRepository;
        $this->shopRepository = $shopRepository;
        $this->deviceRepository = $deviceRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/devices_api/registration", name="device_registration", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function registration(Request $request): JsonResponse
    {
        // зарегать устройство
        $mac = $request->headers->get('mac');

        $constraints = new Assert\Collection([
            'mac' => [
                new Assert\NotBlank(),
                new MyAssert\ContainsMac()
            ]
        ]);
        $violations = $this->validator->validate(['mac' => $mac],$constraints);

        if (count($violations) !== 0) {
            $violation = $violations[0];
            return $this->json(array('success' => false, 'msg' => $violation->getMessage()));
        }

        $check_exist = $this->deviceRepository->findBy(['mac' => $mac]);
        if ($check_exist) {
            return $this->json(array('success' => false, 'msg' => "Этот мак-адрес занят ($mac)"));
        }

        $device = new Device();
        $device->setMac($mac);

        $entityManager = $this->getDoctrine()->getManager();
        try {
            $entityManager->persist($device);
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(array(
                'success' => false,
                'msg' => 'Этот мак-адрес уже занят'
            ));
        }

        return $this->json(array('success' => true, 'id' => $device->getId(), 'get_product_period' => 30 )); //0.1*3600
    }

    /**
     * @Route("/devices_api/get_product/{device_id}", name="get_product", methods={"GET"})
     * @param Request $request
     * @param int|null $device_id
     * @return JsonResponse
     */
    public function getProduct(Request $request, ?int $device_id): JsonResponse
    {
        $mac = $request->headers->get('mac');

        $device = $this->deviceRepository->find($device_id);
        if (!$device) {
            return $this->json(array('success' => false, 'msg' => 'Устройство не найдено'));
        }
        if ($mac != $device->getMac()) {
            return $this->json(array('success' => false, 'msg' => 'Мак адрес устройства не совпадает с заданным ранее'));
        }

        if (!$product = $device->getProduct()) {
            return $this->json(array('success' => true, 'product' => false));
        }
        $data = array(
            'code' => $product->getProductCode(),
            'name' => $product->getName(),
            'main_unit' => $product->getMainUnit(),
            'main_price' => $product->getMainPrice(),
            'second_unit' => $product->getSecondUnit(),
            'second_price' => $product->getSecondPrice(),
        );
        return $this->json(array(
            'success' => true,
            'product' => true,
            'product_data' => $data,
            'get_product_period' => 30,
            'update_date' => date('d.m.Y H:i:s')
        ));
    }

    /**
     * @Route("/devices_api/destroy/{device_id}", name="device_destroy", methods={"POST"})
     * @param Request $request
     * @param int|null $device_id
     * @return JsonResponse
     * @throws ExceptionInterface
     */
    public function deviceDestroy(Request $request, ?int $device_id): JsonResponse
    {
        $mac = $request->headers->get('mac');

        $device = $this->deviceRepository->find($device_id);
        if (!$device) {
            return $this->json(array('success' => false, 'msg' => 'Устройство не найдено'));
        }
        if ($mac != $device->getMac()) {
            return $this->json(array('success' => false, 'msg' => 'Мак адрес устройства не совпадает с заданным ранее'));
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($device);
        $entityManager->flush();
        return $this->json(array('success' => true));
    }


}
