<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Device;
use App\Entity\Shop;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DevicesFixtures extends Fixture
{
    public function getDependencies(): array
    {
        return [CityFixtures::class];
    }

    public function load(ObjectManager $manager)
    {
        $this->loadDevices($manager);
    }

    private function loadDevices(ObjectManager $manager)
    {
        $shopsRepository = $manager->getRepository(Shop::class);
        $shop1 = $shopsRepository->findOneBy(['name' => 'Зарека']);
        $shop2 = $shopsRepository->findOneBy(['name' => 'На панфиловцв']);
        $shop3 = $shopsRepository->findOneBy(['name' => 'На федюнинского 64']);

        $device1 = new Device();
        $device1->setMac("00:00:00:00:00:01");
        $device1->setShop($shop1);
        $manager->persist($device1);

        $device2 = new Device();
        $device2->setMac("00:00:00:00:00:02");
        $device2->setShop($shop1);
        $manager->persist($device2);

        $device3 = new Device();
        $device3->setMac("00:00:00:00:00:03");
        $device3->setShop($shop1);
        $manager->persist($device3);


        $device4 = new Device();
        $device4->setMac("00:00:00:00:00:04");
        $device4->setShop($shop2);
        $manager->persist($device4);

        $device5 = new Device();
        $device5->setMac("00:00:00:00:00:05");
        $device5->setShop($shop2);
        $manager->persist($device5);

        $device6 = new Device();
        $device6->setMac("00:00:00:00:00:06");
        $device6->setShop($shop2);
        $manager->persist($device6);


        $device7 = new Device();
        $device7->setMac("00:00:00:00:00:07");
        $device7->setShop($shop3);
        $manager->persist($device7);

        $device8 = new Device();
        $device8->setMac("00:00:00:00:00:08");
        $device8->setShop($shop3);
        $manager->persist($device8);

        $device9 = new Device();
        $device9->setMac("00:00:00:00:00:09");
        $device9->setShop($shop3);
        $manager->persist($device9);

        $device10 = new Device();
        $device10->setMac("00:00:00:00:00:10");
        $manager->persist($device10);

        $device11 = new Device();
        $device11->setMac("00:00:00:00:00:11");
        $manager->persist($device11);

        $device12 = new Device();
        $device12->setMac("00:00:00:00:00:12");
        $manager->persist($device12);




        $manager->flush();
    }
}
