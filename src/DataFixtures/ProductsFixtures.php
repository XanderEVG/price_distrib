<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Device;
use App\Entity\Product;
use App\Entity\Shop;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductsFixtures extends Fixture
{
    public function getDependencies(): array
    {
        return [DevicesFixtures::class];
    }

    public function load(ObjectManager $manager)
    {
        $this->loadProducts($manager);
    }

    private function loadProducts(ObjectManager $manager)
    {
        $cityRepository = $manager->getRepository(City::class);
        $shopRepository = $manager->getRepository(Shop::class);
        $deviceRepository = $manager->getRepository(Device::class);

        $shops = array();
        $shops[] = $shopRepository->findOneBy(['name' => 'На федюнинского 64']);
        $shops[] = $shopRepository->findOneBy(['name' => 'На панфиловцв']);
        $shops[] = $shopRepository->findOneBy(['name' => 'Зарека']);

        $shops[] = $shopRepository->findOneBy(['name' => 'На стахановцев']);
        $shops[] = $shopRepository->findOneBy(['name' => 'На грушевой']);
        $shops[] = $shopRepository->findOneBy(['name' => 'Заводная']);



        foreach (range(0, 20) as $number) {
            $product = new Product();
            $product->setProductCode("#000$number");
            $product->setName("Товар $number");
            $product->setMainUnit("шт");
            $product->setMainPrice(rand(1000, 100000)/100);
            $product->setSecondUnit("пачка");
            $product->setSecondPrice($product->getMainPrice() * 10);
            $shop = $shops[$number % count($shops)];
            $product->setCity($shop->getCity());
            $product->setShop($shop);
            $manager->persist($product);
        }

        $manager->flush();
    }
}
