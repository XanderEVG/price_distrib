<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Shop;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CityFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->loadTymen($manager);
        $this->loadEcat($manager);
        $this->loadMoscow($manager);


    }

    private function loadTymen(ObjectManager $manager)
    {
        $shop1 = new Shop();
        $shop1->setName("На федюнинского 64");
        $shop1->setAddress("Федюнинского 64");
        $manager->persist($shop1);

        $shop2 = new Shop();
        $shop2->setName("На панфиловцв");
        $shop2->setAddress("Панфиловцв 86");
        $manager->persist($shop2);

        $shop3 = new Shop();
        $shop3->setName("На садовой");
        $shop3->setAddress("Садовая");
        $manager->persist($shop3);

        $shop4 = new Shop();
        $shop4->setName("Зарека");
        $shop4->setAddress("Зарека");
        $manager->persist($shop4);

        $city = new City();
        $city->setName("Тюмень");
        $city->addShop($shop1);
        $city->addShop($shop2);
        $city->addShop($shop3);
        $city->addShop($shop4);


        $manager->persist($city);
        $manager->flush();

        $shop1->setCityId($city->getId());
        $shop2->setCityId($city->getId());
        $shop3->setCityId($city->getId());
        $shop4->setCityId($city->getId());

        $manager->persist($shop1);
        $manager->persist($shop2);
        $manager->persist($shop3);
        $manager->persist($shop4);
        $manager->flush();
    }

    private function loadEcat(ObjectManager $manager)
    {
        $shop1 = new Shop();
        $shop1->setName("На федюнинского 84");
        $shop1->setAddress("Федюнинского 84");
        $manager->persist($shop1);

        $shop2 = new Shop();
        $shop2->setName("На стахановцев");
        $shop2->setAddress("Стахановцев 86");
        $manager->persist($shop2);

        $shop3 = new Shop();
        $shop3->setName("На грушевой");
        $shop3->setAddress("Грушевая 55");
        $manager->persist($shop3);

        $shop4 = new Shop();
        $shop4->setName("Заводная");
        $shop4->setAddress("Заводная 1");
        $manager->persist($shop4);

        $city = new City();
        $city->setName("Екатеринбург");
        $city->addShop($shop1);
        $city->addShop($shop2);
        $city->addShop($shop3);
        $city->addShop($shop4);


        $manager->persist($city);
        $manager->flush();

        $shop1->setCityId($city->getId());
        $shop2->setCityId($city->getId());
        $shop3->setCityId($city->getId());
        $shop4->setCityId($city->getId());

        $manager->persist($shop1);
        $manager->persist($shop2);
        $manager->persist($shop3);
        $manager->persist($shop4);
        $manager->flush();
    }

    private function loadMoscow(ObjectManager $manager)
    {
        $shop1 = new Shop();
        $shop1->setName("На федюнинского 6884");
        $shop1->setAddress("Федюнинского 6884");
        $manager->persist($shop1);

        $shop2 = new Shop();
        $shop2->setName("На Ленинградской");
        $shop2->setAddress("Ленинградская 86");
        $manager->persist($shop2);

        $shop3 = new Shop();
        $shop3->setName("На кольцевой");
        $shop3->setAddress("Кольцевая 554");
        $manager->persist($shop3);

        $shop4 = new Shop();
        $shop4->setName("Замкадная");
        $shop4->setAddress("Замкадная 1");
        $manager->persist($shop4);

        $city = new City();
        $city->setName("Москва");
        $city->addShop($shop1);
        $city->addShop($shop2);
        $city->addShop($shop3);
        $city->addShop($shop4);


        $manager->persist($city);
        $manager->flush();

        $shop1->setCityId($city->getId());
        $shop2->setCityId($city->getId());
        $shop3->setCityId($city->getId());
        $shop4->setCityId($city->getId());

        $manager->persist($shop1);
        $manager->persist($shop2);
        $manager->persist($shop3);
        $manager->persist($shop4);
        $manager->flush();
    }
}
