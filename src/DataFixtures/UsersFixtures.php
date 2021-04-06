<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Shop;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getDependencies()
    {
        return [CityFixtures::class];
    }

    public function load(ObjectManager $manager)
    {
        $citiesRepository = $manager->getRepository(City::class);
        $shopsRepository = $manager->getRepository(Shop::class);

        $user = new User();
        $user->setUsername("admin");
        $user->setFio("Админов Админ Админович");
        $user->setEmail("XanderEVG@gmail.com");
        $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                'adm0x001'
            )
        );

        $city1 = $citiesRepository->findOneBy(['name' => 'Тюмень']);
        $user->addCity($city1);

        $shop1 = $shopsRepository->findOneBy(['name' => 'На федюнинского 64']);
        $user->addShop($shop1);

        $shop2 = $shopsRepository->findOneBy(['name' => 'На панфиловцв']);
        $user->addShop($shop2);


        $city2 = $citiesRepository->findOneBy(['name' => 'Екатеринбург']);
        $user->addCity($city2);

        $shop3 = $shopsRepository->findOneBy(['name' => 'На стахановцев']);
        $user->addShop($shop3);

        $shop4 = $shopsRepository->findOneBy(['name' => 'На грушевой']);
        $user->addShop($shop4);


        $city3 = $citiesRepository->findOneBy(['name' => 'Москва']);
        $user->addCity($city3);

        $shop5 = $shopsRepository->findOneBy(['name' => 'Замкадная']);
        $user->addShop($shop5);





        $manager->persist($user);
        $manager->flush();


    }
}
